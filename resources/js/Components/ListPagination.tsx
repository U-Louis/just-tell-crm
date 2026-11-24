import { Button, buttonVariants } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { ReactNode } from 'react';

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
        <div className="flex items-center justify-between gap-3 border-t px-3 py-3">
            <p className="text-muted-foreground flex-1 text-sm">{count === 1 ? '1 item' : `${count} items`}</p>
            <div className="flex items-center gap-6">
                <p className="text-sm font-medium" data-count={count} data-page={page} data-page-count={pageCount}>
                    Page {page} of {pageCount}
                </p>
                <div className="flex items-center gap-2">
                    <PageLink href={previousUrl} label="Previous">
                        <ChevronLeft />
                    </PageLink>
                    <PageLink href={nextUrl} label="Next">
                        <ChevronRight />
                    </PageLink>
                </div>
            </div>
        </div>
    );
}

function PageLink({ href, label, children }: { href: string | null; label: string; children: ReactNode }) {
    if (href === null) {
        return (
            <Button type="button" variant="outline" size="icon" className="size-8" disabled aria-label={label}>
                {children}
            </Button>
        );
    }

    return (
        <Link href={href} aria-label={label} className={buttonVariants({ variant: 'outline', size: 'icon', className: 'size-8 cursor-pointer' })}>
            {children}
        </Link>
    );
}
