import { FormActions } from '@/Components/FormActions';
import { Button } from '@/components/ui/button';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { inputError } from '@/lib/inputCheck';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import type { UserPageProps } from '../../types';

export default function Show({ user, urls }: UserPageProps) {
    const update = useForm({ name: user.name });
    const remove = useForm({});
    const [nameError, setNameError] = useState<string | null>(null);

    function submitUpdate(event: FormEvent) {
        event.preventDefault();
        const error = inputError('text', update.data.name);
        setNameError(error);
        if (error) {
            return;
        }

        update.put(urls.update);
    }

    function submitDelete(event: { preventDefault(): void }) {
        event.preventDefault();
        remove.delete(urls.destroy);
    }

    return (
        <>
            <Head title={user.name} />
            <div className="flex flex-col gap-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-medium">{user.name}</h1>
                    <Button type="button" variant="secondary" size="sm" disabled>
                        PDF (placeholder)
                    </Button>
                </div>
            <form noValidate onSubmit={submitUpdate} className="flex max-w-xl flex-col gap-4">
                <p>Name {user.name}</p>
                <Field className="gap-1.5" data-invalid={nameError ? true : undefined}>
                    <FieldLabel htmlFor="user-name">Name</FieldLabel>
                    <Input
                        id="user-name"
                        value={update.data.name}
                        aria-invalid={nameError ? true : undefined}
                        onChange={(event) => update.setData('name', event.target.value)}
                    />
                    {nameError && <FieldError>{nameError}</FieldError>}
                </Field>
                <FormActions>
                    <Button type="submit" className="w-auto">
                        Update
                    </Button>
                    <Button type="button" variant="destructive" className="w-auto" onClick={submitDelete}>
                        Delete
                    </Button>
                </FormActions>
            </form>
            </div>
        </>
    );
}
