<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\Actor;
use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\SendEstimate;
use App\Http\CrmLog;
use App\Http\Requests\SendEstimateRequest;
use App\Policies\CrmPolicy;
use Illuminate\Http\RedirectResponse;

final class SendEstimateController
{
    public function __invoke(SendEstimateRequest $request, int $contact, SendEstimate $action, CrmRepository $crm): RedirectResponse
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
}
