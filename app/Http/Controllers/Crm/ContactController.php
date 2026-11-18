<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\Actor;
use App\Domain\Crm\Contact;
use App\Domain\Crm\CreateContact;
use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\DeleteContact;
use App\Domain\Crm\QualifyContact;
use App\Domain\Crm\UpdateContact;
use App\Domain\Crm\User;
use App\Http\ActingUser;
use App\Http\CrmLog;
use App\Http\Export\CsvResponse;
use App\Http\Export\PdfResponse;
use App\Http\ListQuery;
use App\Http\Props\ContactCreatePageProps;
use App\Http\Props\ContactListPageProps;
use App\Http\Props\ContactPageProps;
use App\Http\Requests\CreateContactRequest;
use App\Http\Requests\DeleteContactRequest;
use App\Http\Requests\QualifyContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Policies\CrmPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ContactController
{
    public function index(Request $request, CrmRepository $crm, ActingUser $acting): Response
    {
        [$contacts, $count, $page, $term, $hasMore] = $this->page($request, $crm, $acting);

        return Inertia::render('Contacts/Index', ContactListPageProps::make($contacts, $count, $page, $term, $hasMore));
    }

    public function create(): Response
    {
        return Inertia::render('Contacts/Create', ContactCreatePageProps::make());
    }

    public function store(CreateContactRequest $request, CreateContact $action): RedirectResponse
    {
        $action->execute(new Actor($request->actor()->id), (string) $request->validated('name'));
        CrmLog::write($request, 'F1', 'success', null);

        return redirect()->route('home');
    }

    public function show(Request $request, int $contact, CrmRepository $crm, ActingUser $acting): Response
    {
        $found = $this->visible($contact, $crm, $acting->resolve($request));

        return Inertia::render('Contacts/Show', ContactPageProps::make($found, $crm->listProducts()));
    }

    public function qualify(QualifyContactRequest $request, int $contact, QualifyContact $action, CrmRepository $crm): RedirectResponse
    {
        $this->visible($contact, $crm, $request->actor());
        $email = $request->validated('email');
        $company = $request->validated('company');
        $action->execute(
            new Actor($request->actor()->id),
            $contact,
            is_string($email) ? $email : '',
            is_string($company) ? $company : '',
        );
        CrmLog::write($request, 'F3', 'success', null);

        return redirect()->route('contacts.show', $contact);
    }

    public function update(UpdateContactRequest $request, int $contact, UpdateContact $action, CrmRepository $crm): RedirectResponse
    {
        $this->visible($contact, $crm, $request->actor());
        $email = $request->validated('email');
        $company = $request->validated('company');
        $action->execute(
            new Actor($request->actor()->id),
            $contact,
            (string) $request->validated('name'),
            is_string($email) ? $email : '',
            is_string($company) ? $company : '',
        );
        CrmLog::write($request, 'F13', 'success', null);

        return redirect()->route('contacts.show', $contact);
    }

    public function destroy(DeleteContactRequest $request, int $contact, DeleteContact $action, CrmRepository $crm): RedirectResponse
    {
        $this->visible($contact, $crm, $request->actor());
        $action->execute(new Actor($request->actor()->id), $contact);
        CrmLog::write($request, 'F15', 'success', null);

        return redirect()->route('contacts.index');
    }

    public function csv(Request $request, CrmRepository $crm, ActingUser $acting): StreamedResponse
    {
        $user = $acting->resolve($request);
        $term = ListQuery::term($request);
        $contacts = $user === null ? [] : $crm->searchContacts($user->id, $term, ListQuery::EXPORT_LIMIT, 0);

        return CsvResponse::make('contacts.csv', ['name', 'column'], array_map(
            fn (Contact $contact) => [$contact->name, $contact->column],
            $contacts,
        ));
    }

    public function pdf(Request $request, int $contact, CrmRepository $crm, ActingUser $acting): \Illuminate\Http\Response
    {
        $found = $this->visible($contact, $crm, $acting->resolve($request));
        $fields = [
            'Name' => $found->name,
            'Email' => $found->email ?? '',
            'Company' => $found->company ?? '',
            'Column' => $found->column,
        ];
        if ($found->sentEmailBody !== null) {
            $fields['Sent'] = $found->sentEmailBody;
        }

        return PdfResponse::make('contact-'.$found->id.'.pdf', $found->name, $fields);
    }

    /**
     * @return array{0: list<Contact>, 1: int, 2: int, 3: string, 4: bool}
     */
    private function page(Request $request, CrmRepository $crm, ActingUser $acting): array
    {
        $term = ListQuery::term($request);
        $page = ListQuery::page($request);
        $user = $acting->resolve($request);
        if ($user === null) {
            return [[], 0, $page, $term, false];
        }

        $rows = $crm->searchContacts($user->id, $term, ListQuery::SIZE + 1, ListQuery::offset($page));
        $hasMore = count($rows) > ListQuery::SIZE;

        return [array_slice($rows, 0, ListQuery::SIZE), $crm->countContacts($user->id), $page, $term, $hasMore];
    }

    private function visible(int $contact, CrmRepository $crm, ?User $actor): Contact
    {
        $found = $crm->findContact($contact);
        if ($found === null || ! app(CrmPolicy::class)->viewContact($actor, $found)) {
            abort(404);
        }

        return $found;
    }
}
