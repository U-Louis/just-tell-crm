import { buttonVariants } from '@/components/ui/button';
import { Link } from '@inertiajs/react';

export function ListPagination({
    count,
    page,
    pageCount,
    previousUrl,
    nextUrl,
}: {
    count: number;
    page: number;
    pageCount: number;
    previousUrl: string | null;
    nextUrl: string | null;
}) {
    return (
        <div className="mt-4 flex flex-wrap items-center gap-2">
            {previousUrl && (
                <Link href={previousUrl} className={buttonVariants({ variant: 'outline', size: 'sm' })}>
                    Previous
                </Link>
            )}
            <p data-count={count} data-page={page} data-page-count={pageCount}>
                Page {page} of {pageCount} ({count})
            </p>
            {nextUrl && (
                <Link href={nextUrl} className={buttonVariants({ variant: 'outline', size: 'sm' })}>
                    Next
                </Link>
            )}
        </div>
    );
}
