import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldError, FieldGroup, FieldLabel } from '@/components/ui/field';
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

    function submitDelete(event: FormEvent) {
        event.preventDefault();
        remove.delete(urls.destroy);
    }

    return (
        <>
            <Head title={user.name} />
            <Card>
                <CardHeader>
                    <CardTitle className="text-xl">{user.name}</CardTitle>
                </CardHeader>
                <CardContent className="flex flex-col gap-3">
                    <p>Name {user.name}</p>
                    <a href={urls.pdf} className={buttonVariants({ variant: 'outline', size: 'sm' })}>
                        PDF
                    </a>
                </CardContent>
            </Card>
            <form noValidate onSubmit={submitUpdate} className="mt-4">
                <Card size="sm">
                    <CardHeader>
                        <CardTitle>Update</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <FieldGroup className="gap-4">
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
                            <Button type="submit">Update</Button>
                        </FieldGroup>
                    </CardContent>
                </Card>
            </form>
            <form onSubmit={submitDelete} className="mt-4">
                <Button type="submit" variant="destructive">
                    Delete
                </Button>
            </form>
        </>
    );
}
