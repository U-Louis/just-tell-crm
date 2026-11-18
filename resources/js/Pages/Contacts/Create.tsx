import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldError, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { inputError } from '@/lib/inputCheck';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import type { ContactCreateProps } from '../../types';

export default function Create({ urls }: ContactCreateProps) {
    const form = useForm({ name: '' });
    const [nameError, setNameError] = useState<string | null>(null);

    function submit(event: FormEvent) {
        event.preventDefault();
        const error = inputError('text', form.data.name);
        setNameError(error);
        if (error) {
            return;
        }

        form.post(urls.store);
    }

    return (
        <>
            <Head title="Create contact" />
            <h1 className="text-2xl font-medium">Create contact</h1>
            <form noValidate onSubmit={submit} className="mt-4">
                <Card size="sm">
                    <CardHeader>
                        <CardTitle>Contact</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <FieldGroup className="gap-4">
                            <Field className="gap-1.5" data-invalid={nameError ? true : undefined}>
                                <FieldLabel htmlFor="contact-name">Name</FieldLabel>
                                <Input
                                    id="contact-name"
                                    value={form.data.name}
                                    aria-invalid={nameError ? true : undefined}
                                    onChange={(event) => form.setData('name', event.target.value)}
                                />
                                {nameError && <FieldError>{nameError}</FieldError>}
                            </Field>
                            <Button type="submit">Create contact</Button>
                        </FieldGroup>
                    </CardContent>
                </Card>
            </form>
        </>
    );
}
