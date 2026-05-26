/**
 * Классы навбара: общие правила и три варианта разметки (responsive / desktop / mobile).
 */

import { shellColorRoles, shellTypography } from "./shell.design";

export const navbarDesign = {
    shared: {
        header: "relative z-[1] pb-10 pt-6 sm:pb-12 sm:pt-7",
        linkTransition: "transition-colors duration-200",
        linkActive: "text-app-accent",
        linkInactive: "text-app-canvas-fg/80 hover:text-app-canvas-fg",
        logoLink:
            "absolute left-1/2 top-full z-[2] mb-8 mt-0 inline-flex -translate-x-1/2 -translate-y-[58%] items-center justify-center group sm:mb-10",
        mdiIcon: "mdi text-lg",
        burgerOpen: "border-app-accent/70 text-app-accent",
        burgerClosedHover: "hover:border-app-accent/50 hover:text-app-accent",
    },

    responsive: {
        inner: "mx-auto max-w-7xl px-4 sm:px-6 lg:px-8",
        bar: "flex h-[70px] min-h-[70px] items-center justify-between gap-4 overflow-visible rounded-none border border-app-accent/40 bg-app-canvas px-4 py-0 shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur sm:px-6 lg:px-8",
        leftZone:
            "flex min-w-0 items-center gap-2 sm:gap-3 w-24 sm:w-auto",
        balanceSpacer: "w-10 shrink-0 md:hidden",
        desktopNavGate: "hidden min-w-0 items-center md:flex",
        navLeft: `flex items-center gap-4 ${shellTypography.body.navRow}`,
        logoRow: `${shellTypography.heading.logoMark} relative z-[2] min-w-0 flex-1 self-stretch`,
        logoImg:
            "mx-auto h-[6.25rem] w-auto max-w-[10.5rem] object-contain drop-shadow-[0_0_15px_rgba(198,36,36,0.45)] transition-transform duration-200 group-hover:scale-105 group-hover:drop-shadow-[0_0_22px_rgba(198,36,36,0.7)] sm:h-[7rem] sm:max-w-[12rem] md:h-[7.75rem] md:max-w-[13.5rem]",
        rightZone:
            "flex items-center justify-end gap-2 sm:gap-3 w-24 sm:w-auto",
        burgerButton:
            "flex h-9 w-9 items-center justify-center rounded-none border border-black/20 bg-neutral-950/88 text-app-canvas-fg transition-colors md:hidden",
        navRight: `hidden md:flex items-center gap-4 justify-end ${shellTypography.body.navRow}`,
    },

    desktop: {
        inner: "mx-auto max-w-7xl px-6 lg:px-8",
        bar: "flex h-[70px] min-h-[70px] items-center justify-between gap-4 overflow-visible rounded-none border border-app-accent/40 bg-app-canvas px-4 py-0 shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur sm:px-6 lg:px-8",
        leftZone: "flex min-w-0 items-center",
        navLeft: `flex items-center gap-4 ${shellTypography.body.navRow}`,
        logoRow: `${shellTypography.heading.logoMark} relative z-[2] min-w-0 flex-1 self-stretch`,
        logoImg:
            "mx-auto h-[7rem] w-auto max-w-[12rem] object-contain drop-shadow-[0_0_15px_rgba(198,36,36,0.45)] transition-transform duration-200 group-hover:scale-105 group-hover:drop-shadow-[0_0_22px_rgba(198,36,36,0.7)] lg:h-[7.75rem] lg:max-w-[14rem]",
        rightZone: "flex items-center justify-end gap-4 w-48",
        navRight: `flex items-center gap-4 ${shellTypography.body.navRow}`,
        cartBtn:
            "relative flex h-9 w-9 items-center justify-center rounded-none border border-black/20 bg-neutral-950/88 text-app-canvas-fg transition-colors hover:border-app-accent/50 hover:text-app-accent",
        cartBtnIcon: "mdi mdi-cart-outline text-lg",
    },

    mobile: {
        inner: "mx-auto max-w-7xl px-4",
        bar: "flex h-[70px] min-h-[70px] items-center justify-between gap-4 overflow-visible rounded-none border border-app-accent/40 bg-app-canvas px-4 py-0 shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur",
        scheduleZone:
            "flex shrink-0 items-center justify-start pr-1",
        logoRow: `${shellTypography.heading.logoMark} relative z-[2] min-w-0 flex-1 self-stretch`,
        logoPulseWrap:
            "inline-flex origin-center will-change-transform",
        logoImg:
            "mx-auto h-[6.25rem] w-auto max-w-[10rem] object-contain drop-shadow-[0_0_12px_rgba(198,36,36,0.4)] transition-transform duration-200 group-hover:scale-105 sm:h-[7rem] sm:max-w-[12rem]",
        burgerZone: "flex items-center justify-end gap-2",
        cartBtn:
            "relative flex h-9 w-9 items-center justify-center rounded-none border border-black/20 bg-neutral-950/88 text-app-canvas-fg transition-colors hover:border-app-accent/50 hover:text-app-accent",
        cartBtnIcon: "mdi mdi-cart-outline text-lg",
        burgerButton:
            "flex h-9 w-9 items-center justify-center rounded-none border border-black/20 bg-neutral-950/88 text-app-canvas-fg transition-colors",
    },

    mobileMenu: {
        overlayRoot:
            "pointer-events-none fixed inset-x-0 top-0 z-30 md:hidden pt-28 sm:pt-32",
        innerContainer:
            "pointer-events-auto mx-auto mt-3 max-w-7xl px-4 sm:px-6 lg:px-8",
        sheetNav:
            "overflow-hidden rounded-none border border-app-border-on-surface bg-app-canvas text-sm font-medium text-app-canvas-fg shadow-[0_20px_50px_rgba(0,0,0,0.75)]",
        companySection: "border-b border-app-border-on-surface px-4 py-3",
        companyTitle: `${shellTypography.heading.mobileMenuCompany} ${shellColorRoles.canvasFg}`,
        companyTagline: `${shellTypography.body.mobileMenuTagline} ${shellColorRoles.muted}`,
        companySchedule: `mt-2.5 ${shellTypography.body.mobileMenuMeta} ${shellColorRoles.muted}`,
        companyAddress: `mt-1.5 ${shellTypography.body.mobileMenuMeta} ${shellColorRoles.muted}`,
        phoneLink: `${shellTypography.body.mobileMenuPhone} ${shellColorRoles.accent95} hover:text-app-accent`,
        linksRegion: "space-y-0.5 px-2 py-2",
        routerLinkItem:
            "block rounded-none px-3 py-2.5 text-app-canvas-fg hover:bg-black/5",
    },
} as const;

export type NavbarDesign = typeof navbarDesign;
