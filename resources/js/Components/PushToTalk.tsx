import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState, type PointerEvent } from 'react';
import type { SharedProps } from '../types';

const MIN_HOLD_MS = 300;
const TALK_KEY = 'v';

export function PushToTalk() {
    const page = usePage<SharedProps>();
    const { voice, voiceStoreUrl, actingUser } = page.props;
    const form = useForm<{ clip: File | null }>({ clip: null });
    const [holding, setHolding] = useState(false);
    const [localMessage, setLocalMessage] = useState<string | null>(null);
    const recorder = useRef<MediaRecorder | null>(null);
    const stream = useRef<MediaStream | null>(null);
    const chunks = useRef<Blob[]>([]);
    const pressedAt = useRef(0);
    const startedAt = useRef(0);
    const pressed = useRef(false);
    const keyboardHeld = useRef(false);
    const sawOpenRun = useRef(false);
    const beginRef = useRef<(event?: PointerEvent<HTMLButtonElement>) => void>(() => {});
    const endRef = useRef<() => void>(() => {});
    const busy = form.processing || voice?.status === 'queued' || voice?.status === 'running';

    useEffect(() => {
        if (!voice) {
            return;
        }

        if (voice.status === 'queued' || voice.status === 'running') {
            sawOpenRun.current = true;
            const id = window.setInterval(() => {
                router.reload({ only: ['voice'] });
            }, 2000);

            return () => window.clearInterval(id);
        }

        if (sawOpenRun.current && (voice.status === 'finished' || voice.status === 'failed')) {
            sawOpenRun.current = false;
            router.visit(page.url, { preserveScroll: true });
        }
    }, [page.url, voice]);

    function stopTracks() {
        stream.current?.getTracks().forEach((track) => track.stop());
        stream.current = null;
    }

    async function begin(event?: PointerEvent<HTMLButtonElement>) {
        if (!actingUser || busy || pressed.current) {
            return;
        }

        event?.currentTarget.setPointerCapture(event.pointerId);
        pressed.current = true;
        pressedAt.current = Date.now();
        setLocalMessage(null);

        try {
            const media = await navigator.mediaDevices.getUserMedia({ audio: true });
            if (!pressed.current) {
                media.getTracks().forEach((track) => track.stop());
                return;
            }

            stream.current = media;
            const mimeType = MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : undefined;
            const mediaRecorder = new MediaRecorder(media, mimeType ? { mimeType } : undefined);
            chunks.current = [];
            mediaRecorder.addEventListener('dataavailable', (data) => {
                if (data.data.size > 0) {
                    chunks.current.push(data.data);
                }
            });
            mediaRecorder.start();
            recorder.current = mediaRecorder;
            startedAt.current = Date.now();
            setHolding(true);
        } catch {
            const short = Date.now() - pressedAt.current < MIN_HOLD_MS;
            pressed.current = false;
            stopTracks();
            setHolding(false);
            if (!short) {
                setLocalMessage('The microphone is unavailable.');
            }
        }
    }

    function end() {
        const mediaRecorder = recorder.current;
        pressed.current = false;
        if (mediaRecorder === null || mediaRecorder.state === 'inactive') {
            setHolding(false);
            return;
        }

        const heldFor = Date.now() - startedAt.current;
        mediaRecorder.addEventListener('stop', () => {
            const blob = new Blob(chunks.current, { type: mediaRecorder.mimeType || 'audio/webm' });
            recorder.current = null;
            stopTracks();
            setHolding(false);
            if (heldFor < MIN_HOLD_MS || blob.size === 0) {
                return;
            }

            form.transform(() => ({
                clip: new File([blob], 'clip.webm', { type: blob.type || 'audio/webm' }),
            }));
            form.post(voiceStoreUrl, { forceFormData: true, preserveScroll: true });
        });
        mediaRecorder.stop();
    }

    beginRef.current = begin;
    endRef.current = end;

    useEffect(() => {
        function typing(target: EventTarget | null): boolean {
            if (!(target instanceof HTMLElement)) {
                return false;
            }

            const tag = target.tagName;

            return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || target.isContentEditable;
        }

        function onKeyDown(event: KeyboardEvent) {
            if (event.repeat || event.altKey || event.ctrlKey || event.metaKey) {
                return;
            }
            if (event.key.toLowerCase() !== TALK_KEY || typing(event.target) || pressed.current) {
                return;
            }

            event.preventDefault();
            keyboardHeld.current = true;
            beginRef.current();
        }

        function onKeyUp(event: KeyboardEvent) {
            if (event.key.toLowerCase() !== TALK_KEY || !keyboardHeld.current) {
                return;
            }

            keyboardHeld.current = false;
            event.preventDefault();
            endRef.current();
        }

        window.addEventListener('keydown', onKeyDown);
        window.addEventListener('keyup', onKeyUp);

        return () => {
            window.removeEventListener('keydown', onKeyDown);
            window.removeEventListener('keyup', onKeyUp);
        };
    }, []);

    const statusLabel = voice?.status === 'queued' || voice?.status === 'running' ? 'Working on it.' : null;

    return (
        <div className="fixed right-4 bottom-4 z-50 flex max-w-sm flex-col items-end gap-2">
            {(localMessage || voice?.transcript || voice?.result || statusLabel) && (
                <div className="bg-card w-full rounded-lg border p-3 text-sm shadow" data-voice-status={voice?.status ?? 'idle'}>
                    {localMessage && (
                        <p role="status" data-voice-message={localMessage}>
                            {localMessage}
                        </p>
                    )}
                    {voice?.transcript && <p data-voice-transcript={voice.transcript}>{voice.transcript}</p>}
                    {statusLabel && <p role="status">{statusLabel}</p>}
                    {voice?.result && (voice.status === 'finished' || voice.status === 'failed') && <p data-voice-result={voice.result}>{voice.result}</p>}
                </div>
            )}
            <Button
                type="button"
                data-voice-button
                data-voice-key={TALK_KEY}
                data-voice-holding={holding ? 'true' : 'false'}
                disabled={!actingUser || busy}
                aria-pressed={holding}
                aria-keyshortcuts={TALK_KEY}
                className={cn(
                    'size-24 rounded-full text-base font-semibold shadow-lg transition-transform',
                    holding && 'scale-95 ring-4 ring-ring shadow-inner',
                )}
                onPointerDown={begin}
                onPointerUp={end}
                onPointerCancel={end}
                onContextMenu={(event) => event.preventDefault()}
            >
                Talk ({TALK_KEY})
            </Button>
        </div>
    );
}
