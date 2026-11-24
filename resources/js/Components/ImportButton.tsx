import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/react';
import { Import } from 'lucide-react';
import { IconTooltip } from './IconTooltip';

export function ImportButton({ url, label }: { url: string; label: string }) {
    const form = useForm({});

    return (
        <IconTooltip label={label}>
            <Button
                type="button"
                variant="secondary"
                size="icon"
                aria-label={label}
                disabled={form.processing}
                onClick={() => form.post(url)}
            >
                <Import />
            </Button>
        </IconTooltip>
    );
}
