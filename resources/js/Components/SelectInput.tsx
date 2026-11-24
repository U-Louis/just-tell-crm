import { cn } from '@/lib/utils';
import type { ComponentProps } from 'react';

export function SelectInput({ className, ...props }: ComponentProps<'select'>) {
    return (
        <select
            className={cn(
                'bg-input/50 focus-visible:border-ring focus-visible:ring-ring/30 h-9 w-full rounded-3xl border border-transparent px-3 text-sm outline-none focus-visible:ring-3',
                className,
            )}
            {...props}
        />
    );
}
