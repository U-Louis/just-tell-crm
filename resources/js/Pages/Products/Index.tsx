import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { submissionFailed } from '@/lib/clearOnSuccess';
import { inputError } from '@/lib/inputCheck';
import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { ListPagination } from '../../Components/ListPagination';
import type { ProductListProps } from '../../types';

export default function Index({ products, count, page, pageCount, query, previousUrl, nextUrl, csvUrl, urls }: ProductListProps) {
    const create = useForm({ name: '', unit_price: '' });
    const search = useForm({ q: query });
    const [errors, setErrors] = useState({ name: null as string | null, unitPrice: null as string | null });

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        const nextErrors = {
            name: inputError('text', create.data.name),
            unitPrice: inputError('number', create.data.unit_price),
        };
        setErrors(nextErrors);
        if (nextErrors.name || nextErrors.unitPrice) {
            return;
        }

        create.transform((data) => ({
            name: data.name,
            unit_price: Number.parseInt(data.unit_price, 10),
        }));
        create.post(urls.store, {
            onSuccess: (page) => {
                if (submissionFailed(page)) {
                    return;
                }

                create.setData({ name: '', unit_price: '' });
            },
        });
    }

    function submitSearch(event: FormEvent) {
        event.preventDefault();
        search.get(urls.index);
    }

    return (
        <>
            <Head title="Products" />
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-medium">Products</h1>
                <a href={csvUrl} className={buttonVariants({ variant: 'outline', size: 'sm' })}>
                    CSV
                </a>
            </div>
            <form noValidate onSubmit={submitSearch} className="mt-4 flex items-end gap-2">
                <Field className="max-w-sm gap-1.5">
                    <FieldLabel htmlFor="product-search">Search</FieldLabel>
                    <Input id="product-search" name="q" value={search.data.q} onChange={(event) => search.setData('q', event.target.value)} />
                </Field>
                <Button type="submit" variant="outline">
                    Search
                </Button>
            </form>
            <form noValidate onSubmit={submitCreate} className="mt-4 flex flex-wrap items-end gap-2">
                <Field className="max-w-sm gap-1.5" data-invalid={errors.name ? true : undefined}>
                    <FieldLabel htmlFor="product-name">Name</FieldLabel>
                    <Input
                        id="product-name"
                        value={create.data.name}
                        aria-invalid={errors.name ? true : undefined}
                        onChange={(event) => create.setData('name', event.target.value)}
                    />
                    {errors.name && <FieldError>{errors.name}</FieldError>}
                </Field>
                <Field className="w-36 gap-1.5" data-invalid={errors.unitPrice ? true : undefined}>
                    <FieldLabel htmlFor="unit-price">Unit price</FieldLabel>
                    <Input
                        id="unit-price"
                        type="number"
                        min="0"
                        step="1"
                        value={create.data.unit_price}
                        aria-invalid={errors.unitPrice ? true : undefined}
                        onChange={(event) => create.setData('unit_price', event.target.value)}
                    />
                    {errors.unitPrice && <FieldError>{errors.unitPrice}</FieldError>}
                </Field>
                <Button type="submit">Create product</Button>
            </form>
            <ul className="mt-4 flex flex-col gap-2">
                {products.map((product) => (
                    <li key={product.id} data-product-name={product.name}>
                        <Card size="sm">
                            <CardContent className="flex items-center justify-between gap-3">
                                <Link href={product.showUrl} className="font-medium">
                                    {product.name}
                                </Link>
                                <div className="flex items-center gap-3">
                                    <span>{product.unitPrice}</span>
                                    <Link href={product.showUrl} className={buttonVariants({ variant: 'outline', size: 'sm' })}>
                                        Update
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>
                    </li>
                ))}
            </ul>
            <ListPagination count={count} page={page} pageCount={pageCount} previousUrl={previousUrl} nextUrl={nextUrl} />
        </>
    );
}
