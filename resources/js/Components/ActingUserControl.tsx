import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useForm } from '@inertiajs/react';
import { CheckIcon } from 'lucide-react';
import type { ActingUser } from '../types';

function letterFor(name: string | null | undefined): string {
    const letter = Array.from((name ?? '').trim())[0];

    return letter ? letter.toUpperCase() : '?';
}

export function ActingUserControl({ users, actingUser, selectUrl }: { users: ActingUser[]; actingUser: ActingUser | null; selectUrl: string }) {
    const form = useForm({ user_id: '' });

    function choose(userId: number) {
        form.transform(() => ({ user_id: userId }));
        form.post(selectUrl);
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger render={<Button type="button" variant="outline" size="icon" className="rounded-full" aria-label="Acting user" />}>
                {letterFor(actingUser?.name)}
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {users.map((user) => {
                    const current = actingUser?.id === user.id;

                    return (
                        <DropdownMenuItem key={user.id} onClick={() => choose(user.id)} aria-current={current ? 'true' : undefined}>
                            <span className="flex-1">{user.name}</span>
                            {current && <CheckIcon aria-hidden="true" />}
                        </DropdownMenuItem>
                    );
                })}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
