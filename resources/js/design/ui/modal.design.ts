/** Общее модальное окно приложения ({@link BaseModal.vue}). */

export const modalDesign = {
    root:
        "fixed inset-0 z-[9999] flex items-center justify-center",
    backdrop:
        "absolute inset-0 bg-[rgba(0,0,0,0.72)] backdrop-blur-sm",
    content: "relative z-[1] w-full",
    sizes: {
        md: {
            innerWrap: "relative z-10 mx-auto w-full max-w-lg px-4 sm:px-6 lg:px-8",
            card:
                "rounded-none border border-app-border-on-surface bg-app-canvas px-4 sm:px-6 lg:px-8 py-5 shadow-2xl shadow-black/70 backdrop-blur-xl",
            body: "space-y-4 text-sm leading-relaxed text-app-canvas-fg",
        },
        lg: {
            innerWrap:
                "relative z-10 mx-auto w-full max-w-3xl px-4 sm:max-w-4xl sm:px-6 lg:px-8",
            card:
                "flex max-h-[min(90vh,52rem)] flex-col rounded-none border border-app-border-on-surface bg-app-canvas px-4 sm:px-6 lg:px-8 py-5 shadow-2xl shadow-black/70 backdrop-blur-xl",
            body: "min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain pr-1 text-sm leading-relaxed text-app-canvas-fg",
        },
    },
    headerRow: "mb-4 flex shrink-0 items-start justify-between gap-4",
    headerSlot: "text-base font-semibold text-app-accent",
    closeBtn: "text-app-muted transition-colors hover:text-app-canvas-fg",
    footerWrap: "mt-4 shrink-0 border-t border-app-border-on-surface pt-3",
} as const;
