import { Button, buttonVariants } from '@/components/ui/button';
import { Field, FieldDescription, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { submissionFailed } from '@/lib/clearOnSuccess';
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
        update.put(urls.update, {
            onSuccess: (page) => {
                if (submissionFailed(page)) {
                    return;
                }

                update.setData((data) => ({
                    lines: data.lines.map((line) => ({ ...line, quantity: '' })),
                }));
            },
        });
    }

    function submitDelete(event: { preventDefault(): void }) {
        event.preventDefault();
        remove.delete(urls.destroy);
    }

    return (
        <>
            <Head title={estimate.contactName} />
            <form noValidate onSubmit={submitUpdate} className="flex max-w-xl flex-col gap-4">
                <h1 className="text-2xl font-medium">{estimate.contactName}</h1>
                {estimate.lines.map((line) => (
                    <p key={line.productId}>
                        {line.productName} quantity {line.quantity} unit price {line.unitPrice}
                    </p>
                ))}
                <p data-saved-grand-total={estimate.grandTotal}>Saved grand total {estimate.grandTotal}</p>
                <a href={urls.pdf} className={buttonVariants({ variant: 'outline', size: 'sm', className: 'self-start' })}>
                    PDF
                </a>
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
