import { Alert, AlertDescription } from '@/components/ui/alert';
import { buttonVariants } from '@/components/ui/button';
import { TooltipProvider } from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { ActingUserControl } from '../Components/ActingUserControl';
import { ErrorText } from '../Components/ErrorText';
import { PushToTalk } from '../Components/PushToTalk';
import type { SharedProps } from '../types';

function pathname(url: string): string {
    const withoutQuery = url.split('?')[0] ?? url;
    if (withoutQuery.startsWith('http://') || withoutQuery.startsWith('https://')) {
        try {
            return new URL(withoutQuery).pathname || '/';
        } catch {
            return withoutQuery;
        }
    }

    return withoutQuery === '' ? '/' : withoutQuery;
}

function sectionActive(currentUrl: string, href: string, matchPrefix: boolean) {
    const current = pathname(currentUrl);
    const target = pathname(href);

    return matchPrefix ? current === target || current.startsWith(`${target}/`) : current === target;
}

export default function AppLayout({ children }: { children: ReactNode }) {
    const page = usePage<SharedProps>();
    const { props } = page;
    const sections = [
        { href: props.kanbanUrl, label: 'Board', matchPrefix: false },
        { href: props.contactsUrl, label: 'Contacts', matchPrefix: true },
        { href: props.estimatesUrl, label: 'Estimates', matchPrefix: true },
        { href: props.usersUrl, label: 'Users', matchPrefix: true },
        { href: props.productsUrl, label: 'Products', matchPrefix: true },
    ];

    return (
        <TooltipProvider delay={300}>
            <div className="bg-background min-h-screen">
            <header className="bg-card border-b" data-acting-user={props.actingUser?.name ?? ''}>
                <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-3">
                    <div className="flex flex-wrap items-center gap-4">
                        <Link href={props.kanbanUrl} className="font-heading text-base font-medium">
                            Just Tell CRM
                        </Link>
                        <nav className="flex items-center gap-1">
                            {sections.map((section) => {
                                const active = sectionActive(page.url, section.href, section.matchPrefix);

                                return (
                                    <Link
                                        key={section.label}
                                        href={section.href}
                                        className={cn(buttonVariants({ variant: active ? 'default' : 'ghost', size: 'sm' }))}
                                        aria-current={active ? 'page' : undefined}
                                    >
                                        {section.label}
                                    </Link>
                                );
                            })}
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
            <PushToTalk />
        </div>
        </TooltipProvider>
    );
}
