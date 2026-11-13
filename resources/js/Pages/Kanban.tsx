import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import type { KanbanProps } from '../types';

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
                <div>
                    <label htmlFor="contact-name">Name</label>
                    <input id="contact-name" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} />
                </div>
                <button type="submit">Create contact</button>
            </form>
            <div className="grid gap-4 md:grid-cols-3">
                {columns.map((column) => (
                    <section key={column.name} data-column={column.name} aria-label={column.name} className="border p-3">
                        <h2>{column.name}</h2>
                        <ul>
                            {column.contacts.map((contact) => (
                                <li key={contact.id}>
                                    <Link href={contact.showUrl} data-contact-name={contact.name}>
                                        {contact.name}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </section>
                ))}
            </div>
        </>
    );
}
