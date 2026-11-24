import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldError, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { submissionFailed } from '@/lib/clearOnSuccess';
import { inputError } from '@/lib/inputCheck';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import type { ProductCreateProps } from '../../types';

export default function Create({ urls }: ProductCreateProps) {
    const form = useForm({ name: '', unit_price: '' });
    const [errors, setErrors] = useState({ name: null as string | null, unitPrice: null as string | null });

    function submit(event: FormEvent) {
        event.preventDefault();
        const nextErrors = {
            name: inputError('text', form.data.name),
            unitPrice: inputError('number', form.data.unit_price),
        };
        setErrors(nextErrors);
        if (nextErrors.name || nextErrors.unitPrice) {
            return;
        }

        form.transform((data) => ({
            name: data.name,
            unit_price: Number.parseInt(data.unit_price, 10),
        }));
        form.post(urls.store, {
            onSuccess: (page) => {
                if (submissionFailed(page)) {
                    return;
                }

                form.setData({ name: '', unit_price: '' });
            },
        });
    }

    return (
        <>
            <Head title="Create product" />
            <h1 className="text-2xl font-medium">Create product</h1>
            <form noValidate onSubmit={submit} className="mt-4">
                <Card size="sm">
                    <CardHeader>
                        <CardTitle>Product</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <FieldGroup className="gap-4">
                            <Field className="gap-1.5" data-invalid={errors.name ? true : undefined}>
                                <FieldLabel htmlFor="product-name">Name</FieldLabel>
                                <Input
                                    id="product-name"
                                    value={form.data.name}
                                    aria-invalid={errors.name ? true : undefined}
                                    onChange={(event) => form.setData('name', event.target.value)}
                                />
                                {errors.name && <FieldError>{errors.name}</FieldError>}
                            </Field>
                            <Field className="gap-1.5" data-invalid={errors.unitPrice ? true : undefined}>
                                <FieldLabel htmlFor="unit-price">Unit price</FieldLabel>
                                <Input
                                    id="unit-price"
                                    type="number"
                                    min="0"
                                    step="1"
                                    value={form.data.unit_price}
                                    aria-invalid={errors.unitPrice ? true : undefined}
                                    onChange={(event) => form.setData('unit_price', event.target.value)}
                                />
                                {errors.unitPrice && <FieldError>{errors.unitPrice}</FieldError>}
                            </Field>
                            <Button type="submit">Create product</Button>
                        </FieldGroup>
                    </CardContent>
                </Card>
            </form>
        </>
    );
}
