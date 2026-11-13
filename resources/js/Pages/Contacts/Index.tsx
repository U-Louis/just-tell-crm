import { Head, Link } from '@inertiajs/react';
import type { ContactListProps } from '../../types';

export default function Index({ contacts }: ContactListProps) {
    return (
        <>
            <Head title="Contacts" />
            <h1>Contacts</h1>
            <ul>
                {contacts.map((contact) => (
                    <li key={contact.id} data-contact-name={contact.name}>
                        <Link href={contact.showUrl}>{contact.name}</Link>
                        <span>{contact.column}</span>
                    </li>
                ))}
            </ul>
        </>
    );
}
