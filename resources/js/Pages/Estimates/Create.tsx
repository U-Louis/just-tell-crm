import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldDescription, FieldError, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { inputError } from '@/lib/inputCheck';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import type { EstimateCreateProps } from '../../types';

export default function Create({ contacts, products }: EstimateCreateProps) {
    const [contactId, setContactId] = useState(contacts[0] ? String(contacts[0].id) : '');
    const form = useForm({
        lines: products.map((product) => ({
            product_id: product.id,
            quantity: '',
        })),
    });
    const [quantityErrors, setQuantityErrors] = useState<Array<string | null>>(() => products.map(() => null));

    const preview = products.reduce((sum, product, index) => {
        const quantity = Number.parseInt(form.data.lines[index]?.quantity ?? '0', 10);
        if (!Number.isFinite(quantity) || quantity < 1) {
            return sum;
        }

        return sum + product.unitPrice * quantity;
    }, 0);

    function submit(event: FormEvent) {
        event.preventDefault();
        const errors = form.data.lines.map((line) => inputError('number', line.quantity));
        setQuantityErrors(errors);
        if (errors.some((error) => error !== null)) {
            return;
        }

        const contact = contacts.find((item) => String(item.id) === contactId);
        if (contact === undefined) {
            return;
        }

        form.transform((data) => ({
            lines: data.lines.map((line) => ({
                product_id: line.product_id,
                quantity: Number.parseInt(line.quantity, 10),
            })),
        }));
        form.post(contact.storeUrl);
    }

    return (
        <>
            <Head title="Create estimate" />
            <h1 className="text-2xl font-medium">Create estimate</h1>
            <form noValidate onSubmit={submit} className="mt-4">
                <Card size="sm">
                    <CardHeader>
                        <CardTitle>Estimate</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <FieldGroup className="gap-4">
                            <Field className="max-w-sm gap-1.5">
                                <FieldLabel htmlFor="estimate-contact">Contact</FieldLabel>
                                <select
                                    id="estimate-contact"
                                    value={contactId}
                                    onChange={(event) => setContactId(event.target.value)}
                                    className="bg-input/50 focus-visible:border-ring focus-visible:ring-ring/30 h-9 w-full rounded-3xl border border-transparent px-3 text-sm outline-none focus-visible:ring-3"
                                >
                                    {contacts.map((contact) => (
                                        <option key={contact.id} value={String(contact.id)}>
                                            {contact.name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            {products.map((product, index) => {
                                const quantityError = quantityErrors[index] ?? null;

                                return (
                                    <Field key={product.id} className="gap-1.5" data-invalid={quantityError ? true : undefined}>
                                        <FieldLabel htmlFor={`qty-${product.id}`}>{product.name} quantity</FieldLabel>
                                        <FieldDescription>unit price {product.unitPrice}</FieldDescription>
                                        <Input
                                            id={`qty-${product.id}`}
                                            type="number"
                                            min="0"
                                            step="1"
                                            value={form.data.lines[index]?.quantity ?? ''}
                                            aria-invalid={quantityError ? true : undefined}
                                            onChange={(event) => {
                                                const lines = form.data.lines.map((line, lineIndex) =>
                                                    lineIndex === index ? { ...line, quantity: event.target.value } : line,
                                                );
                                                form.setData('lines', lines);
                                            }}
                                        />
                                        {quantityError && <FieldError>{quantityError}</FieldError>}
                                    </Field>
                                );
                            })}
                            <p data-grand-total={preview}>Grand total {preview}</p>
                            <Button type="submit">Validate estimate</Button>
                        </FieldGroup>
                    </CardContent>
                </Card>
            </form>
        </>
    );
}
