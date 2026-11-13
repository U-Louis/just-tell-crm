import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import type { ActingUser } from '../types';

export function ActingUserControl({
    users,
    actingUser,
    selectUrl,
}: {
    users: ActingUser[];
    actingUser: ActingUser | null;
    selectUrl: string;
}) {
    const form = useForm({
        user_id: actingUser ? String(actingUser.id) : '',
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(selectUrl);
    }

    return (
        <form onSubmit={submit}>
            <label htmlFor="acting-user">Acting user</label>
            <select id="acting-user" value={form.data.user_id} onChange={(event) => form.setData('user_id', event.target.value)}>
                <option value="">Choose</option>
                {users.map((user) => (
                    <option key={user.id} value={user.id}>
                        {user.name}
                    </option>
                ))}
            </select>
            <button type="submit">Select</button>
        </form>
    );
}
