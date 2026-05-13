/**
 * Оболочка приложения: корневой flex, интро, контейнер main.
 * Типографика: @font-face в resources/css/fonts.css; шкала и цветовые роли — `shellTypography` / `shellColorRoles`.
 * Базовая шкала — `shellTypography.scale`; доменные алиасы — `heading` / `body` (композиция без цвета).
 * Размеры для `h1–h6` без классов — см. `@layer base` в resources/css/style.css (Snowstorm 400); утилита `.font-heading` — для заголовочного текста не на `h*`.
 */

/** Должны совпадать с `font-family` в resources/css/fonts.css (`@font-face`). */
export const appFontFamilies = {
    body: "Onest",
    /** Snowstorm: один файл 400 в fonts.css; вес заголовков — `font-normal`. */
    heading: "Snowstorm",
} as const;

/**
 * Ступенчатая шкала (только размер/weight/leading/tracking), без семантики цвета.
 */
const shellTypographyScale = {
    heading: {
        display:
            "text-5xl font-normal leading-none tracking-tight sm:text-6xl lg:text-7xl",
        h1: "text-4xl font-normal leading-tight sm:text-5xl lg:text-6xl",
        h2: "text-3xl font-normal leading-tight sm:text-4xl lg:text-5xl",
        h3: "text-2xl font-normal leading-snug sm:text-3xl lg:text-4xl",
        section: "text-xl font-normal leading-snug sm:text-2xl",
    },
    body: {
        lead: "text-base leading-relaxed sm:text-lg",
        default: "text-sm leading-relaxed sm:text-base",
        small: "text-xs leading-snug",
        caption: "text-[11px] leading-snug",
        overline: "text-[11px] uppercase tracking-[0.18em]",
        overlineTight: "text-[11px] font-semibold uppercase tracking-[0.14em]",
        overlineWide: "text-[11px] uppercase tracking-[0.22em]",
        overlineEyebrow: "text-[11px] uppercase tracking-[0.28em]",
        navEmphasis: "text-sm font-medium tracking-wide",
    },
} as const;

/**
 * Публичная типографика: алиасы на шкалу + layout-утилиты.
 * Цвет подключается через `shellColorRoles` в местах сборки строк.
 */
export const shellTypography = {
    scale: shellTypographyScale,

    heading: {
        /** Hero `<h1>` вторичных страниц — размер из `scale.heading.h2` + контейнер. */
        secondaryPageTitle: `${shellTypographyScale.heading.h2} mb-3 max-w-3xl`,
        statValue: "mt-1 text-base font-semibold sm:text-lg",
        logoMark: shellTypographyScale.heading.section,
        mobileMenuCompany: "text-[13px] font-semibold leading-snug",
    },

    body: {
        secondaryDescription: `${shellTypographyScale.body.default} max-w-2xl`,
        navRow: shellTypographyScale.body.navEmphasis,
        breadcrumbsRow: "mb-4 flex flex-wrap items-center gap-1 text-xs",
        statLabel: shellTypographyScale.body.overlineWide,
        eyebrow: `mb-3 inline-flex rounded-none border border-app-accent/30 bg-[rgba(0,0,0,0.04)] px-3 py-1 ${shellTypographyScale.body.overlineEyebrow} backdrop-blur`,
        mobileMenuTagline: "mt-0.5 line-clamp-2 text-xs leading-snug",
        mobileMenuMeta: `${shellTypographyScale.body.caption} leading-snug`,
        mobileMenuPhone: "mt-2.5 inline-flex text-xs font-medium",
        dockTabRow:
            "group flex flex-col items-center gap-1.5 text-xs sm:text-xs transition-colors",
        dockTabLabelDesktop: "hidden lg:block text-[11px]",
        checkoutFlowBody: "space-y-3 text-xs sm:text-sm",
        checkoutHeadingSm: "text-xs font-semibold",
        checkoutKicker: shellTypographyScale.body.overline,
        checkoutKickerAccent: shellTypographyScale.body.overline,
        checkoutSubsectionKicker: shellTypographyScale.body.overlineTight,
    },
} as const;

/**
 * Алиасы Tailwind для семантики `app-*` (hex только на `.app-shell` в MainLayout*).
 * Перебитие в доменных `*.design.ts` допускается точечно (ошибки `red-*`, контраст на кнопках `text-black`).
 */
export const shellColorRoles = {
    canvasFg: "text-app-canvas-fg",
    canvasFgSoft: "text-app-canvas-fg/90",
    canvasFg80: "text-app-canvas-fg/80",
    muted: "text-app-muted",
    accent: "text-app-accent",
    accent95: "text-app-accent/95",
    surfaceFg: "text-app-surface-fg",
} as const;

export const layoutShellDesign = {
    typography: shellTypography,
    colorRoles: shellColorRoles,

    shared: {
        root: "app-shell min-h-screen flex flex-col",
        /** Корень SPA: основной текст через Onest (`--font-sans` в style.css @theme). */
        typographyRoot: "font-sans antialiased",
        themeDark: "theme-dark text-app-canvas-fg",
        themeLight: "theme-light text-app-canvas-fg",
        introOverlay:
            "pointer-events-none fixed inset-0 z-40 flex items-center justify-center",
        mainGrow: "flex-1",
    },

    core: {
        introLogo: "h-40 w-auto md:h-48",
        mainContainer:
            "mx-auto max-w-7xl px-4 pb-5 pt-0 opacity-0 sm:px-6 lg:px-8",
    },

    desktop: {
        introLogo: "h-44 w-auto md:h-52",
        mainContainer:
            "mx-auto max-w-7xl px-6 pb-7 pt-0 opacity-0 lg:px-8",
    },

    mobile: {
        introLogo: "h-40 w-auto md:h-48",
        mainContainer: "mx-auto max-w-7xl px-4 pb-3 pt-0 opacity-0 sm:px-6",
    },

    /** SecondaryPageLayout: крупный hero вторичных страниц */
    secondaryPage: {
        section: "relative mt-12 mb-12",
        outerGlowWrap:
            "pointer-events-none absolute inset-0 opacity-40 mix-blend-screen",
        outerGlowTL:
            "absolute -top-24 -left-10 h-56 w-56 rounded-full bg-app-accent/15 blur-3xl",
        outerGlowBR:
            "absolute -bottom-24 right-0 h-64 w-64 rounded-full bg-app-accent/12 blur-3xl",
        heroCard:
            "relative overflow-hidden rounded-none border border-black/12 bg-neutral-950/72 shadow-[0_20px_80px_rgba(0,0,0,0.55)]",
        heroImageLayer: "absolute inset-0",
        heroImage: "h-full w-full object-cover opacity-55",
        heroScrim:
            "absolute inset-0 bg-[linear-gradient(135deg,rgba(0,0,0,0.92)_0%,rgba(0,0,0,0.66)_45%,rgba(0,0,0,0.4)_100%)]",
        innerAmbient: "absolute inset-0",
        innerGlowA:
            "absolute -left-20 top-10 h-52 w-52 rounded-full bg-app-accent/10 blur-3xl",
        innerGlowB:
            "absolute top-0 right-0 h-60 w-60 rounded-full bg-app-accent/12 blur-3xl",
        contentPad:
            "relative px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12",
        sticksWrap: "rotate-15",
        stickLeft:
            "pointer-events-none absolute -bottom-2 right-6 h-3 w-auto",
        stickRight:
            "pointer-events-none absolute bottom-1 right-3 h-3 w-auto",
        textCol: "min-w-0",
        breadcrumbsNav: `${shellTypography.body.breadcrumbsRow} ${shellColorRoles.muted}`,
        breadcrumbHomeLink:
            "transition-colors hover:text-app-accent",
        breadcrumbText: shellColorRoles.muted,
        breadcrumbSep: "opacity-60",
        eyebrow: `${shellTypography.body.eyebrow} ${shellColorRoles.accent}`,
        title: `${shellTypography.heading.secondaryPageTitle} ${shellColorRoles.accent}`,
        description: `${shellTypography.body.secondaryDescription} ${shellColorRoles.canvasFgSoft}`,
        statsGrid: "mt-6 grid gap-3 sm:grid-cols-3",
        statCard:
            "rounded-none border border-black/12 bg-[rgba(0,0,0,0.04)] px-4 py-3 backdrop-blur",
        statLabel: `${shellTypography.body.statLabel} ${shellColorRoles.muted}`,
        statValue: `${shellTypography.heading.statValue} ${shellColorRoles.canvasFg}`,
        slotWrap: "space-y-8 sm:space-y-10",
    },
} as const;
