/**
 * Модалка товара и связанные блоки (инфо поверх медиа, галерея внутри модалки).
 */

export const catalogModalDesign = {
    shell: {
        root:
            "fixed inset-0 z-[9999] flex items-center justify-center",
        backdrop: "absolute inset-0 bg-black/50",
        backdropMobile: "absolute inset-0 bg-black/55",
        content:
            "relative z-[1] flex w-full max-h-screen items-center justify-center overflow-hidden p-6",
        contentMobile: "p-[0.35rem_0.6rem]",
        wrapper: "m-auto w-full max-w-4xl",
        wrapperMobile: "m-auto w-full max-w-full",
        panel:
            "relative aspect-video max-h-[85vh] w-full overflow-hidden border border-white/[0.08] bg-black/30 shadow-[0_25px_60px_rgba(0,0,0,0.9)]",
        panelMobile:
            "relative w-full min-h-0 h-[92dvh] max-h-[100dvh] overflow-hidden border border-white/10 bg-[rgba(31,31,35,0.65)] shadow-[0_25px_60px_rgba(0,0,0,0.92)]",
        closeBtn:
            "absolute right-4 top-4 z-[3] flex h-9 w-9 items-center justify-center border border-white/15 bg-black/50 text-xl text-slate-400 transition-colors hover:border-amber-400/50 hover:text-amber-300",
        closeBtnMobile: "left-3 right-auto top-3",
        closeIcon: "mdi mdi-close",
        body: "absolute inset-0 z-0",
        /** Зона галереи: отдельный градиентный оверлей в шаблоне (дочерний div). */
        mediaZone:
            "absolute inset-0 z-0 [&_.product-gallery]:h-full [&_.product-gallery]:min-h-full [&_.product-gallery]:w-full",
        mediaOverlayDesktop:
            "pointer-events-none absolute inset-0 z-[1] bg-gradient-to-t from-black/95 via-black/40 to-transparent",
        mediaOverlayMobile:
            "pointer-events-none absolute inset-0 z-[1] bg-gradient-to-t from-black/95 via-black/45 to-transparent",
        emptyCopy: "Нет данных о товаре.",
        empty:
            "absolute inset-0 z-[1] m-0 flex items-center justify-center p-8 text-sm text-slate-400",
        emptyMobile:
            "static flex items-center justify-center p-8 text-sm text-slate-400",
        info:
            "absolute bottom-3 left-3 right-3 z-[2] flex max-h-[38%] min-w-0 flex-col justify-end",
        infoMobile:
            "bottom-[0.6rem] left-[0.6rem] right-[0.6rem] max-h-[55%]",
    },

    detailInfo: {
        card: "flex flex-col gap-3",
        titleCol: "w-2/3 min-w-0",
        title:
            "bg-black/35 px-2.5 py-2 text-[13px] font-semibold leading-snug text-slate-50 line-clamp-3 shadow-[0_0_24px_rgba(0,0,0,0.7)] backdrop-blur",
        tagsRow: "mt-2 flex flex-wrap gap-1.5",
        tagPill:
            "inline-flex items-center border px-2 py-0.5 text-[10px] font-medium backdrop-blur",
        controlsRow:
            "flex w-full shrink-0 items-center gap-3",
        actionIsland:
            "relative flex w-fit min-w-0 items-center gap-2.5 border border-white/10 bg-black/35 px-3 py-1.5 shadow-[0_0_24px_rgba(0,0,0,0.7)] backdrop-blur",
        favBtn:
            "flex h-10 w-10 items-center justify-center border border-white/15 bg-black/55 text-slate-200 transition-colors hover:border-amber-400/60 hover:text-amber-200",
        favBtnActive: "border-amber-400/60 text-amber-200",
        favIcon: "mdi text-xl",
        nutritionBtn:
            "flex h-10 w-10 items-center justify-center border border-amber-400/40 bg-black/55 text-amber-200 transition-colors hover:border-amber-400/70 hover:text-amber-200",
        nutritionIcon: "mdi mdi-fire-circle text-xl",
        ingredientsBtn:
            "flex h-10 w-10 items-center justify-center border border-white/10 bg-black/55 text-slate-200 transition-colors hover:border-amber-400/50 hover:text-amber-200",
        ingredientsIcon: "mdi mdi-information-outline text-xl",
        cartBtnWrap: "flex h-10 shrink-0 items-center",
        cartAdd:
            "flex h-10 w-10 items-center justify-center bg-amber-400 text-black",
        cartIcon: "mdi mdi-cart-outline text-xl",
        qtyCluster:
            "flex h-10 items-center gap-0.5 border border-amber-400/50 bg-black/60 px-0.5",
        qtyMiniBtn:
            "flex h-9 w-9 shrink-0 items-center justify-center bg-black/50 text-base font-semibold leading-none text-slate-100",
        qtyNum:
            "min-w-[1.5rem] px-0.5 text-center text-[12px] font-semibold tabular-nums text-amber-200",
        priceBtn:
            "ml-auto flex min-h-10 shrink-0 items-center whitespace-nowrap bg-amber-400 px-3 py-1.5 text-[12px] font-semibold text-black transition-transform duration-200 hover:scale-[1.03] cursor-pointer",
        teleportTooltipBase:
            "fixed z-[10000] border border-white/10 bg-[rgba(0,0,0,0.95)] px-3 py-2.5 shadow-xl backdrop-blur max-h-44 overflow-y-auto",
        tooltipWide: "w-[260px]",
        tooltipNarrow: "w-[240px]",
        nutritionBlock: "space-y-2 text-[11px] text-slate-100",
        nutritionRow:
            "flex items-center justify-between gap-2",
        nutritionLabel: "text-slate-300",
        nutritionVal: "font-medium",
        ingredientsBlock: "space-y-2 text-[11px] text-slate-100",
        ingredientsHeading:
            "text-[10px] font-medium text-slate-300",
        ingredientsBody: "break-words text-slate-200/90",
    },

    gallery: {
        root: "product-gallery relative w-full h-64 bg-slate-800/50 overflow-hidden sm:h-80",
        noPhotoLabel: "Нет фото",
        noPhoto:
            "flex h-full w-full items-center justify-center text-sm text-slate-500",
        swiper: "product-gallery__swiper w-full h-full",
        slide: "product-gallery__slide w-full h-full",
        wrap: "product-gallery__wrap w-full h-full",
        img: "product-gallery__img w-full h-full object-cover object-center",
    },
} as const;

export type CatalogModalDesign = typeof catalogModalDesign;
