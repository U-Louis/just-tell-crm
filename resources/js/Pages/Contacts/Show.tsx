import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import type { ContactPageProps } from '../../types';

export default function Show({ contact, products, urls }: ContactPageProps) {
    const qualify = useForm({
        email: contact.email ?? '',
        company: contact.company ?? '',
    });
    const estimate = useForm({
        lines: products.map((product) => ({
            product_id: product.id,
            quantity: '',
        })),
    });

    const preview = products.reduce((sum, product, index) => {
        const quantity = Number.parseInt(estimate.data.lines[index]?.quantity ?? '0', 10);
        if (!Number.isFinite(quantity) || quantity < 1) {
            return sum;
        }

        return sum + product.unitPrice * quantity;
    }, 0);

    function submitQualify(event: FormEvent) {
        event.preventDefault();
        qualify.post(urls.qualify);
    }

    function submitEstimate(event: FormEvent) {
        event.preventDefault();
        estimate.transform((data) => ({
            lines: data.lines.map((line) => ({
                product_id: line.product_id,
                quantity: line.quantity === '' ? 0 : Number.parseInt(line.quantity, 10),
            })),
        }));
        estimate.post(urls.estimate);
    }

    return (
        <>
            <Head title={contact.name} />
            <h1>{contact.name}</h1>
            <p>Email {contact.email ?? ''}</p>
            <p>Company {contact.company ?? ''}</p>
            <p data-column={contact.column}>{contact.column}</p>
            {contact.sentEmailBody && <p data-sent-body={contact.sentEmailBody}>{contact.sentEmailBody}</p>}
            {contact.grandTotal !== null && <p data-saved-grand-total={contact.grandTotal}>Saved grand total {contact.grandTotal}</p>}

            <form noValidate onSubmit={submitQualify} className="mt-4 grid gap-2">
                <div>
                    <label htmlFor="email">Email</label>
                    <input id="email" type="email" value={qualify.data.email} onChange={(event) => qualify.setData('email', event.target.value)} />
                </div>
                <div>
                    <label htmlFor="company">Company</label>
                    <input id="company" value={qualify.data.company} onChange={(event) => qualify.setData('company', event.target.value)} />
                </div>
                <button type="submit">Validate</button>
            </form>

            <form noValidate onSubmit={submitEstimate} className="mt-4 grid gap-2">
                {products.map((product, index) => (
                    <div key={product.id}>
                        <label htmlFor={`qty-${product.id}`}>{product.name} quantity</label>
                        <span> unit price {product.unitPrice}</span>
                        <input
                            id={`qty-${product.id}`}
                            type="number"
                            min="0"
                            step="1"
                            value={estimate.data.lines[index]?.quantity ?? ''}
                            onChange={(event) => {
                                const lines = estimate.data.lines.map((line, lineIndex) =>
                                    lineIndex === index ? { ...line, quantity: event.target.value } : line,
                                );
                                estimate.setData('lines', lines);
                            }}
                        />
                    </div>
                ))}
                <p data-grand-total={preview}>Grand total {preview}</p>
                <button type="submit">Validate estimate</button>
            </form>
        </>
    );
}
