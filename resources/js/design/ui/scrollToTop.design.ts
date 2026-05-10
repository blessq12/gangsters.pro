/** Кнопка «Наверх» ({@link ScrollToTopButton.vue}). Темный/светлый — ключи ниже (ветвление по themeStore в компоненте). */

export const scrollToTopDesign = {
    btnBase:
        "fixed z-[32] flex h-12 w-12 items-center justify-center rounded-none border backdrop-blur-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-app-accent/70 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent md:bottom-8 md:right-8 max-md:bottom-32 max-md:right-4",
    themeDark:
        "border-black/12 bg-[rgba(0,0,0,0.06)] text-app-accent hover:border-app-accent/40 hover:bg-[rgba(0,0,0,0.08)]",
    themeLight:
        "border-slate-200/90 bg-white/95 text-app-accent shadow-md shadow-slate-300/30 hover:border-app-accent/50 hover:bg-neutral-50/90",
    icon: "mdi mdi-chevron-up text-2xl leading-none",
} as const;
