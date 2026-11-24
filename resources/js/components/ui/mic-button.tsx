"use client"

import { useState, type ButtonHTMLAttributes } from "react"

const pills = [
  { peak: 0.42, delay: "-0.18s", duration: "0.72s" },
  { peak: 0.82, delay: "-0.46s", duration: "0.9s" },
  { peak: 1, delay: "0s", duration: "0.64s" },
  { peak: 0.66, delay: "-0.3s", duration: "0.82s" },
  { peak: 0.48, delay: "-0.58s", duration: "0.74s" },
]

export function MicButton({
  defaultListening = false,
  listening: listeningProp,
  label = "Speak",
  idleCaption = "Off",
  listeningCaption = "Listening",
  errorCaption,
  onClick,
  ...props
}: {
  defaultListening?: boolean
  listening?: boolean
  label?: string
  idleCaption?: string
  listeningCaption?: string
  errorCaption?: string
} & Omit<ButtonHTMLAttributes<HTMLButtonElement>, "type">) {
  const [uncontrolled, setUncontrolled] = useState(defaultListening)
  const listening = listeningProp ?? uncontrolled
  const showingError = !listening && Boolean(errorCaption)

  return (
    <div className="pointer-events-none flex flex-col items-center">
      <div
        className="mic-try pointer-events-none relative grid size-44 place-items-center"
        data-listening={listening ? "" : undefined}
      >
      <span className="mic-try-wave" aria-hidden />
      <span className="mic-try-wave mic-try-wave-late" aria-hidden />
      <button
        type="button"
        {...props}
        aria-pressed={listening}
        aria-label={props["aria-label"] ?? (listening ? "Stop listening" : "Start listening")}
        onClick={(event) => {
          if (listeningProp === undefined) {
            setUncontrolled((on) => !on)
          }
          onClick?.(event)
        }}
        className="pointer-events-auto relative z-1 grid size-28 place-items-center overflow-hidden rounded-full bg-primary text-primary-foreground shadow-[0_14px_32px_-16px_var(--primary)] transition-transform outline-none focus-visible:ring-3 focus-visible:ring-ring/40 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none disabled:active:scale-100"
      >
        <span
          className={`font-heading text-base tracking-wide whitespace-nowrap transition-opacity duration-200 ${listening ? "opacity-0" : "opacity-100"}`}
        >
          {label}
        </span>
        <span
          className={`absolute flex h-9 items-center gap-[5px] transition-opacity duration-200 ${listening ? "opacity-100" : "opacity-0"}`}
          aria-hidden
        >
          {pills.map((pill) => (
            <span
              key={pill.delay}
              className="mic-try-pill"
              style={{
                ["--peak" as string]: pill.peak,
                animationDelay: pill.delay,
                animationDuration: pill.duration,
              }}
            />
          ))}
        </span>
      </button>
        <style>{micStyles}</style>
      </div>
      <span
        className={`-mt-1 flex h-8 w-44 items-start justify-center text-center text-xs leading-4 ${showingError ? "text-destructive" : "text-muted-foreground"}`}
        role={showingError ? "status" : undefined}
        data-voice-message={showingError ? errorCaption : undefined}
      >
        {showingError ? errorCaption : listening ? listeningCaption : idleCaption}
      </span>
    </div>
  )
}

const micStyles = `
.mic-try-wave {
  position: absolute;
  width: 7rem;
  height: 7rem;
  border-radius: 9999px;
  border: 1.5px solid color-mix(in oklch, var(--primary) 75%, white);
  background: color-mix(in oklch, var(--primary) 16%, transparent);
  opacity: 0;
  pointer-events: none;
}
.mic-try[data-listening] .mic-try-wave {
  animation: mic-try-wave 1.8s ease-out infinite;
}
.mic-try[data-listening] .mic-try-wave-late {
  animation-delay: 0.9s;
}
@keyframes mic-try-wave {
  0% { transform: scale(1); opacity: 0.9; }
  100% { transform: scale(1.55); opacity: 0; }
}
.mic-try-pill {
  width: 5px;
  height: 10px;
  flex: none;
  border-radius: 999px;
  background: currentColor;
}
.mic-try[data-listening] .mic-try-pill {
  animation-name: mic-try-pill;
  animation-timing-function: ease-in-out;
  animation-iteration-count: infinite;
}
@keyframes mic-try-pill {
  0%, 100% { height: 10px; }
  50% { height: calc(var(--peak) * 36px); }
}
@media (prefers-reduced-motion: reduce) {
  .mic-try[data-listening] .mic-try-wave {
    animation: none;
    opacity: 0.22;
    transform: scale(1.28);
  }
  .mic-try[data-listening] .mic-try-pill {
    animation: none;
    height: calc(var(--peak) * 36px);
  }
}
`
