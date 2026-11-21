<?php

namespace App\Http\Middleware;

use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\User;
use App\Http\ActingUser;
use App\Http\Errors\ErrorMessages;
use App\Infrastructure\Persistence\Crm\VoiceRunStore;
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
        $runs = app(VoiceRunStore::class);
        $voice = $runs->latestForSession($request->session()->getId());
        if ($voice !== null && ! $voice->applied && ($voice->status === 'finished' || $voice->status === 'failed')) {
            $request->session()->put('acting_user_id', $voice->actingUserId);
            $runs->markApplied($voice->id);
            $voice = $runs->find($voice->id);
        }
        $user = app(ActingUser::class)->ensure($request);
        $acting = $user === null ? null : [
            'id' => $user->id,
            'name' => $user->name,
        ];

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
            'voiceStoreUrl' => route('voice.store'),
            'voice' => $voice === null ? null : [
                'id' => $voice->id,
                'status' => $voice->status,
                'transcript' => $voice->transcript,
                'result' => $voice->result,
            ],
        ]);
    }
}
