<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\CrmRepository;
use App\Http\Requests\SelectActingUserRequest;
use Illuminate\Http\RedirectResponse;

final class SelectActingUserController
{
    public function __invoke(SelectActingUserRequest $request, CrmRepository $crm): RedirectResponse
    {
        $user = $crm->findUser((int) $request->validated('user_id'));
        if ($user === null) {
            abort(404);
        }

        $request->session()->put('acting_user_id', $user->id);

        return redirect()->route('home');
    }
}
