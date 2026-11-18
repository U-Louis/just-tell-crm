import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldDescription, FieldError, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { inputError } from '@/lib/inputCheck';
import { Head, useForm } from '@inertiajs/react';
import { useLayoutEffect, useState, type FormEvent } from 'react';
import type { ContactPageProps } from '../../types';

export default function Show({ contact, products, urls }: ContactPageProps) {
    const update = useForm({
        name: contact.name,
        email: contact.email ?? '',
        company: contact.company ?? '',
    });
    const remove = useForm({});
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
    const [updateErrors, setUpdateErrors] = useState({ name: null as string | null, email: null as string | null, company: null as string | null });
    const [qualifyErrors, setQualifyErrors] = useState({ email: null as string | null, company: null as string | null });
    const [quantityErrors, setQuantityErrors] = useState<Array<string | null>>(() => products.map(() => null));

    const preview = products.reduce((sum, product, index) => {
        const quantity = Number.parseInt(estimate.data.lines[index]?.quantity ?? '0', 10);
        if (!Number.isFinite(quantity) || quantity < 1) {
            return sum;
        }

        return sum + product.unitPrice * quantity;
    }, 0);

    function submitUpdate(event: FormEvent) {
        event.preventDefault();
        const errors = {
            name: inputError('text', update.data.name),
            email: inputError('email', update.data.email),
            company: inputError('text', update.data.company),
        };
        setUpdateErrors(errors);
        if (errors.name || errors.email || errors.company) {
            return;
        }

        update.put(urls.update);
    }

    function submitDelete(event: FormEvent) {
        event.preventDefault();
        remove.delete(urls.destroy);
    }

    function submitQualify(event: FormEvent) {
        event.preventDefault();
        const errors = {
            email: inputError('email', qualify.data.email),
            company: inputError('text', qualify.data.company),
        };
        setQualifyErrors(errors);
        if (errors.email || errors.company) {
            return;
        }

        qualify.post(urls.qualify);
    }

    function submitEstimate(event: FormEvent) {
        event.preventDefault();
        const errors = estimate.data.lines.map((line) => inputError('number', line.quantity));
        setQuantityErrors(errors);
        if (errors.some((error) => error !== null)) {
            return;
        }

        estimate.transform((data) => ({
            lines: data.lines.map((line) => ({
                product_id: line.product_id,
                quantity: Number.parseInt(line.quantity, 10),
            })),
        }));
        estimate.post(urls.estimate);
    }

    useLayoutEffect(() => {
        function scrollToAnchor() {
            const id = window.location.hash.slice(1);
            if (id === '') {
                return;
            }
            const section = document.getElementById(id);
            if (section === null) {
                return;
            }
            section.scrollIntoView({ block: 'start' });
            section.querySelector<HTMLElement>('input')?.focus({ preventScroll: true });
        }

        scrollToAnchor();
        window.addEventListener('hashchange', scrollToAnchor);

        return () => window.removeEventListener('hashchange', scrollToAnchor);
    }, []);

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
                            <Field className="gap-1.5" data-invalid={updateErrors.name ? true : undefined}>
                                <FieldLabel htmlFor="update-name">Name</FieldLabel>
                                <Input
                                    id="update-name"
                                    value={update.data.name}
                                    aria-invalid={updateErrors.name ? true : undefined}
                                    onChange={(event) => update.setData('name', event.target.value)}
                                />
                                {updateErrors.name && <FieldError>{updateErrors.name}</FieldError>}
                            </Field>
                            <Field className="gap-1.5" data-invalid={updateErrors.email ? true : undefined}>
                                <FieldLabel htmlFor="update-email">Email address</FieldLabel>
                                <Input
                                    id="update-email"
                                    type="email"
                                    value={update.data.email}
                                    aria-invalid={updateErrors.email ? true : undefined}
                                    onChange={(event) => update.setData('email', event.target.value)}
                                />
                                {updateErrors.email && <FieldError>{updateErrors.email}</FieldError>}
                            </Field>
                            <Field className="gap-1.5" data-invalid={updateErrors.company ? true : undefined}>
                                <FieldLabel htmlFor="update-company">Company name</FieldLabel>
                                <Input
                                    id="update-company"
                                    value={update.data.company}
                                    aria-invalid={updateErrors.company ? true : undefined}
                                    onChange={(event) => update.setData('company', event.target.value)}
                                />
                                {updateErrors.company && <FieldError>{updateErrors.company}</FieldError>}
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

            <form id="qualify" noValidate onSubmit={submitQualify} className="mt-4 scroll-mt-4">
                <Card size="sm">
                    <CardHeader>
                        <CardTitle>Qualify</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <FieldGroup className="gap-4">
                            <Field className="gap-1.5" data-invalid={qualifyErrors.email ? true : undefined}>
                                <FieldLabel htmlFor="email">Email</FieldLabel>
                                <Input
                                    id="email"
                                    type="email"
                                    value={qualify.data.email}
                                    aria-invalid={qualifyErrors.email ? true : undefined}
                                    onChange={(event) => qualify.setData('email', event.target.value)}
                                />
                                {qualifyErrors.email && <FieldError>{qualifyErrors.email}</FieldError>}
                            </Field>
                            <Field className="gap-1.5" data-invalid={qualifyErrors.company ? true : undefined}>
                                <FieldLabel htmlFor="company">Company</FieldLabel>
                                <Input
                                    id="company"
                                    value={qualify.data.company}
                                    aria-invalid={qualifyErrors.company ? true : undefined}
                                    onChange={(event) => qualify.setData('company', event.target.value)}
                                />
                                {qualifyErrors.company && <FieldError>{qualifyErrors.company}</FieldError>}
                            </Field>
                            <Button type="submit">Validate</Button>
                        </FieldGroup>
                    </CardContent>
                </Card>
            </form>

            <Separator className="my-4" />

            <form id="estimate" noValidate onSubmit={submitEstimate} className="scroll-mt-4">
                <Card size="sm">
                    <CardHeader>
                        <CardTitle>Estimate</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <FieldGroup className="gap-4">
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
                                            value={estimate.data.lines[index]?.quantity ?? ''}
                                            aria-invalid={quantityError ? true : undefined}
                                            onChange={(event) => {
                                                const lines = estimate.data.lines.map((line, lineIndex) =>
                                                    lineIndex === index ? { ...line, quantity: event.target.value } : line,
                                                );
                                                estimate.setData('lines', lines);
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
