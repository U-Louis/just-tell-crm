import { Head } from '@inertiajs/react';
import type { EstimateListProps } from '../../types';

export default function Index({ estimates }: EstimateListProps) {
    return (
        <>
            <Head title="Estimates" />
            <h1>Estimates</h1>
            <ul>
                {estimates.map((estimate) => (
                    <li key={estimate.id} data-estimate-contact={estimate.contactName} data-grand-total={estimate.grandTotal}>
                        {estimate.contactName} {estimate.grandTotal}
                    </li>
                ))}
            </ul>
        </>
    );
}
