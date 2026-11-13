<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\CrmRepository;
use App\Http\ActingUser;
use App\Http\Props\ContactListPageProps;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowContactListController
{
    public function __invoke(Request $request, CrmRepository $crm, ActingUser $acting): Response
    {
        $user = $acting->resolve($request);
        $contacts = $user === null ? [] : $crm->listContacts($user->id);

        return Inertia::render('Contacts/Index', ContactListPageProps::make($contacts));
    }
}
