<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\CrmRepository;
use App\Http\Props\ContactPageProps;
use Inertia\Inertia;
use Inertia\Response;

final class ShowContactController
{
    public function __invoke(int $contact, CrmRepository $crm): Response
    {
        $found = $crm->findContact($contact);
        if ($found === null) {
            abort(404);
        }

        return Inertia::render('Contacts/Show', ContactPageProps::make($found, $crm->listProducts()));
    }
}
