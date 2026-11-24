import { columnAccent } from '@/lib/columnColor';

export function ColumnPill({ column }: { column: string }) {
    const accent = columnAccent(column);

    return (
        <span
            data-column={column}
            className="inline-flex w-fit items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium"
            style={{ borderColor: accent, color: accent }}
        >
            <span aria-hidden className="size-2 rounded-full" style={{ backgroundColor: accent }} />
            {column}
        </span>
    );
}
