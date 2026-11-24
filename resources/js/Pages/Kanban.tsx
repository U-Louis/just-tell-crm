import { KanbanBoard, type KanbanCardRenderer, type KanbanData, type KanbanItem } from '@/components/kanban-board';
import { Button, buttonVariants } from '@/components/ui/button';
import { COLUMN_SWATCH } from '@/lib/columnColor';
import { Head, Link, router, useForm } from '@inertiajs/react';
import type { KanbanColumn, KanbanProps } from '../types';

type ContactCard = {
    name: string;
    showUrl: string;
    nextColumn: string | null;
    moveUrl: string | null;
};

const columnBands = ['amber', 'emerald', 'sky'] as const;

const contactRenderer: KanbanCardRenderer<ContactCard> = {
    id: 'contact',
    label: 'Contact',
    render: (data) => (
        <Link href={data.showUrl} draggable={false} data-contact-name={data.name} className="block py-1 text-sm font-medium">
            {data.name}
        </Link>
    ),
};

function boardData(columns: KanbanColumn[]): KanbanData {
    return {
        columns: columns.map((column, index) => ({
            id: column.name,
            title: column.name,
            color: COLUMN_SWATCH[column.name] ?? columnBands[Math.min(index, columnBands.length - 1)],
            allowReorder: false,
            items: column.contacts.map((contact) => ({
                id: String(contact.id),
                rendererId: 'contact',
                data: {
                    name: contact.name,
                    showUrl: contact.showUrl,
                    nextColumn: contact.nextColumn,
                    moveUrl: contact.moveUrl,
                },
            })),
        })),
    };
}

function card(item: KanbanItem): ContactCard {
    return item.data as ContactCard;
}

function openMove(moveUrl: string) {
    const url = new URL(moveUrl, window.location.origin);
    const hash = url.hash;
    url.hash = '';
    router.visit(`${url.pathname}${url.search}`, {
        onFinish: () => {
            if (hash !== '') {
                window.location.hash = hash;
            }
        },
    });
}

export default function Kanban({ columns, createUrl, importUrl }: KanbanProps) {
    const data = boardData(columns);
    const importer = useForm({});

    return (
        <>
            <Head title="Board" />
            <div className="mb-4 flex flex-wrap items-center gap-2">
                <Link href={createUrl} className={buttonVariants({ className: 'w-fit' })}>
                    New contact
                </Link>
                <Button type="button" variant="secondary" className="w-auto" disabled={importer.processing} onClick={() => importer.post(importUrl)}>
                    Import new contact
                </Button>
            </div>
            <div className="h-[32rem]">
                <KanbanBoard
                    aria-label="Board"
                    className="h-full"
                    lockStructure
                    renderers={[contactRenderer]}
                    data={data}
                    canMoveItem={({ item, toColumnId }) => card(item).nextColumn === toColumnId}
                    onItemMove={({ item }) => {
                        const moveUrl = card(item).moveUrl;
                        if (moveUrl !== null) {
                            openMove(moveUrl);
                        }
                    }}
                />
            </div>
        </>
    );
}
