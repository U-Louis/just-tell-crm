<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\Actor;
use App\Domain\Crm\CreateContact;
use App\Http\CrmLog;
use App\Http\Requests\CreateContactRequest;
use Illuminate\Http\RedirectResponse;

final class CreateContactController
{
    public function __invoke(CreateContactRequest $request, CreateContact $action): RedirectResponse
    {
        $action->execute(new Actor($request->actor()->id), (string) $request->validated('name'));
        CrmLog::write($request, 'F1', 'success', null);

        return redirect()->route('home');
    }
}
