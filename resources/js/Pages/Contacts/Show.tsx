import { ColumnPill } from '@/Components/ColumnPill';
import { FormActions } from '@/Components/FormActions';
import { SelectInput } from '@/Components/SelectInput';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Field, FieldDescription, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { submissionFailed } from '@/lib/clearOnSuccess';
import { inputError } from '@/lib/inputCheck';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useLayoutEffect, useState, type FormEvent } from 'react';
import type { ContactPageProps, SharedProps } from '../../types';

export default function Show({ contact, companySizes, products, urls }: ContactPageProps) {
    const page = usePage<SharedProps>();
    const update = useForm({
        name: contact.name,
        email: contact.email ?? '',
        company: contact.company ?? '',
        description: contact.description ?? '',
        notes: contact.notes ?? '',
        company_size: contact.companySize ?? '',
        owner_id: String(contact.ownerId ?? page.props.actingUser?.id ?? ''),
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
    const [updateErrors, setUpdateErrors] = useState({
        name: null as string | null,
        email: null as string | null,
        company: null as string | null,
        description: null as string | null,
        notes: null as string | null,
    });
    const [qualifyErrors, setQualifyErrors] = useState({ email: null as string | null, company: null as string | null });
    const [quantityErrors, setQuantityErrors] = useState<Array<string | null>>(() => products.map(() => null));
    const [sendError, setSendError] = useState<string | null>(null);

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
            description: inputError('text', update.data.description),
            notes: inputError('text', update.data.notes),
        };
        setUpdateErrors(errors);
        if (errors.name || errors.email || errors.company || errors.description || errors.notes) {
            return;
        }

        update.transform((data) => ({
            ...data,
            owner_id: data.owner_id === '' ? null : Number(data.owner_id),
        }));
        update.put(urls.update);
    }

    function submitDelete(event: { preventDefault(): void }) {
        event.preventDefault();
        remove.delete(urls.destroy);
    }

    function submitQualify(event: { preventDefault(): void }) {
        event.preventDefault();
        const errors = {
            email: inputError('email', qualify.data.email),
            company: inputError('text', qualify.data.company),
        };
        setQualifyErrors(errors);
        if (errors.email || errors.company) {
            return;
        }

        qualify.post(urls.qualify, {
            onSuccess: (page) => {
                if (submissionFailed(page)) {
                    return;
                }

                qualify.setData({ email: '', company: '' });
            },
        });
    }

    function submitEstimate(event: { preventDefault(): void }) {
        event.preventDefault();
        const errors = estimate.data.lines.map((line) => inputError('number', line.quantity));
        setQuantityErrors(errors);
        if (errors.some((error) => error !== null)) {
            return;
        }

        if (!contact.validated) {
            setSendError('Validate the contact before sending an estimate.');
            return;
        }

        setSendError(null);
        estimate.transform((data) => ({
            lines: data.lines.map((line) => ({
                product_id: line.product_id,
                quantity: Number.parseInt(line.quantity, 10),
            })),
        }));
        estimate.post(urls.estimate, {
            onSuccess: (page) => {
                if (submissionFailed(page)) {
                    return;
                }

                estimate.setData((data) => ({
                    lines: data.lines.map((line) => ({ ...line, quantity: '' })),
                }));
            },
        });
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
            <div className="flex flex-col gap-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-medium">{contact.name}</h1>
                    <Button type="button" variant="secondary" size="sm" disabled>
                        PDF (placeholder)
                    </Button>
                </div>
            <form noValidate onSubmit={submitUpdate} className="flex max-w-xl flex-col gap-4">
                <p>Email {contact.email ?? ''}</p>
                <p>Company {contact.company ?? ''}</p>
                <ColumnPill column={contact.column} />
                {contact.sentEmailBody && (
                    <Alert>
                        <AlertDescription data-sent-body={contact.sentEmailBody}>{contact.sentEmailBody}</AlertDescription>
                    </Alert>
                )}
                {contact.grandTotal !== null && <p data-saved-grand-total={contact.grandTotal}>Saved grand total {contact.grandTotal}</p>}
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
                <Field className="gap-1.5" data-invalid={updateErrors.description ? true : undefined}>
                    <FieldLabel htmlFor="update-description">Description</FieldLabel>
                    <Input
                        id="update-description"
                        value={update.data.description}
                        aria-invalid={updateErrors.description ? true : undefined}
                        onChange={(event) => update.setData('description', event.target.value)}
                    />
                    {updateErrors.description && <FieldError>{updateErrors.description}</FieldError>}
                </Field>
                <Field className="gap-1.5" data-invalid={updateErrors.notes ? true : undefined}>
                    <FieldLabel htmlFor="update-notes">Notes</FieldLabel>
                    <Input
                        id="update-notes"
                        value={update.data.notes}
                        aria-invalid={updateErrors.notes ? true : undefined}
                        onChange={(event) => update.setData('notes', event.target.value)}
                    />
                    {updateErrors.notes && <FieldError>{updateErrors.notes}</FieldError>}
                </Field>
                <Field className="gap-1.5">
                    <FieldLabel htmlFor="company-size">Company size</FieldLabel>
                    <SelectInput id="company-size" value={update.data.company_size} onChange={(event) => update.setData('company_size', event.target.value)}>
                        <option value="">Select a size</option>
                        {companySizes.map((size) => (
                            <option key={size} value={size}>
                                {size}
                            </option>
                        ))}
                    </SelectInput>
                </Field>
                <Field className="gap-1.5">
                    <FieldLabel htmlFor="contact-owner">Owner</FieldLabel>
                    <SelectInput id="contact-owner" value={update.data.owner_id} onChange={(event) => update.setData('owner_id', event.target.value)}>
                        {page.props.users.map((user) => (
                            <option key={user.id} value={String(user.id)}>
                                {user.name}
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
                <div id="qualify" className="flex scroll-mt-4 flex-col gap-4">
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
                    <FormActions>
                        <Button type="button" className="w-auto" onClick={submitQualify}>
                            Validate
                        </Button>
                    </FormActions>
                </div>
                <div id="estimate" className="flex scroll-mt-4 flex-col gap-4">
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
                    {sendError && <FieldError>{sendError}</FieldError>}
                    <FormActions>
                        <Button type="button" className="w-auto" onClick={submitEstimate}>
                            Send estimate
                        </Button>
                    </FormActions>
                </div>
            </form>
            </div>
        </>
    );
}
