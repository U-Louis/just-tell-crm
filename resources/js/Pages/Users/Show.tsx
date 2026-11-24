import { Button, buttonVariants } from '@/components/ui/button';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { submissionFailed } from '@/lib/clearOnSuccess';
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

        update.put(urls.update, {
            onSuccess: (page) => {
                if (submissionFailed(page)) {
                    return;
                }

                update.setData({ name: '' });
            },
        });
    }

    function submitDelete(event: { preventDefault(): void }) {
        event.preventDefault();
        remove.delete(urls.destroy);
    }

    return (
        <>
            <Head title={user.name} />
            <form noValidate onSubmit={submitUpdate} className="flex max-w-xl flex-col gap-4">
                <h1 className="text-2xl font-medium">{user.name}</h1>
                <p>Name {user.name}</p>
                <a href={urls.pdf} className={buttonVariants({ variant: 'outline', size: 'sm', className: 'self-start' })}>
                    PDF
                </a>
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
                <div className="flex flex-wrap gap-2">
                    <Button type="submit">Update</Button>
                    <Button type="button" variant="destructive" onClick={submitDelete}>
                        Delete
                    </Button>
                </div>
            </form>
        </>
    );
}
