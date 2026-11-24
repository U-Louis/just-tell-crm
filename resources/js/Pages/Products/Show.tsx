import { FormActions } from '@/Components/FormActions';
import { SelectInput } from '@/Components/SelectInput';
import { Button } from '@/components/ui/button';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { inputError } from '@/lib/inputCheck';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import type { ProductPageProps } from '../../types';

export default function Show({ product, categories, urls }: ProductPageProps) {
    const update = useForm({
        name: product.name,
        unit_price: String(product.unitPrice),
        description: product.description ?? '',
        category: product.category ?? '',
    });
    const remove = useForm({});
    const [errors, setErrors] = useState({
        name: null as string | null,
        unitPrice: null as string | null,
        description: null as string | null,
    });

    function submitUpdate(event: FormEvent) {
        event.preventDefault();
        const nextErrors = {
            name: inputError('text', update.data.name),
            unitPrice: inputError('number', update.data.unit_price),
            description: inputError('text', update.data.description),
        };
        setErrors(nextErrors);
        if (nextErrors.name || nextErrors.unitPrice || nextErrors.description) {
            return;
        }

        update.transform((data) => ({
            name: data.name,
            unit_price: Number.parseInt(data.unit_price, 10),
            description: data.description,
            category: data.category,
        }));
        update.put(urls.update);
    }

    function submitDelete(event: { preventDefault(): void }) {
        event.preventDefault();
        remove.delete(urls.destroy);
    }

    return (
        <>
            <Head title={product.name} />
            <div className="flex flex-col gap-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-medium">{product.name}</h1>
                    <Button type="button" variant="secondary" size="sm" disabled>
                        PDF (placeholder)
                    </Button>
                </div>
            <form noValidate onSubmit={submitUpdate} className="flex max-w-xl flex-col gap-4">
                <p>Name {product.name}</p>
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
                <Field className="gap-1.5" data-invalid={errors.description ? true : undefined}>
                    <FieldLabel htmlFor="product-description">Description</FieldLabel>
                    <Input
                        id="product-description"
                        value={update.data.description}
                        aria-invalid={errors.description ? true : undefined}
                        onChange={(event) => update.setData('description', event.target.value)}
                    />
                    {errors.description && <FieldError>{errors.description}</FieldError>}
                </Field>
                <Field className="gap-1.5">
                    <FieldLabel htmlFor="product-category">Category</FieldLabel>
                    <SelectInput id="product-category" value={update.data.category} onChange={(event) => update.setData('category', event.target.value)}>
                        <option value="">Select a category</option>
                        {categories.map((category) => (
                            <option key={category} value={category}>
                                {category}
                            </option>
                        ))}
                    </SelectInput>
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
