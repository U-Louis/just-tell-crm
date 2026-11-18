import { Head } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import type { EstimateListProps } from '../../types';

export default function Index({ estimates }: EstimateListProps) {
    return (
        <>
            <Head title="Estimates" />
            <h1 className="text-2xl font-medium">Estimates</h1>
            <ul className="mt-4 flex flex-col gap-2">
                {estimates.map((estimate) => (
                    <li key={estimate.id} data-estimate-contact={estimate.contactName} data-grand-total={estimate.grandTotal}>
                        <Card size="sm">
                            <CardContent className="flex items-center justify-between gap-3">
                                <span className="font-medium">{estimate.contactName}</span>
                                <span>{estimate.grandTotal}</span>
                            </CardContent>
                        </Card>
                    </li>
                ))}
            </ul>
        </>
    );
}
