import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Search } from 'lucide-react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { ConfirmDeleteDialog } from '../../Components/ConfirmDeleteDialog';
import { ListPagination } from '../../Components/ListPagination';
import type { EstimateListProps } from '../../types';

export default function Index({ estimates, count, page, pageCount, query, previousUrl, nextUrl, csvUrl, createUrl, urls }: EstimateListProps) {
    const search = useForm({ q: query });
    const [destroyUrl, setDestroyUrl] = useState<string | null>(null);

    function submitSearch(event: FormEvent) {
        event.preventDefault();
        search.get(urls.index);
    }

    return (
        <>
            <Head title="Estimates" />
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-2">
                    <h1 className="text-2xl font-medium">Estimates</h1>
                    <Link href={createUrl} aria-label="Create estimate" className={buttonVariants({ variant: 'secondary', size: 'icon', className: 'text-base' })}>
                        <span className="font-bold">+</span>
                    </Link>
                </div>
                <a href={csvUrl} className={buttonVariants({ variant: 'secondary', size: 'sm' })}>
                    CSV
                </a>
            </div>
            <form noValidate onSubmit={submitSearch} className="mt-4 flex max-w-sm items-center gap-2">
                <Input
                    id="estimate-search"
                    name="q"
                    aria-label="Search"
                    value={search.data.q}
                    onChange={(event) => search.setData('q', event.target.value)}
                />
                <Button type="submit" variant="outline" size="icon" aria-label="Search">
                    <Search />
                </Button>
            </form>
            <div className="mt-4 overflow-hidden rounded-xl border">
                <table className="w-full border-collapse text-sm">
                    <thead className="bg-muted/50 text-left">
                        <tr>
                            <th scope="col" className="px-3 py-2 font-medium">
                                Contact
                            </th>
                            <th scope="col" className="px-3 py-2 font-medium">
                                Grand total
                            </th>
                            <th scope="col" className="px-3 py-2 text-right font-medium">
                                <span className="sr-only">Delete</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {estimates.map((estimate) => (
                            <tr
                                key={estimate.id}
                                data-estimate-contact={estimate.contactName}
                                data-grand-total={estimate.grandTotal}
                                className="hover:bg-muted/40 cursor-pointer border-t"
                                onClick={() => router.visit(estimate.showUrl)}
                            >
                                <td className="px-3 py-2 font-medium">{estimate.contactName}</td>
                                <td className="px-3 py-2">{estimate.grandTotal}</td>
                                <td className="px-3 py-2 text-right">
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        onPointerDown={(event) => event.stopPropagation()}
                                        onClick={(event) => {
                                            event.stopPropagation();
                                            setDestroyUrl(estimate.destroyUrl);
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
                title="Delete this estimate?"
                description="This removes the estimate."
                onOpenChange={(open) => {
                    if (!open) {
                        setDestroyUrl(null);
                    }
                }}
            />
        </>
    );
}
