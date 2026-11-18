<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\Actor;
use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\DeleteEstimate;
use App\Domain\Crm\EstimateDetail;
use App\Domain\Crm\ListedEstimate;
use App\Domain\Crm\SendEstimate;
use App\Domain\Crm\UpdateEstimate;
use App\Domain\Crm\User;
use App\Http\ActingUser;
use App\Http\CrmLog;
use App\Http\Export\CsvResponse;
use App\Http\Export\PdfResponse;
use App\Http\ListQuery;
use App\Http\Props\EstimateCreatePageProps;
use App\Http\Props\EstimateListPageProps;
use App\Http\Props\EstimatePageProps;
use App\Http\Requests\DeleteEstimateRequest;
use App\Http\Requests\SendEstimateRequest;
use App\Http\Requests\UpdateEstimateRequest;
use App\Policies\CrmPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class EstimateController
{
    public function index(Request $request, CrmRepository $crm, ActingUser $acting): Response
    {
        [$estimates, $count, $page, $term, $hasMore] = $this->page($request, $crm, $acting);

        return Inertia::render('Estimates/Index', EstimateListPageProps::make($estimates, $count, $page, $term, $hasMore));
    }

    public function create(Request $request, CrmRepository $crm, ActingUser $acting): Response
    {
        $user = $acting->resolve($request);
        $contacts = $user === null ? [] : $crm->listContacts($user->id);

        return Inertia::render('Estimates/Create', EstimateCreatePageProps::make($contacts, $crm->listProducts()));
    }

    public function store(SendEstimateRequest $request, int $contact, SendEstimate $action, CrmRepository $crm): RedirectResponse
    {
        $found = $crm->findContact($contact);
        if ($found === null || ! app(CrmPolicy::class)->viewContact($request->actor(), $found)) {
            abort(404);
        }

        $lines = [];
        foreach ($request->validated('lines') as $line) {
            $lines[] = [
                'product_id' => (int) $line['product_id'],
                'quantity' => (int) $line['quantity'],
            ];
        }

        $action->execute(new Actor($request->actor()->id), $contact, $lines);
        CrmLog::write($request, 'F4', 'success', null);

        return redirect()->route('contacts.show', $contact);
    }

    public function show(Request $request, int $estimate, CrmRepository $crm, ActingUser $acting): Response
    {
        $found = $this->visible($estimate, $crm, $acting->resolve($request));

        return Inertia::render('Estimates/Show', EstimatePageProps::make($found));
    }

    public function update(UpdateEstimateRequest $request, int $estimate, UpdateEstimate $action, CrmRepository $crm): RedirectResponse
    {
        $this->visible($estimate, $crm, $request->actor());
        $lines = [];
        foreach ($request->validated('lines') as $line) {
            $lines[] = [
                'product_id' => (int) $line['product_id'],
                'quantity' => (int) $line['quantity'],
            ];
        }

        $action->execute(new Actor($request->actor()->id), $estimate, $lines);
        CrmLog::write($request, 'F22', 'success', null);

        return redirect()->route('estimates.show', $estimate);
    }

    public function destroy(DeleteEstimateRequest $request, int $estimate, DeleteEstimate $action, CrmRepository $crm): RedirectResponse
    {
        $this->visible($estimate, $crm, $request->actor());
        $action->execute(new Actor($request->actor()->id), $estimate);
        CrmLog::write($request, 'F24', 'success', null);

        return redirect()->route('estimates.index');
    }

    public function csv(Request $request, CrmRepository $crm, ActingUser $acting): StreamedResponse
    {
        $user = $acting->resolve($request);
        $term = ListQuery::term($request);
        $estimates = $user === null ? [] : $crm->searchEstimates($user->id, $term, ListQuery::EXPORT_LIMIT, 0);

        return CsvResponse::make('estimates.csv', ['name', 'grand_total'], array_map(
            fn (ListedEstimate $estimate) => [$estimate->contactName, $estimate->grandTotal],
            $estimates,
        ));
    }

    public function pdf(Request $request, int $estimate, CrmRepository $crm, ActingUser $acting): \Illuminate\Http\Response
    {
        $found = $this->visible($estimate, $crm, $acting->resolve($request));
        $lines = [];
        foreach ($found->lines as $line) {
            $lines[] = $line->productName.' quantity '.$line->quantity.' unit price '.$line->unitPrice;
        }

        return PdfResponse::make('estimate-'.$found->id.'.pdf', $found->contactName, [
            'Contact' => $found->contactName,
            'Lines' => implode("\n", $lines),
            'Grand total' => (string) $found->grandTotal,
        ]);
    }

    /**
     * @return array{0: list<ListedEstimate>, 1: int, 2: int, 3: string, 4: bool}
     */
    private function page(Request $request, CrmRepository $crm, ActingUser $acting): array
    {
        $term = ListQuery::term($request);
        $page = ListQuery::page($request);
        $user = $acting->resolve($request);
        if ($user === null) {
            return [[], 0, $page, $term, false];
        }

        $rows = $crm->searchEstimates($user->id, $term, ListQuery::SIZE + 1, ListQuery::offset($page));
        $hasMore = count($rows) > ListQuery::SIZE;

        return [array_slice($rows, 0, ListQuery::SIZE), $crm->countEstimates($user->id), $page, $term, $hasMore];
    }

    private function visible(int $estimate, CrmRepository $crm, ?User $actor): EstimateDetail
    {
        $found = $crm->findEstimate($estimate);
        if ($found === null || ! app(CrmPolicy::class)->viewEstimate($actor, $found->userId)) {
            abort(404);
        }

        return $found;
    }
}
