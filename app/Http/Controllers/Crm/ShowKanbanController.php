<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\CrmRepository;
use App\Http\Props\KanbanPageProps;
use Inertia\Inertia;
use Inertia\Response;

final class ShowKanbanController
{
    public function __invoke(CrmRepository $crm): Response
    {
        return Inertia::render('Kanban', KanbanPageProps::make($crm->listContacts()));
    }
}
