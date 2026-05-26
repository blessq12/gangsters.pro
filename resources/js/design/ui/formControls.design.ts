/**
 * Брендовые checkbox / radio (без системных белых галочек).
 */

const controlBase =
    "relative flex shrink-0 items-center justify-center rounded-none border transition-colors appearance-none";

export const formControlsDesign = {
    inputHidden: "sr-only",
    checkbox: `${controlBase} h-4 w-4 border-app-border-on-surface bg-app-glass-fill`,
    checkboxSm: `${controlBase} h-3.5 w-3.5 border-app-border-on-surface bg-app-glass-fill`,
    checkboxChecked: "border-app-accent bg-app-accent",
    radio: `${controlBase} h-4 w-4 rounded-full border-neutral-400 bg-app-glass-fill`,
    radioChecked: "border-app-accent bg-app-accent",
    checkIcon: "mdi mdi-check text-sm text-black",
    radioDot: "h-1.5 w-1.5 rounded-full bg-black",
} as const;

export type FormControlsDesign = typeof formControlsDesign;
