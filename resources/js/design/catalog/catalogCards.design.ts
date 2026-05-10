/**
 * Карточки товара: десктоп, мобильная сетка, горизонтальная мобильная.
 */

export const catalogCardsDesign = {
    shared: {
        noPhotoText: "Нет фото",
        tagTone: {
            red: "border-red-400/50 bg-red-500/20 text-red-100",
            green: "border-green-400/50 bg-green-500/20 text-green-100",
            slate: "border-slate-400/50 bg-slate-500/20 text-slate-100",
            sky: "border-sky-400/50 bg-sky-500/20 text-sky-100",
            violet: "border-violet-400/50 bg-violet-500/20 text-violet-100",
            default:
                "border-amber-400/60 bg-amber-500/20 text-amber-100",
        },
    },

    desktop: {
        article:
            "group flex h-full flex-col overflow-hidden bg-[rgba(255,255,255,0.02)] shadow-[0_18px_45px_rgba(0,0,0,0.85)] transition duration-300 hover:-translate-y-1 hover:bg-[rgba(255,255,255,0.03)]",
        mediaWrap:
            "relative w-full overflow-hidden aspect-[4/3] sm:aspect-[5/4] lg:h-full lg:aspect-auto",
        img: "absolute inset-0 h-full w-full object-cover object-center transition-transform duration-500 ease-out group-hover:scale-105",
        placeholder:
            "absolute inset-0 flex items-center justify-center bg-slate-900/70 text-xs text-slate-400",
        gradient:
            "pointer-events-none absolute inset-0 bg-gradient-to-t from-black/80 via-black/45 to-black/10",
        badgesCol:
            "absolute left-2.5 top-2.5 z-10 flex max-w-[70%] flex-col gap-1 sm:left-3 sm:top-3",
        weightPill:
            "inline-flex w-fit items-center border border-white/10 bg-[rgba(0,0,0,0.75)] px-2 py-1 text-[10px] font-medium text-slate-100 backdrop-blur sm:px-2.5 sm:text-[11px]",
        tagsRow: "flex flex-wrap gap-1",
        tagPill:
            "inline-flex items-center border px-2 py-0.5 text-[10px] font-medium backdrop-blur",
        imageHit:
            "absolute inset-0 z-[1] cursor-pointer",
        topRightCluster:
            "absolute right-2.5 top-2.5 z-10 flex items-center gap-1.5 sm:right-3 sm:top-3",
        nutritionBtn:
            "flex h-7 w-7 shrink-0 items-center justify-center border border-white/20 bg-black/60 text-slate-200 backdrop-blur transition-colors hover:border-amber-400/60 hover:text-amber-200 sm:h-6 sm:w-6",
        nutritionBtnIcon: "mdi mdi-information-outline text-sm sm:text-xs",
        nutritionTooltip:
            "absolute right-0 top-full z-10 mt-1.5 min-w-[180px] border border-white/10 bg-[rgba(0,0,0,0.94)] px-3 py-2.5 shadow-xl backdrop-blur sm:min-w-[200px]",
        nutritionTooltipInner: "space-y-1.5 text-[11px] text-slate-100 sm:text-xs",
        nutritionRow: "flex justify-between gap-4",
        nutritionLabel: "text-slate-300",
        nutritionVal: "font-medium",
        nutritionFooter:
            "mt-1.5 border-t border-white/10 pt-1.5 text-[10px] text-slate-400 sm:text-[11px]",
        priceBadge:
            "inline-flex items-center bg-amber-400 px-2.5 py-1 text-[11px] font-semibold text-black shadow-[0_0_20px_rgba(251,191,36,0.7)] sm:px-3 sm:py-1.5 sm:text-xs transition-transform duration-200 hover:scale-[1.03] cursor-pointer",
        footerIsland:
            "absolute inset-x-2.5 bottom-2.5 z-10 border border-amber-400/30 bg-[rgba(255,255,255,0.04)] px-3 py-2.5 backdrop-blur shadow-[0_0_20px_rgba(0,0,0,0.9)] sm:inset-x-3 sm:bottom-3 sm:px-3.5",
        titleRow: "flex items-start gap-2",
        titleText:
            "min-w-0 flex-1 space-y-1",
        titleHeading:
            "text-sm font-semibold leading-snug text-slate-50 line-clamp-2 sm:text-base sm:line-clamp-3",
        favBtn:
            "shrink-0 flex h-9 w-9 items-center justify-center border border-white/30 bg-black/60 text-[15px] text-slate-200 transition-colors hover:border-amber-400 hover:text-amber-200 sm:h-7 sm:w-7 sm:text-[13px]",
        favBtnActive: "border-amber-400 text-amber-300",
        cartRow: "mt-2 flex items-center justify-between gap-2",
        addBtn:
            "inline-flex min-h-10 flex-1 items-center justify-center bg-amber-400 px-3 py-2 text-xs font-semibold text-black shadow-[0_0_12px_rgba(251,191,36,0.45)] transition-transform hover:scale-[1.02] sm:min-h-0 sm:py-1.5 sm:text-sm",
        qtyBar:
            "inline-flex min-h-10 flex-1 items-center justify-between border border-amber-400/60 bg-black/70 px-2 py-1 text-xs text-slate-50",
        qtyBtn:
            "flex h-8 w-8 items-center justify-center bg-black/70 text-[16px] sm:h-6 sm:w-6 sm:text-[14px]",
        qtyLabel:
            "px-1 text-xs sm:text-sm font-semibold",
    },

    mobileGrid: {
        article:
            "group flex h-full flex-col overflow-hidden bg-[rgba(255,255,255,0.02)] shadow-[0_18px_45px_rgba(0,0,0,0.85)] transition duration-300 hover:-translate-y-1 hover:bg-[rgba(255,255,255,0.03)]",
        srOnlyAria: "sr-only",
        mediaWrap:
            "relative w-full overflow-hidden aspect-[4/3] lg:h-full lg:aspect-auto",
        img: "absolute inset-0 h-full w-full object-cover object-center transition-transform duration-500 ease-out group-hover:scale-105",
        placeholder:
            "absolute inset-0 flex items-center justify-center bg-slate-900/70 text-xs text-slate-400",
        gradient:
            "pointer-events-none absolute inset-0 bg-gradient-to-t from-black/80 via-black/45 to-black/10",
        imageHit:
            "absolute inset-0 z-[1] cursor-pointer",
        badgesCol:
            "absolute left-3 top-3 z-10 flex max-w-[70%] flex-col gap-1",
        weightPill:
            "inline-flex items-center border border-white/10 bg-[rgba(0,0,0,0.75)] px-2.5 py-1 text-[10px] font-medium text-slate-100 backdrop-blur",
        tagsRow: "flex flex-wrap gap-1",
        tagPill:
            "inline-flex items-center border px-2 py-0.5 text-[10px] font-medium backdrop-blur",
        favFab:
            "absolute right-3 top-3 z-10 flex h-10 w-10 items-center justify-center border border-white/15 bg-black/55 text-slate-200 transition-[transform,box-shadow,border-color,color] duration-300 ease-out hover:border-amber-400/60 hover:text-amber-200",
        favFabActive: "border-amber-400/60 text-amber-200",
        favFabIcon:
            "mdi text-xl transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]",
        feedbackRing:
            "pc-feedback-ring pointer-events-none absolute inset-0 ring-2 ring-amber-400/45",
        bottomStack:
            "absolute inset-x-3 bottom-3 z-10 flex flex-col gap-2.5",
        titleWrap: "w-2/3 min-w-0 pointer-events-none",
        titlePill:
            "bg-black/35 px-2.5 py-2 text-[13px] font-semibold leading-snug text-slate-50 line-clamp-3 shadow-[0_0_24px_rgba(0,0,0,0.7)] backdrop-blur",
        actionsRow: "flex w-full shrink-0 items-center gap-3",
        actionCluster:
            "relative flex w-fit min-w-0 items-center gap-2.5 border border-white/10 bg-black/35 px-3 py-1.5 shadow-[0_0_24px_rgba(0,0,0,0.7)] backdrop-blur",
        nutritionIconBtn:
            "flex h-10 w-10 items-center justify-center border border-amber-400/40 bg-black/55 text-amber-200 transition-transform duration-300 ease-out hover:border-amber-400/70 hover:text-amber-200",
        ingredientsIconBtn:
            "flex h-10 w-10 items-center justify-center border border-white/10 bg-black/55 text-slate-200 transition-transform duration-300 ease-out hover:border-amber-400/50 hover:text-amber-200",
        cartIconOuter: "flex h-10 shrink-0 items-center",
        cartAddCircle:
            "relative flex h-10 w-10 items-center justify-center bg-amber-400 text-black transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]",
        cartAddIcon:
            "mdi mdi-cart-outline text-xl transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]",
        feedbackCartRing:
            "pc-feedback-ring pc-feedback-ring--cart pointer-events-none absolute -inset-1 ring-2 ring-amber-300/55",
        qtyCluster:
            "flex h-10 items-center gap-0.5 border border-amber-400/50 bg-black/60 px-0.5 transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]",
        qtyMiniBtn:
            "flex h-9 w-9 shrink-0 items-center justify-center bg-black/50 text-base font-semibold leading-none text-slate-100 transition-colors hover:bg-black/70",
        qtyNum:
            "min-w-[1.5rem] px-0.5 text-center text-[12px] font-semibold tabular-nums text-amber-200",
        priceSide:
            "ml-auto flex min-h-10 shrink-0 items-center whitespace-nowrap bg-amber-400 px-3 py-1.5 text-[12px] font-semibold text-black transition-transform duration-200 hover:scale-[1.03] cursor-pointer",
        teleportTooltipBase:
            "fixed z-[1300] border border-white/10 bg-[rgba(0,0,0,0.95)] px-2.5 py-2.5 shadow-xl backdrop-blur",
        teleportTooltipNutritionWidth: "w-[190px]",
        teleportTooltipIngredientsWidth:
            "w-[210px] max-h-44 overflow-y-auto",
        teleportNutritionInner: "space-y-1 text-[11px] text-slate-100",
        teleportNutritionRow:
            "flex items-center justify-between gap-2",
        teleportNutritionLabel: "text-slate-300",
        teleportNutritionVal: "font-medium",
        teleportIngredientsInner: "space-y-1 text-[11px] text-slate-100",
        teleportIngredientsHeading:
            "text-[10px] font-medium text-slate-300",
        teleportIngredientsBody: "text-slate-200/90",
    },

    horizontalMobile: {
        article:
            "group flex overflow-hidden border border-white/10 bg-[rgba(255,255,255,0.02)] shadow-[0_10px_32px_rgba(0,0,0,0.7)]",
        thumbCol:
            "relative w-28 shrink-0 cursor-pointer overflow-hidden sm:w-32",
        thumbImg:
            "h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105",
        thumbPlaceholder:
            "flex h-full w-full items-center justify-center bg-slate-900/70 text-xs text-slate-400",
        thumbGradient:
            "pointer-events-none absolute inset-0 bg-gradient-to-r from-black/5 via-black/20 to-black/50",
        body: "flex min-w-0 flex-1 justify-between gap-3 p-3",
        textCol: "min-w-0 flex-1",
        title:
            "line-clamp-2 text-sm font-semibold leading-snug text-slate-100",
        weightMuted: "mt-1 text-[11px] text-slate-400",
        actionsCluster:
            "relative mt-3 flex flex-wrap items-center gap-2",
        favBtn:
            "flex h-9 w-9 items-center justify-center border border-white/15 bg-black/45 text-slate-100 transition hover:border-amber-400/60 hover:text-amber-200",
        favBtnActive: "border-amber-400/60 text-amber-200",
        favIcon: "mdi text-lg",
        nutritionBtn:
            "flex h-9 w-9 items-center justify-center border border-amber-400/40 bg-black/45 text-amber-200 transition hover:border-amber-400/70",
        nutritionIcon: "mdi mdi-fire-circle text-lg",
        ingredientsBtn:
            "flex h-9 w-9 items-center justify-center border border-white/10 bg-black/45 text-slate-100 transition hover:border-amber-400/60 hover:text-amber-200",
        ingredientsIcon: "mdi mdi-information-outline text-lg",
        cartAdd:
            "flex h-9 w-9 items-center justify-center bg-amber-400 text-black transition hover:scale-105",
        cartAddIcon: "mdi mdi-cart-outline text-lg",
        qtyCluster:
            "flex h-9 items-center border border-amber-400/50 bg-black/60 px-1",
        qtyMiniBtn:
            "flex h-7 w-7 items-center justify-center text-sm font-semibold text-slate-100 transition hover:bg-black/50",
        qtyNum:
            "min-w-[1.25rem] px-1 text-center text-xs font-semibold text-amber-200",
        rightCol:
            "flex shrink-0 flex-col items-end justify-between gap-3",
        priceBtn:
            "bg-amber-400 px-2.5 py-1.5 text-xs font-semibold text-black transition hover:scale-[1.03]",
        weightEcho: "text-[11px] text-slate-400",
        teleportTooltipBase:
            "fixed z-[1300] border border-white/10 bg-[rgba(0,0,0,0.95)] px-2.5 py-2 shadow-xl backdrop-blur",
        teleportTooltipNutritionWidth: "w-[180px]",
        teleportTooltipIngredientsWidth:
            "w-[210px] max-h-44 overflow-y-auto",
        teleportNutritionInner: "space-y-1 text-[11px] text-slate-100",
        teleportNutritionRow:
            "flex items-center justify-between gap-2",
        teleportNutritionLabel: "text-slate-300",
        teleportNutritionVal: "font-medium",
        teleportIngredientsInner: "space-y-1 text-[11px] text-slate-100",
        teleportIngredientsHeading:
            "text-[10px] font-medium text-slate-300",
        teleportIngredientsBody: "text-slate-200/90",
    },
} as const;

export type CatalogCardsDesign = typeof catalogCardsDesign;
