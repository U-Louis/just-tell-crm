import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Field, FieldLabel } from '@/components/ui/field';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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
    const items = Object.fromEntries(users.map((user) => [String(user.id), user.name]));

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(selectUrl);
    }

    return (
        <form onSubmit={submit} className="flex items-end gap-2">
            <Field className="w-48 gap-1.5">
                <FieldLabel htmlFor="acting-user">Acting user</FieldLabel>
                <Select
                    items={items}
                    value={form.data.user_id === '' ? null : form.data.user_id}
                    onValueChange={(value) => form.setData('user_id', value == null ? '' : String(value))}
                >
                    <SelectTrigger id="acting-user" className="w-full">
                        <SelectValue placeholder="Choose" />
                    </SelectTrigger>
                    <SelectContent>
                        {users.map((user) => (
                            <SelectItem key={user.id} value={String(user.id)}>
                                {user.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </Field>
            <Button type="submit">Select</Button>
        </form>
    );
}
