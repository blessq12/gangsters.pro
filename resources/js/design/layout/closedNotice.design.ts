/**
 * Модалка «сейчас не принимаем заказы» — типографика как у {@link NotFoundPage}.
 */

export const closedNoticeDesign = {
    contentWrap:
        "text-center text-app-canvas-fg",
    kicker: "font-heading text-5xl font-normal text-app-accent sm:text-6xl",
    title: "mt-3 text-xl font-normal text-app-canvas-fg sm:text-2xl",
    lead: "mt-3 text-sm text-app-muted",
    todayBlock: "mt-6",
    todayLabel:
        "text-[11px] font-medium uppercase tracking-[0.22em] text-app-muted",
    todayLine:
        "mt-2 font-heading text-lg font-normal text-app-accent sm:text-xl",
    actions: "mt-8",
    confirmBtn:
        "inline-flex w-full items-center justify-center rounded-none bg-app-accent px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-app-accent-hover",
} as const;
