import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldError, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { submissionFailed } from '@/lib/clearOnSuccess';
import { inputError } from '@/lib/inputCheck';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import type { UserCreateProps } from '../../types';

export default function Create({ urls }: UserCreateProps) {
    const form = useForm({ name: '' });
    const [nameError, setNameError] = useState<string | null>(null);

    function submit(event: FormEvent) {
        event.preventDefault();
        const error = inputError('text', form.data.name);
        setNameError(error);
        if (error) {
            return;
        }

        form.post(urls.store, {
            onSuccess: (page) => {
                if (submissionFailed(page)) {
                    return;
                }

                form.setData({ name: '' });
            },
        });
    }

    return (
        <>
            <Head title="Create user" />
            <h1 className="text-2xl font-medium">Create user</h1>
            <form noValidate onSubmit={submit} className="mt-4">
                <Card size="sm">
                    <CardHeader>
                        <CardTitle>User</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <FieldGroup className="gap-4">
                            <Field className="gap-1.5" data-invalid={nameError ? true : undefined}>
                                <FieldLabel htmlFor="user-name">Name</FieldLabel>
                                <Input
                                    id="user-name"
                                    value={form.data.name}
                                    aria-invalid={nameError ? true : undefined}
                                    onChange={(event) => form.setData('name', event.target.value)}
                                />
                                {nameError && <FieldError>{nameError}</FieldError>}
                            </Field>
                            <Button type="submit">Create user</Button>
                        </FieldGroup>
                    </CardContent>
                </Card>
            </form>
        </>
    );
}
