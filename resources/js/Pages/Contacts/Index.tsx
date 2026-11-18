import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import type { ContactListProps } from '../../types';

export default function Index({ contacts }: ContactListProps) {
    return (
        <>
            <Head title="Contacts" />
            <h1 className="text-2xl font-medium">Contacts</h1>
            <ul className="mt-4 flex flex-col gap-2">
                {contacts.map((contact) => (
                    <li key={contact.id} data-contact-name={contact.name}>
                        <Card size="sm">
                            <CardContent className="flex items-center justify-between gap-3">
                                <Link href={contact.showUrl} className="font-medium">
                                    {contact.name}
                                </Link>
                                <Badge variant="outline">{contact.column}</Badge>
                            </CardContent>
                        </Card>
                    </li>
                ))}
            </ul>
        </>
    );
}
