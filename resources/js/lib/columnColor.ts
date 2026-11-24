import { DEFAULT_PALETTE, findSwatch, swatchCssColor } from '@/components/kanban-board/lib/palette';

export const COLUMN_SWATCH: Record<string, string> = {
    new: 'amber',
    qualified: 'emerald',
    'estimate sent': 'sky',
};

export function columnAccent(column: string): string {
    return swatchCssColor(findSwatch(DEFAULT_PALETTE, COLUMN_SWATCH[column])) ?? 'var(--muted-foreground)';
}
