/**
 * Карточки товара: десктоп, мобильная сетка, горизонтальная мобильная.
 */

import { shellColorRoles } from "../layout/shell.design";

const mediaFooterGradient =
    "pointer-events-none absolute inset-x-0 bottom-0 z-[2] h-[55%] bg-gradient-to-t from-black/92 via-black/50 to-transparent";
const mediaFooterStack =
    "absolute inset-x-0 bottom-0 z-10 flex flex-col gap-2 px-3 pb-3 pt-2 sm:inset-x-3 sm:pb-3";

const titleUnderPhotoBase = `font-heading font-normal leading-snug line-clamp-2 sm:line-clamp-3 ${shellColorRoles.canvasFg}`;
/** Единый заголовок mobile grid + horizontal list. */
const catalogProductTitleMobile = `${titleUnderPhotoBase} text-[13px] sm:text-sm`;

export const catalogCardsDesign = {
    commerce: {
        root: {
            desktop:
                "flex w-full items-center justify-between gap-2",
            desktopCompact:
                "gap-1.5",
            mobileGrid:
                "flex w-full shrink-0 items-center gap-3",
            horizontal:
                "flex min-w-0 flex-1 flex-nowrap items-center gap-2",
        },
        priceIdle: {
            desktop:
                "shrink-0 whitespace-nowrap text-sm font-semibold tabular-nums text-app-accent sm:text-base",
            mobileGrid:
                "ml-auto flex min-h-10 shrink-0 items-center whitespace-nowrap bg-app-accent/15 px-3 py-1.5 text-[12px] font-semibold tabular-nums text-app-accent",
            horizontal:
                "ml-auto flex h-9 shrink-0 items-center whitespace-nowrap bg-app-accent/15 px-2.5 py-1.5 text-[11px] font-semibold tabular-nums text-app-accent sm:text-xs",
        },
        priceInCart: {
            desktop:
                "shrink-0 whitespace-nowrap text-xs font-semibold tabular-nums text-app-muted sm:text-sm",
            mobileGrid:
                "ml-auto flex min-h-10 shrink-0 items-center whitespace-nowrap px-2 text-[12px] font-semibold tabular-nums text-app-muted",
            horizontal:
                "ml-auto flex h-9 shrink-0 items-center whitespace-nowrap px-2 text-[11px] font-semibold tabular-nums text-app-muted sm:text-xs",
        },
    },

    shared: {
        noPhotoText: "Нет фото",
        tagTone: {
            red: "border-red-400/50 bg-red-500/20 text-red-100",
            green: "border-green-400/50 bg-green-500/20 text-green-100",
            slate: "border-slate-400/50 bg-slate-500/20 text-app-canvas-fg",
            sky: "border-sky-400/50 bg-sky-500/20 text-sky-100",
            violet: "border-violet-400/50 bg-violet-500/20 text-violet-100",
            default:
                "border-app-accent/60 bg-app-accent/20 text-app-accent",
        },
        setBadge:
            "inline-flex w-fit items-center border border-violet-400/50 bg-violet-500/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-violet-100 backdrop-blur sm:text-[11px]",
        setCountPill:
            "inline-flex w-fit items-center bg-[rgba(0,0,0,0.75)] px-2 py-1 text-[10px] font-medium text-app-canvas-fg backdrop-blur sm:px-2.5 sm:text-[11px]",
    },

    desktop: {
        article:
            "relative z-[1] group flex h-full flex-col overflow-hidden bg-app-glass-fill shadow-[0_18px_45px_rgba(0,0,0,0.85)] transition duration-300 hover:-translate-y-1 hover:bg-app-accent-soft-bg",
        mediaWrap:
            "relative w-full overflow-hidden aspect-[4/3] sm:aspect-[5/4] lg:h-full lg:aspect-auto",
        img: "absolute inset-0 h-full w-full object-cover object-center transition-transform duration-500 ease-out group-hover:scale-105",
        placeholder:
            "absolute inset-0 flex items-center justify-center bg-neutral-950/75 text-xs text-app-muted",
        gradient:
            "pointer-events-none absolute inset-0 bg-gradient-to-t from-black/80 via-black/45 to-black/10",
        badgesCol:
            "absolute left-2.5 top-2.5 z-10 flex max-w-[70%] flex-col gap-1 sm:left-3 sm:top-3",
        weightPill:
            "inline-flex w-fit items-center bg-[rgba(0,0,0,0.75)] px-2 py-1 text-[10px] font-medium text-app-canvas-fg backdrop-blur sm:px-2.5 sm:text-[11px]",
        tagsRow: "flex flex-wrap gap-1",
        tagPill:
            "inline-flex items-center px-2 py-0.5 text-[10px] font-medium backdrop-blur",
        imageHit:
            "absolute inset-0 z-[1] cursor-pointer",
        cartAddIconBtn:
            "inline-flex h-9 w-9 shrink-0 items-center justify-center bg-app-accent text-black transition hover:scale-[1.03] sm:h-8 sm:w-8",
        cartAddIcon: "mdi mdi-cart-outline text-lg",
        mediaFooterGradient,
        mediaFooterStack,
        titleRow: "flex items-start gap-2",
        titleText: "min-w-0 flex-1",
        titleUnderPhoto: `${titleUnderPhotoBase} text-sm sm:text-base`,
        favBtn:
            "shrink-0 flex h-9 w-9 items-center justify-center border border-transparent bg-neutral-950/85 text-[15px] text-app-canvas-fg transition-colors hover:border-app-accent hover:text-app-accent sm:h-8 sm:w-8",
        favBtnActive: "border-app-accent text-app-accent",
        addBtn:
            "inline-flex min-h-10 flex-1 items-center justify-center bg-app-accent px-3 py-2 text-xs font-semibold text-black shadow-[0_0_12px_rgba(198,36,36,0.45)] transition-transform hover:scale-[1.02] sm:min-h-0 sm:py-1.5 sm:text-sm",
        qtyBar:
            "inline-flex min-h-10 flex-1 items-center justify-between border border-app-accent/60 bg-neutral-950/88 px-2 py-1 text-xs text-app-canvas-fg",
        qtyBtn:
            "flex h-8 w-8 items-center justify-center bg-neutral-950/88 text-[16px] sm:h-6 sm:w-6 sm:text-[14px]",
        qtyLabel:
            "px-1 text-xs sm:text-sm font-semibold",
    },

    mobileGrid: {
        article:
            "relative z-[1] group flex h-full flex-col overflow-hidden bg-app-glass-fill shadow-[0_18px_45px_rgba(0,0,0,0.85)] transition duration-300 hover:-translate-y-1 hover:bg-app-accent-soft-bg",
        srOnlyAria: "sr-only",
        mediaWrap:
            "relative w-full overflow-hidden aspect-[4/3] lg:h-full lg:aspect-auto",
        img: "absolute inset-0 h-full w-full object-cover object-center transition-transform duration-500 ease-out group-hover:scale-105",
        placeholder:
            "absolute inset-0 flex items-center justify-center bg-neutral-950/75 text-xs text-app-muted",
        gradient:
            "pointer-events-none absolute inset-0 bg-gradient-to-t from-black/80 via-black/45 to-black/10",
        imageHit:
            "absolute inset-0 z-[1] cursor-pointer",
        badgesCol:
            "absolute left-3 top-3 z-10 flex max-w-[70%] flex-col gap-1",
        weightPill:
            "inline-flex items-center bg-[rgba(0,0,0,0.75)] px-2.5 py-1 text-[10px] font-medium text-app-canvas-fg backdrop-blur",
        tagsRow: "flex flex-wrap gap-1",
        tagPill:
            "inline-flex items-center px-2 py-0.5 text-[10px] font-medium backdrop-blur",
        favFab:
            "absolute right-3 top-3 z-10 flex h-10 w-10 items-center justify-center border border-transparent bg-neutral-950/85 text-app-canvas-fg transition-[transform,box-shadow,border-color,color] duration-300 ease-out hover:border-app-accent/60 hover:text-app-accent",
        favFabActive: "border-app-accent/60 text-app-accent",
        favFabIcon:
            "mdi text-xl transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]",
        feedbackRing:
            "pc-feedback-ring pointer-events-none absolute inset-0 ring-2 ring-app-accent/45",
        mediaFooterGradient,
        mediaFooterStack,
        titleUnderPhoto: catalogProductTitleMobile,
        cartAddText:
            "relative inline-flex min-h-11 items-center justify-center gap-1.5 bg-app-accent px-3 py-2 text-xs font-semibold text-black transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] sm:text-[13px]",
        cartAddIcon:
            "mdi mdi-cart-outline text-lg transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]",
        feedbackCartRing:
            "pc-feedback-ring pc-feedback-ring--cart pointer-events-none absolute -inset-1 ring-2 ring-app-accent/55",
        qtyCluster:
            "flex h-10 items-center gap-0.5 border border-app-accent/50 bg-neutral-950/85 px-0.5 transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]",
        qtyMiniBtn:
            "flex h-9 w-9 shrink-0 items-center justify-center bg-neutral-950/85 text-base font-semibold leading-none text-app-canvas-fg transition-colors hover:bg-neutral-950/88",
        qtyNum:
            "min-w-[1.5rem] px-0.5 text-center text-[12px] font-semibold tabular-nums text-app-accent",
    },

    horizontalMobile: {
        article:
            "relative z-[1] group flex overflow-hidden bg-app-glass-fill shadow-[0_10px_32px_rgba(0,0,0,0.7)]",
        thumbCol:
            "relative w-28 shrink-0 cursor-pointer overflow-hidden sm:w-32",
        thumbImg:
            "h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105",
        thumbPlaceholder:
            "flex h-full w-full items-center justify-center bg-neutral-950/75 text-xs text-app-muted",
        thumbGradient:
            "pointer-events-none absolute inset-0 bg-gradient-to-r from-black/5 via-black/20 to-black/50",
        body: "flex min-w-0 flex-1 flex-col justify-center gap-1.5 p-2.5 sm:p-3",
        titleRow: "flex min-w-0 items-baseline gap-2",
        title: `min-w-0 flex-1 ${catalogProductTitleMobile}`,
        weightInline: "shrink-0 text-[11px] text-app-muted",
        setBadgeInline:
            "shrink-0 inline-flex w-fit items-center border border-violet-400/50 bg-violet-500/20 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-violet-100",
        primaryTagInline: "self-start",
        actionsBar:
            "flex w-full min-w-0 shrink-0 flex-nowrap items-center gap-2",
        favBtn:
            "flex h-9 w-9 shrink-0 items-center justify-center border border-transparent bg-neutral-950/85 text-app-canvas-fg transition hover:border-app-accent/60 hover:text-app-accent",
        favBtnActive: "border-app-accent/60 text-app-accent",
        favIcon: "mdi text-lg",
        cartAddIconBtn:
            "relative inline-flex h-9 w-9 items-center justify-center bg-app-accent text-black transition hover:scale-[1.03]",
        cartAddIcon: "mdi mdi-cart-outline text-lg",
        qtyCluster:
            "flex h-9 shrink-0 items-center gap-0.5 border border-app-accent/50 bg-neutral-950/85 px-0.5",
        qtyMiniBtn:
            "flex h-8 w-8 shrink-0 items-center justify-center text-sm font-semibold text-app-canvas-fg transition-colors hover:bg-neutral-950/88",
        qtyNum:
            "min-w-[1.25rem] px-0.5 text-center text-[11px] font-semibold tabular-nums text-app-accent",
    },
} as const;

export type CatalogCardsDesign = typeof catalogCardsDesign;
