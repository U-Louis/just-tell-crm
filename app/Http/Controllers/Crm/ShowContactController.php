<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\CrmRepository;
use App\Http\ActingUser;
use App\Http\Props\ContactPageProps;
use App\Policies\CrmPolicy;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowContactController
{
    public function __invoke(Request $request, int $contact, CrmRepository $crm, ActingUser $acting): Response
    {
        $found = $crm->findContact($contact);
        if ($found === null || ! app(CrmPolicy::class)->viewContact($acting->resolve($request), $found)) {
            abort(404);
        }

        return Inertia::render('Contacts/Show', ContactPageProps::make($found, $crm->listProducts()));
    }
}
