import { Component, type ReactNode } from 'react';

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
                <p role="alert" data-error-code="error.unexpected">
                    Something unexpected went wrong.
                </p>
            );
        }

        return this.props.children;
    }
}
