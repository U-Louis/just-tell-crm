export type { Auth, BreadcrumbItem, NavGroup, NavItem, SharedData, User } from './types/index';

export type ActingUser = {
    id: number;
    name: string;
};

export type SharedError = {
    code: string;
    feature_id: string | null;
    message: string | null;
    fields?: Record<string, string>;
};

export type SharedProps = {
    actingUser: ActingUser | null;
    users: ActingUser[];
    error: SharedError | null;
    correlationId: string | null;
    kanbanUrl: string;
    selectActingUserUrl: string;
};

export type KanbanContact = {
    id: number;
    name: string;
    showUrl: string;
};

export type KanbanColumn = {
    name: string;
    contacts: KanbanContact[];
};

export type KanbanProps = {
    columns: KanbanColumn[];
    urls: {
        store: string;
    };
};

export type ProductOption = {
    id: number;
    name: string;
    unitPrice: number;
};

export type ContactDetail = {
    name: string;
    email: string | null;
    company: string | null;
    column: string;
    sentEmailBody: string | null;
    grandTotal: number | null;
};

export type ContactPageProps = {
    contact: ContactDetail;
    products: ProductOption[];
    urls: {
        qualify: string;
        estimate: string;
    };
};
