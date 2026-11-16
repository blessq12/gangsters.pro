/** Кнопка «Наверх» ({@link ScrollToTopButton.vue}). Dark-only стиль приложения. */

export const scrollToTopDesign = {
    btnBase:
        "fixed z-20 flex h-12 w-12 items-center justify-center rounded-none border backdrop-blur-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-app-accent/70 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent bottom-28 right-4 md:right-8",
    theme:
        "border-black/12 bg-[rgba(0,0,0,0.06)] text-app-accent hover:border-app-accent/40 hover:bg-[rgba(0,0,0,0.08)]",
    icon: "mdi mdi-chevron-up text-2xl leading-none",
} as const;
