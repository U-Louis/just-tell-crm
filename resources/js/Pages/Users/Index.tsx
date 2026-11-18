import { Button, buttonVariants } from '@/components/ui/button';
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Head, Link, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { ConfirmDeleteDialog } from '../../Components/ConfirmDeleteDialog';
import type { UserListProps } from '../../types';

export default function Index({ users, count, query, previousUrl, nextUrl, csvUrl, createUrl, urls }: UserListProps) {
    const search = useForm({ q: query });
    const [destroyUrl, setDestroyUrl] = useState<string | null>(null);

    function submitSearch(event: FormEvent) {
        event.preventDefault();
        search.get(urls.index);
    }

    return (
        <>
            <Head title="Users" />
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-2">
                    <h1 className="text-2xl font-medium">Users</h1>
                    <Link href={createUrl} aria-label="Create user" className={buttonVariants({ variant: 'outline', size: 'icon-sm' })}>
                        +
                    </Link>
                </div>
                <a href={csvUrl} className={buttonVariants({ variant: 'outline', size: 'sm' })}>
                    CSV
                </a>
            </div>
            <p className="mt-2" data-count={count}>
                Count {count}
            </p>
            <form noValidate onSubmit={submitSearch} className="mt-4 flex items-end gap-2">
                <Field className="max-w-sm gap-1.5">
                    <FieldLabel htmlFor="user-search">Search</FieldLabel>
                    <Input id="user-search" name="q" value={search.data.q} onChange={(event) => search.setData('q', event.target.value)} />
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
                            <th scope="col" className="px-3 py-2 text-right font-medium">
                                <span className="sr-only">Delete</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {users.map((user) => (
                            <tr
                                key={user.id}
                                data-user-name={user.name}
                                className="hover:bg-muted/40 cursor-pointer border-t"
                                onClick={() => router.visit(user.showUrl)}
                            >
                                <td className="px-3 py-2 font-medium">{user.name}</td>
                                <td className="px-3 py-2 text-right">
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        onPointerDown={(event) => event.stopPropagation()}
                                        onClick={(event) => {
                                            event.stopPropagation();
                                            setDestroyUrl(user.destroyUrl);
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
            <div className="mt-4 flex gap-2">
                {previousUrl && (
                    <Link href={previousUrl} className={buttonVariants({ variant: 'outline', size: 'sm' })}>
                        Previous
                    </Link>
                )}
                {nextUrl && (
                    <Link href={nextUrl} className={buttonVariants({ variant: 'outline', size: 'sm' })}>
                        Next
                    </Link>
                )}
            </div>
            <ConfirmDeleteDialog
                open={destroyUrl !== null}
                destroyUrl={destroyUrl}
                title="Delete this user?"
                description="This removes the user."
                onOpenChange={(open) => {
                    if (!open) {
                        setDestroyUrl(null);
                    }
                }}
            />
        </>
    );
}
