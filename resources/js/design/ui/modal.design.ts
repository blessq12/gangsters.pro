/** Общее модальное окно приложения ({@link BaseModal.vue}). */

export const modalDesign = {
    root:
        "fixed inset-0 z-[9999] flex items-center justify-center",
    backdrop:
        "absolute inset-0 bg-[rgba(0,0,0,0.72)] backdrop-blur-sm",
    content: "relative z-[1]",
    innerWrap: "relative z-10 mx-auto w-full max-w-lg px-4 sm:px-6 lg:px-8",
    card:
        "rounded-none border border-app-border-on-surface bg-[rgba(0,0,0,0.9)] px-4 sm:px-6 lg:px-8 py-5 shadow-2xl shadow-black/70 backdrop-blur-xl",
    headerRow: "mb-4 flex items-start justify-between gap-4",
    headerSlot: "text-base font-semibold text-app-accent",
    closeBtn: "text-app-muted transition-colors hover:text-app-canvas-fg",
    body: "space-y-4 text-sm leading-relaxed text-app-canvas-fg",
    footerWrap: "mt-4 border-t border-app-border-on-surface pt-3",
} as const;
