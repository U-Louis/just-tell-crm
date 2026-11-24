import type { ReactNode } from 'react';

export function FormActions({ children }: { children: ReactNode }) {
    return <div className="flex w-full flex-wrap justify-end gap-2">{children}</div>;
}
