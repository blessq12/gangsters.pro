/**
 * Подтверждение сворачивания дока во время оформления.
 * Типографика и кнопки — в духе {@link closedNoticeDesign} и checkout CTA.
 */

export const dockDismissConfirmDesign = {
    contentWrap: "text-center text-app-canvas-fg",
    title:
        "font-heading text-xl font-normal leading-snug text-app-accent sm:text-2xl",
    message: "mt-3 text-sm leading-relaxed text-app-muted",
    actions: "mt-8 flex flex-col gap-2",
    primaryBtn:
        "inline-flex w-full items-center justify-center rounded-none bg-app-accent px-5 py-2.5 text-sm font-semibold text-black shadow-[0_0_18px_rgba(198,36,36,0.75)] transition hover:bg-app-accent-hover",
    secondaryBtn:
        "inline-flex w-full items-center justify-center rounded-none border border-app-border-on-surface bg-black/5 px-5 py-2.5 text-sm font-medium text-app-canvas-fg transition hover:border-app-accent/40 hover:bg-black/8 hover:text-app-accent",
} as const;

export type DockDismissConfirmDesign = typeof dockDismissConfirmDesign;
