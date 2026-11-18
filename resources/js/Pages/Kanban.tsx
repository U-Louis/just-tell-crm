import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { KanbanBoard, type KanbanCardRenderer, type KanbanData } from '@/components/kanban-board';
import { Button } from '@/components/ui/button';
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import type { KanbanColumn, KanbanProps } from '../types';

type ContactCard = {
    name: string;
    showUrl: string;
};

const contactRenderer: KanbanCardRenderer<ContactCard> = {
    id: 'contact',
    label: 'Contact',
    render: (data) => (
        <Link href={data.showUrl} data-contact-name={data.name} className="text-sm font-medium">
            {data.name}
        </Link>
    ),
};

function boardData(columns: KanbanColumn[]): KanbanData {
    return {
        columns: columns.map((column) => ({
            id: column.name,
            title: column.name,
            items: column.contacts.map((contact) => ({
                id: String(contact.id),
                rendererId: 'contact',
                data: { name: contact.name, showUrl: contact.showUrl },
            })),
        })),
    };
}

export default function Kanban({ columns, urls }: KanbanProps) {
    const form = useForm({ name: '' });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(urls.store);
    }

    return (
        <>
            <Head title="Board" />
            <form noValidate onSubmit={submit} className="mb-4 flex items-end gap-2">
                <Field className="max-w-sm gap-1.5">
                    <FieldLabel htmlFor="contact-name">Name</FieldLabel>
                    <Input id="contact-name" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} />
                </Field>
                <Button type="submit">Create contact</Button>
            </form>
            <div className="h-[32rem]">
                <KanbanBoard readOnly aria-label="Board" className="h-full" renderers={[contactRenderer]} data={boardData(columns)} />
            </div>
        </>
    );
}
