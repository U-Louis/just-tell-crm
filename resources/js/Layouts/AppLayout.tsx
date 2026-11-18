import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { ActingUserControl } from '../Components/ActingUserControl';
import { ErrorText } from '../Components/ErrorText';
import type { SharedProps } from '../types';

export default function AppLayout({ children }: { children: ReactNode }) {
    const page = usePage<SharedProps>();
    const { props } = page;

    function navClass(href: string, matchPrefix: boolean) {
        const current = page.url.split('?')[0] ?? page.url;
        const active = matchPrefix ? current === href || current.startsWith(`${href}/`) : current === href;

        return cn(buttonVariants({ variant: active ? 'secondary' : 'ghost', size: 'sm' }));
    }

    return (
        <div className="min-h-screen bg-background">
            <header className="border-b bg-card" data-acting-user={props.actingUser?.name ?? ''}>
                <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-3">
                    <div className="flex flex-wrap items-center gap-4">
                        <Link href={props.kanbanUrl} className="font-heading text-base font-medium">
                            Just Tell CRM
                        </Link>
                        <nav className="flex items-center gap-1">
                            <Link href={props.kanbanUrl} className={navClass(props.kanbanUrl, false)}>
                                Board
                            </Link>
                            <Link href={props.contactsUrl} className={navClass(props.contactsUrl, true)}>
                                Contacts
                            </Link>
                            <Link href={props.estimatesUrl} className={navClass(props.estimatesUrl, true)}>
                                Estimates
                            </Link>
                        </nav>
                    </div>
                    <ActingUserControl users={props.users} actingUser={props.actingUser} selectUrl={props.selectActingUserUrl} />
                </div>
            </header>
            <div className="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-4">
                {!props.actingUser && (
                    <Alert role="status">
                        <AlertDescription>Choose an acting user.</AlertDescription>
                    </Alert>
                )}
                <ErrorText error={props.error} />
                <main>{children}</main>
            </div>
        </div>
    );
}
