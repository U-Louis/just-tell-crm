import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { ActingUserControl } from '../Components/ActingUserControl';
import { ErrorText } from '../Components/ErrorText';
import type { SharedProps } from '../types';

export default function AppLayout({ children }: { children: ReactNode }) {
    const { props } = usePage<SharedProps>();

    return (
        <div>
            <header className="flex items-center justify-between gap-4 border-b p-4" data-acting-user={props.actingUser?.name ?? ''}>
                <div className="flex items-center gap-4">
                    <Link href={props.kanbanUrl}>Just Tell CRM</Link>
                    <nav className="flex items-center gap-4">
                        <Link href={props.kanbanUrl}>Board</Link>
                        <Link href={props.contactsUrl}>Contacts</Link>
                        <Link href={props.estimatesUrl}>Estimates</Link>
                    </nav>
                </div>
                <ActingUserControl users={props.users} actingUser={props.actingUser} selectUrl={props.selectActingUserUrl} />
            </header>
            {!props.actingUser && <p role="status">Choose an acting user.</p>}
            <ErrorText error={props.error} />
            <main className="p-4">{children}</main>
        </div>
    );
}
