import { Alert, AlertDescription } from '@/components/ui/alert';
import type { SharedError } from '../types';

export function ErrorText({ error }: { error: SharedError | null }) {
    if (!error?.code) {
        return null;
    }

    return (
        <Alert variant="destructive" data-error-code={error.code}>
            <AlertDescription>{error.message ?? 'Something unexpected went wrong.'}</AlertDescription>
        </Alert>
    );
}
