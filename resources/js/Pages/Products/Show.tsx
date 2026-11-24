import { Button } from '@/components/ui/button';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
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

    function submitDelete(event: { preventDefault(): void }) {
        event.preventDefault();
        remove.delete(urls.destroy);
    }

    return (
        <>
            <Head title={product.name} />
            <form noValidate onSubmit={submitUpdate} className="flex max-w-xl flex-col gap-4">
                <h1 className="text-2xl font-medium">{product.name}</h1>
                <p>Name {product.name}</p>
                <p>Unit price {product.unitPrice}</p>
                <Button type="button" variant="outline" size="sm" className="self-start" disabled>
                    PDF (placeholder)
                </Button>
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
