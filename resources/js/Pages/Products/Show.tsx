import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldError, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { submissionFailed } from '@/lib/clearOnSuccess';
import { inputError } from '@/lib/inputCheck';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import type { ProductPageProps } from '../../types';

export default function Show({ product, urls }: ProductPageProps) {
    const update = useForm({
        name: product.name,
        unit_price: String(product.unitPrice),
    });
    const remove = useForm({});
    const [errors, setErrors] = useState({ name: null as string | null, unitPrice: null as string | null });

    function submitUpdate(event: FormEvent) {
        event.preventDefault();
        const nextErrors = {
            name: inputError('text', update.data.name),
            unitPrice: inputError('number', update.data.unit_price),
        };
        setErrors(nextErrors);
        if (nextErrors.name || nextErrors.unitPrice) {
            return;
        }

        update.transform((data) => ({
            name: data.name,
            unit_price: Number.parseInt(data.unit_price, 10),
        }));
        update.put(urls.update, {
            onSuccess: (page) => {
                if (submissionFailed(page)) {
                    return;
                }

                update.setData({ name: '', unit_price: '' });
            },
        });
    }

    function submitDelete(event: FormEvent) {
        event.preventDefault();
        remove.delete(urls.destroy);
    }

    return (
        <>
            <Head title={product.name} />
            <Card>
                <CardHeader>
                    <CardTitle className="text-xl">{product.name}</CardTitle>
                </CardHeader>
                <CardContent className="flex flex-col gap-3">
                    <p>Name {product.name}</p>
                    <p>Unit price {product.unitPrice}</p>
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
                            <Field className="gap-1.5" data-invalid={errors.name ? true : undefined}>
                                <FieldLabel htmlFor="product-name">Name</FieldLabel>
                                <Input
                                    id="product-name"
                                    value={update.data.name}
                                    aria-invalid={errors.name ? true : undefined}
                                    onChange={(event) => update.setData('name', event.target.value)}
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
                                    value={update.data.unit_price}
                                    aria-invalid={errors.unitPrice ? true : undefined}
                                    onChange={(event) => update.setData('unit_price', event.target.value)}
                                />
                                {errors.unitPrice && <FieldError>{errors.unitPrice}</FieldError>}
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
