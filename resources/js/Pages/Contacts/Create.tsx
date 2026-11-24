import { FormActions } from '@/Components/FormActions';
import { SelectInput } from '@/Components/SelectInput';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldError, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { submissionFailed } from '@/lib/clearOnSuccess';
import { inputError } from '@/lib/inputCheck';
import { Head, useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import type { ContactCreateProps, SharedProps } from '../../types';

export default function Create({ companySizes, urls }: ContactCreateProps) {
    const page = usePage<SharedProps>();
    const form = useForm({
        name: '',
        description: '',
        notes: '',
        company_size: '',
        owner_id: page.props.actingUser ? String(page.props.actingUser.id) : '',
    });
    const [errors, setErrors] = useState({
        name: null as string | null,
        description: null as string | null,
        notes: null as string | null,
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        const nextErrors = {
            name: inputError('text', form.data.name),
            description: inputError('text', form.data.description),
            notes: inputError('text', form.data.notes),
        };
        setErrors(nextErrors);
        if (nextErrors.name || nextErrors.description || nextErrors.notes) {
            return;
        }

        form.transform((data) => ({
            name: data.name,
            description: data.description,
            notes: data.notes,
            company_size: data.company_size,
            owner_id: data.owner_id === '' ? null : Number(data.owner_id),
        }));
        form.post(urls.store, {
            onSuccess: (visit) => {
                if (submissionFailed(visit)) {
                    return;
                }

                form.setData({
                    name: '',
                    description: '',
                    notes: '',
                    company_size: '',
                    owner_id: page.props.actingUser ? String(page.props.actingUser.id) : '',
                });
            },
        });
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
                            <Field className="gap-1.5" data-invalid={errors.name ? true : undefined}>
                                <FieldLabel htmlFor="contact-name">Name</FieldLabel>
                                <Input
                                    id="contact-name"
                                    value={form.data.name}
                                    aria-invalid={errors.name ? true : undefined}
                                    onChange={(event) => form.setData('name', event.target.value)}
                                />
                                {errors.name && <FieldError>{errors.name}</FieldError>}
                            </Field>
                            <Field className="gap-1.5" data-invalid={errors.description ? true : undefined}>
                                <FieldLabel htmlFor="contact-description">Description</FieldLabel>
                                <Input
                                    id="contact-description"
                                    value={form.data.description}
                                    aria-invalid={errors.description ? true : undefined}
                                    onChange={(event) => form.setData('description', event.target.value)}
                                />
                                {errors.description && <FieldError>{errors.description}</FieldError>}
                            </Field>
                            <Field className="gap-1.5" data-invalid={errors.notes ? true : undefined}>
                                <FieldLabel htmlFor="contact-notes">Notes</FieldLabel>
                                <Input
                                    id="contact-notes"
                                    value={form.data.notes}
                                    aria-invalid={errors.notes ? true : undefined}
                                    onChange={(event) => form.setData('notes', event.target.value)}
                                />
                                {errors.notes && <FieldError>{errors.notes}</FieldError>}
                            </Field>
                            <Field className="gap-1.5">
                                <FieldLabel htmlFor="company-size">Company size</FieldLabel>
                                <SelectInput
                                    id="company-size"
                                    value={form.data.company_size}
                                    onChange={(event) => form.setData('company_size', event.target.value)}
                                >
                                    <option value="">Select a size</option>
                                    {companySizes.map((size) => (
                                        <option key={size} value={size}>
                                            {size}
                                        </option>
                                    ))}
                                </SelectInput>
                            </Field>
                            <Field className="gap-1.5">
                                <FieldLabel htmlFor="contact-owner">Owner</FieldLabel>
                                <SelectInput
                                    id="contact-owner"
                                    value={form.data.owner_id}
                                    onChange={(event) => form.setData('owner_id', event.target.value)}
                                >
                                    {page.props.users.map((user) => (
                                        <option key={user.id} value={String(user.id)}>
                                            {user.name}
                                        </option>
                                    ))}
                                </SelectInput>
                            </Field>
                            <FormActions>
                                <Button type="submit" className="w-auto">
                                    Create contact
                                </Button>
                            </FormActions>
                        </FieldGroup>
                    </CardContent>
                </Card>
            </form>
        </>
    );
}
