import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { type ReactNode } from 'react';
import { createRoot } from 'react-dom/client';
import { route as routeFn } from 'ziggy-js';
import { ErrorBoundary } from './Components/ErrorBoundary';
import AppLayout from './Layouts/AppLayout';

declare global {
    const route: typeof routeFn;
}

const appName = import.meta.env.VITE_APP_NAME || 'Just Tell CRM';

type PageModule = {
    default: {
        layout?: (page: ReactNode) => ReactNode;
    };
};

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')).then((module) => {
            const page = module as PageModule;
            page.default.layout = (children) => <AppLayout>{children}</AppLayout>;

            return module;
        }),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <ErrorBoundary>
                <App {...props} />
            </ErrorBoundary>,
        );
    },
    progress: false,
});
