import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Head, Link, useForm } from '@inertiajs/react';
import { Search } from 'lucide-react';
import type { FormEvent } from 'react';
import { IconTooltip } from '../../Components/IconTooltip';
import { ImportButton } from '../../Components/ImportButton';
import { ListPagination } from '../../Components/ListPagination';
import { PlusLink } from '../../Components/PlusLink';
import type { ProductListProps } from '../../types';

export default function Index({ products, count, page, pageCount, query, previousUrl, nextUrl, csvUrl, createUrl, importUrl, urls }: ProductListProps) {
    const search = useForm({ q: query });

    function submitSearch(event: FormEvent) {
        event.preventDefault();
        search.get(urls.index);
    }

    return (
        <>
            <Head title="Products" />
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-2">
                    <h1 className="text-2xl font-medium">Products</h1>
                    <PlusLink href={createUrl} label="Create product" />
                    <ImportButton url={importUrl} label="Import product" />
                </div>
                <a href={csvUrl} className={buttonVariants({ variant: 'secondary', size: 'sm' })}>
                    CSV
                </a>
            </div>
            <form noValidate onSubmit={submitSearch} className="mt-4 flex max-w-sm items-center gap-2">
                <Input
                    id="product-search"
                    name="q"
                    aria-label="Search"
                    value={search.data.q}
                    onChange={(event) => search.setData('q', event.target.value)}
                />
                <IconTooltip label="Search">
                    <Button type="submit" variant="outline" size="icon" aria-label="Search" className="cursor-pointer">
                        <Search />
                    </Button>
                </IconTooltip>
            </form>
            <div className="mt-4 overflow-hidden rounded-xl border">
            <ul className="flex flex-col gap-2 p-3">
                {products.map((product) => (
                    <li key={product.id} data-product-name={product.name}>
                        <Card size="sm">
                            <CardContent className="flex items-center justify-between gap-3">
                                <Link href={product.showUrl} className="font-medium">
                                    {product.name}
                                </Link>
                                <div className="flex items-center gap-3">
                                    <span>{product.unitPrice}</span>
                                    <Link href={product.showUrl} className={buttonVariants({ variant: 'outline', size: 'sm' })}>
                                        Update
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>
                    </li>
                ))}
            </ul>
            <ListPagination count={count} page={page} pageCount={pageCount} previousUrl={previousUrl} nextUrl={nextUrl} />
            </div>
        </>
    );
}
