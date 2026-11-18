import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldDescription, FieldError, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { inputError } from '@/lib/inputCheck';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import type { EstimatePageProps } from '../../types';

export default function Show({ estimate, urls }: EstimatePageProps) {
    const update = useForm({
        lines: estimate.lines.map((line) => ({
            product_id: line.productId,
            quantity: String(line.quantity),
        })),
    });
    const remove = useForm({});
    const [quantityErrors, setQuantityErrors] = useState<Array<string | null>>(() => estimate.lines.map(() => null));

    const preview = estimate.lines.reduce((sum, line, index) => {
        const quantity = Number.parseInt(update.data.lines[index]?.quantity ?? '0', 10);
        if (!Number.isFinite(quantity) || quantity < 1) {
            return sum;
        }

        return sum + line.currentUnitPrice * quantity;
    }, 0);

    function submitUpdate(event: FormEvent) {
        event.preventDefault();
        const errors = update.data.lines.map((line) => inputError('number', line.quantity));
        setQuantityErrors(errors);
        if (errors.some((error) => error !== null)) {
            return;
        }

        update.transform((data) => ({
            lines: data.lines.map((line) => ({
                product_id: line.product_id,
                quantity: Number.parseInt(line.quantity, 10),
            })),
        }));
        update.put(urls.update);
    }

    function submitDelete(event: FormEvent) {
        event.preventDefault();
        remove.delete(urls.destroy);
    }

    return (
        <>
            <Head title={estimate.contactName} />
            <Card>
                <CardHeader>
                    <CardTitle className="text-xl">{estimate.contactName}</CardTitle>
                </CardHeader>
                <CardContent className="flex flex-col gap-3">
                    {estimate.lines.map((line) => (
                        <p key={line.productId}>
                            {line.productName} quantity {line.quantity} unit price {line.unitPrice}
                        </p>
                    ))}
                    <p data-saved-grand-total={estimate.grandTotal}>Saved grand total {estimate.grandTotal}</p>
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
                            {estimate.lines.map((line, index) => {
                                const quantityError = quantityErrors[index] ?? null;

                                return (
                                    <Field key={line.productId} className="gap-1.5" data-invalid={quantityError ? true : undefined}>
                                        <FieldLabel htmlFor={`qty-${line.productId}`}>{line.productName} quantity</FieldLabel>
                                        <FieldDescription>current unit price {line.currentUnitPrice}</FieldDescription>
                                        <Input
                                            id={`qty-${line.productId}`}
                                            type="number"
                                            min="0"
                                            step="1"
                                            value={update.data.lines[index]?.quantity ?? ''}
                                            aria-invalid={quantityError ? true : undefined}
                                            onChange={(event) => {
                                                const lines = update.data.lines.map((item, lineIndex) =>
                                                    lineIndex === index ? { ...item, quantity: event.target.value } : item,
                                                );
                                                update.setData('lines', lines);
                                            }}
                                        />
                                        {quantityError && <FieldError>{quantityError}</FieldError>}
                                    </Field>
                                );
                            })}
                            <p data-grand-total={preview}>Grand total {preview}</p>
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
