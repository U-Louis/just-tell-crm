<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\Actor;
use App\Domain\Crm\CreateUser;
use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\DeleteUser;
use App\Domain\Crm\UpdateUser;
use App\Domain\Crm\User;
use App\Http\ActingUser;
use App\Http\CrmLog;
use App\Http\Export\CsvResponse;
use App\Http\Export\PdfResponse;
use App\Http\ListQuery;
use App\Http\Props\UserCreatePageProps;
use App\Http\Props\UserListPageProps;
use App\Http\Props\UserPageProps;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\SelectActingUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Policies\CrmPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class UserController
{
    public function select(SelectActingUserRequest $request, CrmRepository $crm): RedirectResponse
    {
        $user = $crm->findUser((int) $request->validated('user_id'));
        if ($user === null) {
            abort(404);
        }

        $request->session()->put('acting_user_id', $user->id);

        return redirect()->route('home');
    }

    public function index(Request $request, CrmRepository $crm, ActingUser $acting): Response
    {
        [$users, $count, $page, $term, $hasMore] = $this->page($request, $crm, $acting);

        return Inertia::render('Users/Index', UserListPageProps::make($users, $count, $page, $term, $hasMore));
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create', UserCreatePageProps::make());
    }

    public function store(CreateUserRequest $request, CreateUser $action): RedirectResponse
    {
        $action->execute(new Actor($request->actor()->id), (string) $request->validated('name'));
        CrmLog::write($request, 'F7', 'success', null);

        return redirect()->route('users.index');
    }

    public function show(Request $request, int $user, CrmRepository $crm, ActingUser $acting): Response
    {
        $found = $this->visible($user, $crm, $acting->resolve($request));

        return Inertia::render('Users/Show', UserPageProps::make($found));
    }

    public function update(UpdateUserRequest $request, int $user, UpdateUser $action, CrmRepository $crm): RedirectResponse
    {
        $this->visible($user, $crm, $request->actor());
        $action->execute(new Actor($request->actor()->id), $user, (string) $request->validated('name'));
        CrmLog::write($request, 'F10', 'success', null);

        return redirect()->route('users.show', $user);
    }

    public function destroy(DeleteUserRequest $request, int $user, DeleteUser $action, CrmRepository $crm): RedirectResponse
    {
        $this->visible($user, $crm, $request->actor());
        $action->execute(new Actor($request->actor()->id), $user);
        CrmLog::write($request, 'F12', 'success', null);
        if ((int) $request->session()->get('acting_user_id') === $user) {
            $request->session()->forget('acting_user_id');
        }

        return redirect()->route('users.index');
    }

    public function csv(Request $request, CrmRepository $crm, ActingUser $acting): StreamedResponse
    {
        $term = ListQuery::term($request);
        $users = $acting->resolve($request) === null
            ? []
            : $crm->searchUsers($term, ListQuery::EXPORT_LIMIT, 0);

        return CsvResponse::make('users.csv', ['name'], array_map(
            fn (User $user) => [$user->name],
            $users,
        ));
    }

    public function pdf(Request $request, int $user, CrmRepository $crm, ActingUser $acting): \Illuminate\Http\Response
    {
        $found = $this->visible($user, $crm, $acting->resolve($request));

        return PdfResponse::make('user-'.$found->id.'.pdf', $found->name, [
            'Name' => $found->name,
        ]);
    }

    /**
     * @return array{0: list<User>, 1: int, 2: int, 3: string, 4: bool}
     */
    private function page(Request $request, CrmRepository $crm, ActingUser $acting): array
    {
        $term = ListQuery::term($request);
        $page = ListQuery::page($request);
        if ($acting->resolve($request) === null) {
            return [[], 0, $page, $term, false];
        }

        $rows = $crm->searchUsers($term, ListQuery::SIZE + 1, ListQuery::offset($page));
        $hasMore = count($rows) > ListQuery::SIZE;

        return [array_slice($rows, 0, ListQuery::SIZE), $crm->countUsers(), $page, $term, $hasMore];
    }

    private function visible(int $user, CrmRepository $crm, ?User $actor): User
    {
        $found = $crm->findUser($user);
        if ($found === null || ! app(CrmPolicy::class)->viewUser($actor, $found)) {
            abort(404);
        }

        return $found;
    }
}
