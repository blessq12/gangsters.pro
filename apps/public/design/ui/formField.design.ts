/**
 * Обёртка поля: label, ошибка, invalid-состояние инпута.
 */

export const formFieldDesign = {
    root: "min-w-0 w-full space-y-1",
    label: "mb-1 block text-sm font-medium text-app-canvas-fg",
    errorSm: "text-xs text-red-400",
    errorXs: "text-xs text-red-400",
    groupInvalid: "rounded-none ring-1 ring-red-500/40",
    inputInvalid:
        "border-red-500/70 ring-1 ring-red-500/40 focus:border-red-500 focus:ring-red-500/50",
} as const;

export type FormFieldDesign = typeof formFieldDesign;
