<?php

namespace App\Http;

use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\User;
use Illuminate\Http\Request;

final class ActingUser
{
    public function __construct(private CrmRepository $crm) {}

    public function resolve(Request $request): ?User
    {
        $id = $request->session()->get('acting_user_id');
        if (! is_int($id) && ! (is_string($id) && ctype_digit($id))) {
            return null;
        }

        return $this->crm->findUser((int) $id);
    }
}
