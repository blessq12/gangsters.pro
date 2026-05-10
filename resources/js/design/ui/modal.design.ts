/** Общее модальное окно приложения ({@link BaseModal.vue}). */

export const modalDesign = {
    root:
        "fixed inset-0 z-[9999] flex items-center justify-center",
    backdrop:
        "absolute inset-0 bg-black/40",
    content: "relative z-[1]",
    innerWrap: "relative z-10 mx-auto w-full max-w-lg px-4 sm:px-6 lg:px-8",
    card:
        "rounded-none border border-white/10 bg-[rgba(255,255,255,0.04)] px-4 sm:px-6 lg:px-8 py-5 shadow-2xl shadow-black/60 backdrop-blur-lg",
    headerRow: "mb-4 flex items-start justify-between gap-4",
    headerSlot: "text-base font-semibold text-amber-300",
    closeBtn: "text-slate-400 transition-colors hover:text-white",
    body: "space-y-4 text-sm leading-relaxed text-slate-100",
    footerWrap: "mt-4 border-t border-white/10 pt-3",
} as const;
