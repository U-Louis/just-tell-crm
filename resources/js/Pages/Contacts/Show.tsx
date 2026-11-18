import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldDescription, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
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
            <Card>
                <CardHeader>
                    <CardTitle className="text-xl">{contact.name}</CardTitle>
                    <CardDescription>Email {contact.email ?? ''}</CardDescription>
                </CardHeader>
                <CardContent className="flex flex-col gap-3">
                    <p>Company {contact.company ?? ''}</p>
                    <Badge variant="outline" data-column={contact.column}>
                        {contact.column}
                    </Badge>
                    {contact.sentEmailBody && (
                        <Alert>
                            <AlertDescription data-sent-body={contact.sentEmailBody}>{contact.sentEmailBody}</AlertDescription>
                        </Alert>
                    )}
                    {contact.grandTotal !== null && <p data-saved-grand-total={contact.grandTotal}>Saved grand total {contact.grandTotal}</p>}
                </CardContent>
            </Card>

            <form noValidate onSubmit={submitQualify} className="mt-4">
                <Card size="sm">
                    <CardHeader>
                        <CardTitle>Qualify</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <FieldGroup className="gap-4">
                            <Field className="gap-1.5">
                                <FieldLabel htmlFor="email">Email</FieldLabel>
                                <Input
                                    id="email"
                                    type="email"
                                    value={qualify.data.email}
                                    onChange={(event) => qualify.setData('email', event.target.value)}
                                />
                            </Field>
                            <Field className="gap-1.5">
                                <FieldLabel htmlFor="company">Company</FieldLabel>
                                <Input id="company" value={qualify.data.company} onChange={(event) => qualify.setData('company', event.target.value)} />
                            </Field>
                            <Button type="submit">Validate</Button>
                        </FieldGroup>
                    </CardContent>
                </Card>
            </form>

            <Separator className="my-4" />

            <form noValidate onSubmit={submitEstimate}>
                <Card size="sm">
                    <CardHeader>
                        <CardTitle>Estimate</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <FieldGroup className="gap-4">
                            {products.map((product, index) => (
                                <Field key={product.id} className="gap-1.5">
                                    <FieldLabel htmlFor={`qty-${product.id}`}>{product.name} quantity</FieldLabel>
                                    <FieldDescription>unit price {product.unitPrice}</FieldDescription>
                                    <Input
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
                                </Field>
                            ))}
                            <p data-grand-total={preview}>Grand total {preview}</p>
                            <Button type="submit">Validate estimate</Button>
                        </FieldGroup>
                    </CardContent>
                </Card>
            </form>
        </>
    );
}
