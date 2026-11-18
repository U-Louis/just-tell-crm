import { Component, type ReactNode } from 'react';
import { Alert, AlertDescription } from '@/components/ui/alert';

type Properties = {
    children: ReactNode;
};

type State = {
    failed: boolean;
};

export class ErrorBoundary extends Component<Properties, State> {
    state: State = { failed: false };

    static getDerivedStateFromError(): State {
        return { failed: true };
    }

    render(): ReactNode {
        if (this.state.failed) {
            return (
                <Alert variant="destructive" data-error-code="error.unexpected">
                    <AlertDescription>Something unexpected went wrong.</AlertDescription>
                </Alert>
            );
        }

        return this.props.children;
    }
}
