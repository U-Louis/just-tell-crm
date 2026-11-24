import { buttonVariants } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { IconTooltip } from './IconTooltip';

export function PlusLink({ href, label }: { href: string; label: string }) {
    return (
        <IconTooltip label={label}>
            <Link href={href} aria-label={label} className={buttonVariants({ variant: 'secondary', size: 'icon', className: 'text-base' })}>
                <span className="font-bold">+</span>
            </Link>
        </IconTooltip>
    );
}
