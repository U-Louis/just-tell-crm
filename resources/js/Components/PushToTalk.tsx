import { MicButton } from '@/components/ui/mic-button';
import { router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState, type PointerEvent } from 'react';
import type { SharedProps } from '../types';

const MIN_HOLD_MS = 300;
const TALK_KEY = 'v';
const MIC_UNAVAILABLE = 'The microphone is unavailable.';

async function microphoneAvailable(): Promise<boolean> {
    if (!navigator.mediaDevices?.enumerateDevices) {
        return false;
    }

    try {
        const permission = await navigator.permissions.query({ name: 'microphone' });
        if (permission.state === 'denied') {
            return false;
        }
    } catch {
        // This browser does not expose the microphone permission.
    }

    try {
        const devices = await navigator.mediaDevices.enumerateDevices();

        return devices.some((device) => device.kind === 'audioinput');
    } catch {
        return false;
    }
}

export function PushToTalk() {
    const page = usePage<SharedProps>();
    const { voice, voiceStoreUrl, actingUser } = page.props;
    const form = useForm<{ clip: File | null }>({ clip: null });
    const [holding, setHolding] = useState(false);
    const [localMessage, setLocalMessage] = useState<string | null>(null);
    const [micUnavailable, setMicUnavailable] = useState<boolean | null>(null);
    const recorder = useRef<MediaRecorder | null>(null);
    const stream = useRef<MediaStream | null>(null);
    const chunks = useRef<Blob[]>([]);
    const pressedAt = useRef(0);
    const startedAt = useRef(0);
    const pressed = useRef(false);
    const micFailed = useRef(false);
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

    useEffect(() => {
        let cancelled = false;

        async function refresh() {
            const available = await microphoneAvailable();
            if (cancelled) {
                return;
            }

            setMicUnavailable(!available);
            setLocalMessage(available ? null : MIC_UNAVAILABLE);
        }

        void refresh();
        navigator.mediaDevices?.addEventListener('devicechange', refresh);

        return () => {
            cancelled = true;
            navigator.mediaDevices?.removeEventListener('devicechange', refresh);
        };
    }, []);

    function stopTracks() {
        stream.current?.getTracks().forEach((track) => track.stop());
        stream.current = null;
    }

    function markUnavailable() {
        stopTracks();
        setHolding(false);
        setMicUnavailable(true);
        setLocalMessage(MIC_UNAVAILABLE);
    }

    async function begin(event?: PointerEvent<HTMLButtonElement>) {
        if (!actingUser || busy || pressed.current || micUnavailable !== false) {
            return;
        }

        pressed.current = true;
        pressedAt.current = Date.now();
        micFailed.current = false;
        setHolding(true);
        setLocalMessage(null);
        try {
            event?.currentTarget.setPointerCapture(event.pointerId);
        } catch {
            // The press still records if the pointer cannot be captured.
        }

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
            micFailed.current = true;
            markUnavailable();
        }
    }

    function end() {
        if (!pressed.current) {
            return;
        }

        const mediaRecorder = recorder.current;
        pressed.current = false;
        if (mediaRecorder === null || mediaRecorder.state === 'inactive') {
            if (micFailed.current) {
                markUnavailable();
            } else {
                stopTracks();
                setHolding(false);
            }
            micFailed.current = false;
            return;
        }

        const recordedFor = Date.now() - startedAt.current;
        mediaRecorder.addEventListener('stop', () => {
            const blob = new Blob(chunks.current, { type: mediaRecorder.mimeType || 'audio/webm' });
            recorder.current = null;
            stopTracks();
            setHolding(false);
            if (recordedFor < MIN_HOLD_MS || blob.size === 0) {
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

        function onPointerUp() {
            if (pressed.current) {
                endRef.current();
            }
        }

        window.addEventListener('keydown', onKeyDown);
        window.addEventListener('keyup', onKeyUp);
        window.addEventListener('pointerup', onPointerUp);

        return () => {
            window.removeEventListener('keydown', onKeyDown);
            window.removeEventListener('keyup', onKeyUp);
            window.removeEventListener('pointerup', onPointerUp);
        };
    }, []);

    const statusLabel = voice?.status === 'queued' || voice?.status === 'running' ? 'Working on it.' : null;

    return (
        <div className="pointer-events-none fixed right-4 bottom-4 z-50 flex max-w-sm flex-col items-end gap-2">
            {(voice?.transcript || voice?.result || statusLabel) && (
                <div className="bg-card pointer-events-auto w-full rounded-lg border p-3 text-sm shadow" data-voice-status={voice?.status ?? 'idle'}>
                    {voice?.transcript && <p data-voice-transcript={voice.transcript}>{voice.transcript}</p>}
                    {statusLabel && <p role="status">{statusLabel}</p>}
                    {voice?.result && (voice.status === 'finished' || voice.status === 'failed') && <p data-voice-result={voice.result}>{voice.result}</p>}
                </div>
            )}
            <MicButton
                listening={holding}
                label={`Speak (${TALK_KEY})`}
                idleCaption=""
                errorCaption={localMessage ?? undefined}
                data-voice-button
                data-voice-key={TALK_KEY}
                data-voice-holding={holding ? 'true' : 'false'}
                disabled={!actingUser || busy || micUnavailable !== false}
                aria-keyshortcuts={TALK_KEY}
                onPointerDown={begin}
                onPointerUp={end}
                onContextMenu={(event) => event.preventDefault()}
            />
        </div>
    );
}
