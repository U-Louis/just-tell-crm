import type { SharedError } from '../types';

export function ErrorText({ error }: { error: SharedError | null }) {
    if (!error?.code) {
        return null;
    }

    return (
        <p role="alert" data-error-code={error.code}>
            {error.message ?? 'Something unexpected went wrong.'}
        </p>
    );
}
