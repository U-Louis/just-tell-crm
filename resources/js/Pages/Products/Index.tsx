import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Head, Link, useForm } from '@inertiajs/react';
import { Search } from 'lucide-react';
import type { FormEvent } from 'react';
import { ListPagination } from '../../Components/ListPagination';
import type { ProductListProps } from '../../types';

export default function Index({ products, count, page, pageCount, query, previousUrl, nextUrl, csvUrl, createUrl, urls }: ProductListProps) {
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
                    <Link href={createUrl} aria-label="Create product" className={buttonVariants({ variant: 'secondary', size: 'icon', className: 'text-base font-bold' })}>
                        +
                    </Link>
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
                <Button type="submit" variant="outline" size="icon" aria-label="Search">
                    <Search />
                </Button>
            </form>
            <ul className="mt-4 flex flex-col gap-2">
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
        </>
    );
}
