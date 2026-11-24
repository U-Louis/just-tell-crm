import { Badge } from '@/components/ui/badge';
import { Button, buttonVariants } from '@/components/ui/button';
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Head, Link, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { ConfirmDeleteDialog } from '../../Components/ConfirmDeleteDialog';
import { ListPagination } from '../../Components/ListPagination';
import type { ContactListProps } from '../../types';

export default function Index({ contacts, count, page, pageCount, query, previousUrl, nextUrl, csvUrl, createUrl, urls }: ContactListProps) {
    const search = useForm({ q: query });
    const [destroyUrl, setDestroyUrl] = useState<string | null>(null);

    function submitSearch(event: FormEvent) {
        event.preventDefault();
        search.get(urls.index);
    }

    return (
        <>
            <Head title="Contacts" />
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-2">
                    <h1 className="text-2xl font-medium">Contacts</h1>
                    <Link href={createUrl} aria-label="Create contact" className={buttonVariants({ variant: 'secondary', size: 'icon', className: 'text-base font-bold' })}>
                        +
                    </Link>
                </div>
                <a href={csvUrl} className={buttonVariants({ variant: 'secondary', size: 'sm' })}>
                    CSV
                </a>
            </div>
            <form noValidate onSubmit={submitSearch} className="mt-4 flex items-end gap-2">
                <Field className="max-w-sm gap-1.5">
                    <FieldLabel htmlFor="contact-search">Search</FieldLabel>
                    <Input id="contact-search" name="q" value={search.data.q} onChange={(event) => search.setData('q', event.target.value)} />
                </Field>
                <Button type="submit" variant="outline">
                    Search
                </Button>
            </form>
            <div className="mt-4 overflow-hidden rounded-xl border">
                <table className="w-full border-collapse text-sm">
                    <thead className="bg-muted/50 text-left">
                        <tr>
                            <th scope="col" className="px-3 py-2 font-medium">
                                Name
                            </th>
                            <th scope="col" className="px-3 py-2 font-medium">
                                Column
                            </th>
                            <th scope="col" className="px-3 py-2 text-right font-medium">
                                <span className="sr-only">Delete</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {contacts.map((contact) => (
                            <tr
                                key={contact.id}
                                data-contact-name={contact.name}
                                className="hover:bg-muted/40 cursor-pointer border-t"
                                onClick={() => router.visit(contact.showUrl)}
                            >
                                <td className="px-3 py-2 font-medium">{contact.name}</td>
                                <td className="px-3 py-2">
                                    <Badge variant="outline">{contact.column}</Badge>
                                </td>
                                <td className="px-3 py-2 text-right">
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        onPointerDown={(event) => event.stopPropagation()}
                                        onClick={(event) => {
                                            event.stopPropagation();
                                            setDestroyUrl(contact.destroyUrl);
                                        }}
                                    >
                                        Delete
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <ListPagination count={count} page={page} pageCount={pageCount} previousUrl={previousUrl} nextUrl={nextUrl} />
            <ConfirmDeleteDialog
                open={destroyUrl !== null}
                destroyUrl={destroyUrl}
                title="Delete this contact?"
                description="This removes the contact."
                onOpenChange={(open) => {
                    if (!open) {
                        setDestroyUrl(null);
                    }
                }}
            />
        </>
    );
}
