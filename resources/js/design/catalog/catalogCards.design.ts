/**
 * Карточки товара: десктоп, мобильная сетка, горизонтальная мобильная.
 */

import { shellColorRoles, shellTypography } from "../layout/shell.design";

const mediaFooterGradient =
    "pointer-events-none absolute inset-x-0 bottom-0 z-[2] h-[55%] bg-gradient-to-t from-black/92 via-black/50 to-transparent";
const mediaFooterStack =
    "absolute inset-x-0 bottom-0 z-10 flex flex-col gap-2 px-3 pb-3 pt-2 sm:inset-x-3 sm:pb-3";

const titleUnderPhotoBase = `font-heading font-normal leading-snug line-clamp-2 sm:line-clamp-3 ${shellColorRoles.canvasFg}`;

export const catalogCardsDesign = {
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
        topRightCluster:
            "absolute right-2.5 top-2.5 z-10 flex items-center gap-1.5 sm:right-3 sm:top-3",
        nutritionBtn:
            "flex h-7 w-7 shrink-0 items-center justify-center border border-transparent bg-neutral-950/85 text-app-canvas-fg backdrop-blur transition-colors hover:border-app-accent/60 hover:text-app-accent sm:h-6 sm:w-6",
        nutritionBtnIcon: "mdi mdi-information-outline text-sm sm:text-xs",
        nutritionTooltip:
            "absolute right-0 top-full z-10 mt-1.5 min-w-[180px] bg-[rgba(0,0,0,0.94)] px-3 py-2.5 shadow-xl backdrop-blur sm:min-w-[200px]",
        nutritionTooltipInner: "space-y-1.5 text-[11px] text-app-canvas-fg sm:text-xs",
        nutritionRow: "flex justify-between gap-4",
        nutritionLabel: "text-app-muted",
        nutritionVal: "font-medium",
        nutritionFooter:
            "mt-1.5 pt-1.5 text-[10px] text-app-muted sm:text-[11px]",
        priceBadge:
            "inline-flex items-center bg-app-accent px-2.5 py-1 text-[11px] font-semibold text-black shadow-[0_0_20px_rgba(198,36,36,0.7)] sm:px-3 sm:py-1.5 sm:text-xs transition-transform duration-200 hover:scale-[1.03] cursor-pointer",
        mediaFooterGradient,
        mediaFooterStack,
        titleRow: "flex items-start gap-2",
        titleText: "min-w-0 flex-1",
        titleUnderPhoto: `${titleUnderPhotoBase} text-sm sm:text-base`,
        favBtn:
            "shrink-0 flex h-9 w-9 items-center justify-center border border-transparent bg-neutral-950/85 text-[15px] text-app-canvas-fg transition-colors hover:border-app-accent hover:text-app-accent sm:h-8 sm:w-8",
        favBtnActive: "border-app-accent text-app-accent",
        cartRow: "flex items-center justify-between gap-2",
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
        titleUnderPhoto: `${titleUnderPhotoBase} text-[13px] sm:text-sm`,
        actionsUnderPhoto: "flex w-full shrink-0 items-center gap-3",
        actionCluster:
            "relative flex w-fit min-w-0 items-center gap-2.5 bg-[rgba(0,0,0,0.78)] px-3 py-1.5 shadow-[0_0_24px_rgba(0,0,0,0.7)] backdrop-blur-xl",
        nutritionIconBtn:
            "flex h-10 w-10 items-center justify-center border border-app-accent/40 bg-neutral-950/85 text-app-accent transition-transform duration-300 ease-out hover:border-app-accent/70 hover:text-app-accent",
        ingredientsIconBtn:
            "flex h-10 w-10 items-center justify-center border border-transparent bg-neutral-950/85 text-app-canvas-fg transition-transform duration-300 ease-out hover:border-app-accent/50 hover:text-app-accent",
        cartIconOuter: "flex h-10 shrink-0 items-center",
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
        priceSide:
            "ml-auto flex min-h-10 shrink-0 items-center whitespace-nowrap bg-app-accent px-3 py-1.5 text-[12px] font-semibold text-black transition-transform duration-200 hover:scale-[1.03] cursor-pointer",
        teleportTooltipBase:
            "fixed z-[1300] bg-[rgba(0,0,0,0.95)] px-2.5 py-2.5 shadow-xl backdrop-blur",
        teleportTooltipNutritionWidth: "w-[190px]",
        teleportTooltipIngredientsWidth:
            "w-[210px] max-h-44 overflow-y-auto",
        teleportNutritionInner: "space-y-1 text-[11px] text-app-canvas-fg",
        teleportNutritionRow:
            "flex items-center justify-between gap-2",
        teleportNutritionLabel: "text-app-muted",
        teleportNutritionVal: "font-medium",
        teleportIngredientsInner: "space-y-1 text-[11px] text-app-canvas-fg",
        teleportIngredientsHeading:
            "text-[10px] font-medium text-app-muted",
        teleportIngredientsBody: "text-app-canvas-fg/90",
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
        body: "flex min-w-0 flex-1 justify-between gap-3 p-3",
        textCol: "min-w-0 flex-1",
        title:
            `line-clamp-2 ${shellTypography.scale.heading.section} font-normal leading-snug ${shellColorRoles.surfaceFg}`,
        weightMuted: "mt-1 text-[11px] text-app-muted",
        actionsCluster:
            "relative mt-3 flex flex-wrap items-center gap-2",
        favBtn:
            "flex h-9 w-9 items-center justify-center border border-transparent bg-app-surface text-app-surface-fg transition hover:border-app-accent/60 hover:text-app-accent",
        favBtnActive: "border-app-accent/60 text-app-accent",
        favIcon: "mdi text-lg",
        nutritionBtn:
            "flex h-9 w-9 items-center justify-center border border-app-accent/40 bg-app-surface text-app-accent transition hover:border-app-accent/70",
        nutritionIcon: "mdi mdi-fire-circle text-lg",
        ingredientsBtn:
            "flex h-9 w-9 items-center justify-center border border-transparent bg-app-surface text-app-surface-fg transition hover:border-app-accent/60 hover:text-app-accent",
        ingredientsIcon: "mdi mdi-information-outline text-lg",
        cartAddText:
            "inline-flex min-h-11 items-center justify-center gap-1.5 bg-app-accent px-3 py-2 text-xs font-semibold text-black transition hover:scale-[1.02]",
        cartAddIcon: "mdi mdi-cart-outline text-base",
        qtyCluster:
            "flex h-9 items-center border border-app-accent/50 bg-neutral-950/85 px-1",
        qtyMiniBtn:
            "flex h-7 w-7 items-center justify-center text-sm font-semibold text-app-canvas-fg transition hover:bg-neutral-950/85",
        qtyNum:
            "min-w-[1.25rem] px-1 text-center text-xs font-semibold text-app-accent",
        rightCol:
            "flex shrink-0 flex-col items-end justify-between gap-3",
        priceBtn:
            "bg-app-accent px-2.5 py-1.5 text-xs font-semibold text-black transition hover:scale-[1.03]",
        weightEcho: "text-[11px] text-app-muted",
        teleportTooltipBase:
            "fixed z-[1300] bg-[rgba(0,0,0,0.95)] px-2.5 py-2 shadow-xl backdrop-blur",
        teleportTooltipNutritionWidth: "w-[180px]",
        teleportTooltipIngredientsWidth:
            "w-[210px] max-h-44 overflow-y-auto",
        teleportNutritionInner: "space-y-1 text-[11px] text-app-canvas-fg",
        teleportNutritionRow:
            "flex items-center justify-between gap-2",
        teleportNutritionLabel: "text-app-muted",
        teleportNutritionVal: "font-medium",
        teleportIngredientsInner: "space-y-1 text-[11px] text-app-canvas-fg",
        teleportIngredientsHeading:
            "text-[10px] font-medium text-app-muted",
        teleportIngredientsBody: "text-app-canvas-fg/90",
    },
} as const;

export type CatalogCardsDesign = typeof catalogCardsDesign;
