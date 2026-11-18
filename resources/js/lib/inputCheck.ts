export type InputShape = 'text' | 'email' | 'number';

const TEXT_LIMIT = 255;

const MESSAGES = {
    text: 'Use at most 255 characters.',
    email: 'Enter a valid email address.',
    number: 'Enter a whole number of 0 or more.',
} as const;

const EMAIL_ADDRESS = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export function inputError(shape: InputShape, value: string): string | null {
    if (shape === 'text') {
        return Array.from(value.trim()).length > TEXT_LIMIT ? MESSAGES.text : null;
    }

    if (shape === 'email') {
        const email = value.trim();
        if (email === '') {
            return null;
        }

        return EMAIL_ADDRESS.test(email) ? null : MESSAGES.email;
    }

    return /^\d+$/.test(value.trim()) ? null : MESSAGES.number;
}
