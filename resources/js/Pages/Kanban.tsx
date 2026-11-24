import { KanbanBoard, type KanbanCardRenderer, type KanbanData, type KanbanItem } from '@/components/kanban-board';
import { Button } from '@/components/ui/button';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { submissionFailed } from '@/lib/clearOnSuccess';
import { inputError } from '@/lib/inputCheck';
import { Head, Link, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import type { KanbanColumn, KanbanProps } from '../types';

type ContactCard = {
    name: string;
    showUrl: string;
    nextColumn: string | null;
    moveUrl: string | null;
};

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
        columns: columns.map((column) => ({
            id: column.name,
            title: column.name,
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

export default function Kanban({ columns, urls }: KanbanProps) {
    const form = useForm({ name: '' });
    const [nameError, setNameError] = useState<string | null>(null);
    const data = boardData(columns);

    function submit(event: FormEvent) {
        event.preventDefault();
        const error = inputError('text', form.data.name);
        setNameError(error);
        if (error) {
            return;
        }

        form.post(urls.store, {
            onSuccess: (page) => {
                if (submissionFailed(page)) {
                    return;
                }

                form.setData({ name: '' });
            },
        });
    }

    return (
        <>
            <Head title="Board" />
            <form noValidate onSubmit={submit} className="mb-4 flex items-end gap-2">
                <Field className="max-w-sm gap-1.5" data-invalid={nameError ? true : undefined}>
                    <FieldLabel htmlFor="contact-name">Name</FieldLabel>
                    <Input
                        id="contact-name"
                        value={form.data.name}
                        aria-invalid={nameError ? true : undefined}
                        onChange={(event) => form.setData('name', event.target.value)}
                    />
                    {nameError && <FieldError>{nameError}</FieldError>}
                </Field>
                <Button type="submit">Create contact</Button>
            </form>
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
