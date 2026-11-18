<?php

namespace App\Http\Middleware;

use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\User;
use App\Http\Errors\ErrorMessages;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $crm = app(CrmRepository::class);
        $acting = null;
        $id = $request->session()->get('acting_user_id');
        if (is_int($id) || (is_string($id) && ctype_digit($id))) {
            $user = $crm->findUser((int) $id);
            if ($user !== null) {
                $acting = [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            }
        }

        $error = $request->session()->get('error');
        if (! is_array($error) || ! is_string($error['code'] ?? null)) {
            $error = null;
        } else {
            $error['message'] = ErrorMessages::MAP[$error['code']] ?? null;
        }

        return array_merge(parent::share($request), [
            'actingUser' => $acting,
            'users' => array_map(
                fn (User $user) => ['id' => $user->id, 'name' => $user->name],
                $crm->listUsers(),
            ),
            'error' => $error,
            'correlationId' => $request->attributes->get('correlation_id'),
            'kanbanUrl' => route('home'),
            'contactsUrl' => route('contacts.index'),
            'estimatesUrl' => route('estimates.index'),
            'usersUrl' => route('users.index'),
            'productsUrl' => route('products.index'),
            'selectActingUserUrl' => route('acting-user.store'),
        ]);
    }
}
