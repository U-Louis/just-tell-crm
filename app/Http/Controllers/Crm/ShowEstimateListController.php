<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\CrmRepository;
use App\Http\ActingUser;
use App\Http\Props\EstimateListPageProps;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowEstimateListController
{
    public function __invoke(Request $request, CrmRepository $crm, ActingUser $acting): Response
    {
        $user = $acting->resolve($request);
        $estimates = $user === null ? [] : $crm->listEstimates($user->id);

        return Inertia::render('Estimates/Index', EstimateListPageProps::make($estimates));
    }
}
