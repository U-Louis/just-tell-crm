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
    contactsUrl: string;
    estimatesUrl: string;
    usersUrl: string;
    productsUrl: string;
    selectActingUserUrl: string;
};

export type ContactListItem = {
    id: number;
    name: string;
    column: string;
    showUrl: string;
    destroyUrl: string;
};

export type ContactListProps = {
    contacts: ContactListItem[];
    count: number;
    query: string;
    previousUrl: string | null;
    nextUrl: string | null;
    csvUrl: string;
    createUrl: string;
    urls: {
        index: string;
    };
};

export type ContactCreateProps = {
    urls: {
        store: string;
    };
};

export type EstimateListItem = {
    id: number;
    contactName: string;
    grandTotal: number;
    showUrl: string;
    destroyUrl: string;
};

export type EstimateListProps = {
    estimates: EstimateListItem[];
    count: number;
    query: string;
    previousUrl: string | null;
    nextUrl: string | null;
    csvUrl: string;
    createUrl: string;
    urls: {
        index: string;
    };
};

export type KanbanContact = {
    id: number;
    name: string;
    showUrl: string;
    nextColumn: string | null;
    moveUrl: string | null;
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

export type EstimateCreateContact = {
    id: number;
    name: string;
    storeUrl: string;
};

export type EstimateCreateProps = {
    contacts: EstimateCreateContact[];
    products: ProductOption[];
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
        update: string;
        destroy: string;
        pdf: string;
    };
};

export type UserListItem = {
    id: number;
    name: string;
    showUrl: string;
    destroyUrl: string;
};

export type UserListProps = {
    users: UserListItem[];
    count: number;
    query: string;
    previousUrl: string | null;
    nextUrl: string | null;
    csvUrl: string;
    createUrl: string;
    urls: {
        index: string;
    };
};

export type UserCreateProps = {
    urls: {
        store: string;
    };
};

export type UserPageProps = {
    user: {
        id: number;
        name: string;
    };
    urls: {
        update: string;
        destroy: string;
        pdf: string;
    };
};

export type ProductListItem = {
    id: number;
    name: string;
    unitPrice: number;
    showUrl: string;
};

export type ProductListProps = {
    products: ProductListItem[];
    count: number;
    query: string;
    previousUrl: string | null;
    nextUrl: string | null;
    csvUrl: string;
    urls: {
        index: string;
        store: string;
    };
};

export type ProductPageProps = {
    product: {
        id: number;
        name: string;
        unitPrice: number;
    };
    urls: {
        update: string;
        destroy: string;
        pdf: string;
    };
};

export type EstimateLineDetail = {
    productId: number;
    productName: string;
    quantity: number;
    unitPrice: number;
    currentUnitPrice: number;
};

export type EstimatePageProps = {
    estimate: {
        id: number;
        contactName: string;
        grandTotal: number;
        lines: EstimateLineDetail[];
    };
    urls: {
        update: string;
        destroy: string;
        pdf: string;
    };
};
