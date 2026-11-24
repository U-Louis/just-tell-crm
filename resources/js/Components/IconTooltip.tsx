import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import type { ReactElement } from 'react';

export function IconTooltip({ label, children }: { label: string; children: ReactElement }) {
    return (
        <Tooltip>
            <TooltipTrigger render={children} />
            <TooltipContent>{label}</TooltipContent>
        </Tooltip>
    );
}
