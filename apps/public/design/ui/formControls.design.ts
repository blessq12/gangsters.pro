/**
 * Брендовые checkbox (без системных белых галочек).
 * Поверхность и focus — как у публичных полей (client.shared.input).
 * Border unchecked: canvas-fg/30 — читаемость на тёмных островах (dock).
 * peer-checked на decor; маркер (i) — через [&_i] (не на вложенных peer).
 */

const controlSurface =
    "h-full w-full shrink-0 rounded-none border border-app-canvas-fg/30 bg-app-glass-fill transition-colors appearance-none";

const controlFocus =
    "peer-focus-visible:border-app-accent peer-focus-visible:ring-1 peer-focus-visible:ring-app-accent/60";

export const formControlsDesign = {
    /** Только для screen-reader; на кликабельных контролах не использовать. */
    inputHidden: "sr-only",
    rootMd: "relative inline-flex h-4 w-4 shrink-0 items-center justify-center",
    rootSm:
        "relative inline-flex h-3.5 w-3.5 shrink-0 items-center justify-center",
    inputOverlay:
        "peer absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0 disabled:cursor-not-allowed",
    controlDecorLayer:
        "pointer-events-none absolute inset-0 z-0 flex items-center justify-center",
    checkbox: `${controlSurface} ${controlFocus}`,
    checkboxSm: `${controlSurface} ${controlFocus}`,
    checkboxCheckedPeer:
        "peer-checked:border-app-accent peer-checked:bg-app-accent peer-checked:[&_i]:opacity-100",
    checkIcon:
        "mdi mdi-check text-sm leading-none text-white opacity-0 transition-opacity",
    checkIconSm:
        "mdi mdi-check text-[10px] leading-none text-white opacity-0 transition-opacity",
} as const;

export type FormControlsDesign = typeof formControlsDesign;
