<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\Actor;
use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\QualifyContact;
use App\Http\CrmLog;
use App\Http\Requests\QualifyContactRequest;
use Illuminate\Http\RedirectResponse;

final class QualifyContactController
{
    public function __invoke(QualifyContactRequest $request, int $contact, QualifyContact $action, CrmRepository $crm): RedirectResponse
    {
        if ($crm->findContact($contact) === null) {
            abort(404);
        }

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
}
