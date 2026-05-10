/**
 * Презентация чекаута: все шаги и общие паттерны полей/кнопок.
 */

export const checkoutDesign = {
    shared: {
        stepKicker:
            "text-[11px] uppercase tracking-[0.18em] text-slate-400",
        stepKickerAccent:
            "text-[11px] uppercase tracking-[0.18em] text-amber-300",
        flowBody: "space-y-3 text-xs sm:text-sm text-slate-200",
        headingSm: "text-xs font-semibold text-slate-100",
        headingCardMuted: "text-[11px] font-semibold text-slate-300",
        subsectionKickerSm:
            "text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400",
        subsectionKickerXs:
            "pt-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400",
        subsectionKickerXsSpaced:
            "pt-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400",
        pillRound: "rounded-none px-3 py-1 transition",
        pillRoundText: "rounded-none px-3 py-1 text-[11px] transition",
        pillActive:
            "bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]",
        pillInactive:
            "bg-white/5 text-slate-200 hover:bg-white/10",
        introMuted: "text-xs text-slate-300",
        authTabRow: "flex gap-2 text-[11px] font-medium",
        guestIsland:
            "space-y-2 rounded-none border border-white/10 bg-black/30 px-3 py-3",
        grid2: "grid grid-cols-2 gap-2",
        addressEmptyHint:
            "rounded-none border border-dashed border-slate-600/60 bg-black/40 px-4 py-3 text-[11px] text-slate-300",
        addressLi:
            "flex items-center gap-2 rounded-none border border-white/10 bg-black/40 px-3 py-2",
        radioField:
            "h-4 w-4 rounded-none border-slate-400 text-amber-400 focus:ring-amber-400",
        checkboxSm:
            "h-3.5 w-3.5 rounded-none border-white/20 bg-black/60 text-amber-400 focus:ring-amber-400/60",
        labelAddress: "flex-1 cursor-pointer text-xs text-slate-200",
        addressTitle: "block font-medium text-slate-100",
        addressMeta: "block text-[11px] text-slate-400",
        borderSectionTop:
            "space-y-2 border-t border-white/5 pt-3",
        expandRowBtn:
            "flex w-full items-center justify-between rounded-none bg-white/5 px-3 py-2 text-[11px] font-medium text-slate-100 hover:bg-white/10",
        expandRowChevronMuted: "text-[11px] text-slate-400",
        newAddressWrap: "space-y-2 pt-1",
        inputFieldCol2:
            "col-span-2 rounded-none border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60",
        inputFieldFull:
            "w-full rounded-none border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60",
        inputFieldGridCell:
            "rounded-none border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60",
        textareaAddress:
            "w-full rounded-none border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60",
        textareaFlow:
            "w-full rounded-none border border-white/10 bg-black/40 px-3 py-2 text-xs text-slate-100 placeholder-slate-500 outline-none focus:border-amber-400",
        checkboxLabelRow:
            "flex items-center gap-2 text-[11px] text-slate-300",
        saveSecondaryBtn:
            "inline-flex w-full items-center justify-center rounded-none bg-white/5 px-3 py-1.5 text-[11px] font-medium text-slate-100 transition hover:bg-white/10 disabled:opacity-50",
        navFooterRow:
            "mt-2 flex items-center justify-between text-[11px] text-slate-400",
        linkUnderline: "underline-offset-2 hover:underline",
        btnPrimarySm:
            "inline-flex items-center justify-center rounded-none bg-amber-400 px-3 py-1.5 text-[11px] font-semibold text-black shadow-[0_0_14px_rgba(251,191,36,0.7)] transition hover:bg-amber-300",
        btnPrimarySmBusy:
            "inline-flex items-center justify-center rounded-none bg-amber-400 px-3 py-1.5 text-[11px] font-semibold text-black shadow-[0_0_14px_rgba(251,191,36,0.7)] transition hover:bg-amber-300 disabled:opacity-60",
        btnPrimaryMd:
            "inline-flex items-center justify-center rounded-none bg-amber-400 px-4 py-2 text-xs font-semibold text-black shadow-[0_0_18px_rgba(251,191,36,0.7)] transition hover:bg-amber-300",
        btnSecondaryOutline:
            "inline-flex w-full items-center justify-center rounded-none border border-white/15 bg-white/5 px-4 py-2 text-xs font-medium text-slate-100 transition hover:bg-white/10",
        btnSecondaryOutlineCompact:
            "w-full rounded-none border border-white/10 bg-white/5 px-3 py-2 text-center text-[11px] font-medium text-slate-200 transition hover:bg-white/10",
        errorBanner:
            "rounded-none border border-red-500/40 bg-red-950/40 px-3 py-2 text-[11px] text-red-200",
        errorLine: "text-[11px] text-red-400",
        mutedNote: "text-[11px] text-slate-400",
        textBodyXs: "text-xs text-slate-200",
        textMutedLine: "text-[11px] text-slate-400",
        textSuccessLead: "text-sm font-semibold text-slate-50",
        textSuccessBody: "text-xs text-slate-300",
        monoAccent: "font-mono text-amber-300",
        spacerAfterComment: "space-y-1",
    },

    cart: {
        emptyState:
            "rounded-none bg-[rgba(255,255,255,0.03)] px-4 py-5 text-sm text-slate-300",
        userList: "space-y-2 text-xs sm:text-sm text-slate-200",
        qtyBar:
            "inline-flex items-center justify-between rounded-none border border-amber-400/60 bg-black/70 px-2 py-1 text-xs text-slate-50",
        qtyBtn:
            "flex h-6 w-6 items-center justify-center rounded-none bg-black/70 text-[14px]",
        qtyLabel: "px-2 font-semibold",
        userLineItem:
            "flex items-center justify-between gap-3 rounded-none bg-[rgba(255,255,255,0.03)] px-3 py-2",
        lineTitle: "truncate font-medium text-slate-100",
        lineSub: "mt-0.5 text-[11px] text-slate-400",
        lineActions: "flex items-center gap-3",
        removeLink:
            "shrink-0 text-[11px] text-slate-400 transition-colors hover:text-red-400",
        systemList:
            "mt-2 rounded-none border border-amber-400/25 bg-amber-400/8 px-2.5 py-2 text-[11px] text-slate-200",
        systemLine:
            "mt-1 flex items-center justify-between gap-2 rounded-none px-1 py-0.5",
        totalsCard:
            "mt-3 space-y-1 rounded-none border border-white/10 bg-[rgba(255,255,255,0.02)] px-3 py-2 text-xs sm:text-sm",
        totalsRow: "flex items-center justify-between",
        totalsLabelMuted: "text-slate-300/85",
        totalsLabelStrong: "font-medium text-slate-300/90",
        totalsValue: "text-slate-100",
        totalsDivider:
            "flex items-center justify-between border-t border-white/10 pt-1",
        grandTotal: "font-semibold text-amber-300",
        giftCard:
            "mt-3 rounded-none border border-amber-300/25 bg-amber-400/10 px-3 py-2 text-xs sm:text-sm",
        giftRow: "flex items-center justify-between gap-2",
        giftTitle: "font-semibold text-amber-200",
        giftSelectedHint: "mt-0.5 truncate text-[11px] text-slate-300",
        giftCta:
            "shrink-0 rounded-none border border-amber-300/60 bg-black/40 px-3 py-1 text-[11px] font-medium text-amber-200 transition hover:bg-black/60",
        authActions: "mt-3 flex flex-col gap-2",
        giftModalList: "space-y-2",
        giftRadioLabel:
            "flex cursor-pointer items-center gap-3 rounded-none border border-white/10 bg-white/5 px-3 py-2 transition hover:border-amber-300/40",
        giftRadioInput: "h-4 w-4 accent-amber-300",
        giftRadioBody: "min-w-0 flex-1",
        giftRadioTitle: "truncate text-sm font-medium text-slate-100",
        giftRadioPrice: "text-xs text-slate-400",
        giftThumb: "h-10 w-10 rounded-none object-cover",
        giftFooterRow: "flex justify-end",
        giftApplyBtn:
            "inline-flex items-center justify-center rounded-none bg-amber-400 px-4 py-2 text-xs font-semibold text-black transition hover:bg-amber-300 disabled:cursor-not-allowed disabled:opacity-60",
    },

    auth: {
        footerCol: "mt-2 flex flex-col gap-2 text-[11px] text-slate-400",
    },

    delivery: {
        methodRow: "flex flex-wrap gap-2",
    },

    confirm: {
        summaryCard:
            "space-y-2 rounded-none bg-[rgba(255,255,255,0.03)] px-3 py-3",
        orderList: "space-y-1 text-xs",
        orderLineRow: "flex items-center justify-between gap-2",
        orderLineTruncate: "truncate text-slate-100",
        orderLineMuted: "shrink-0 text-slate-300",
        systemLineAccent:
            "flex items-center justify-between gap-2 rounded-none border border-amber-400/25 bg-amber-400/10 px-2 py-1",
        badgeTiny: "ml-1 text-[10px] font-medium text-amber-200",
        totalsInset:
            "mt-2 space-y-1 border-t border-white/5 pt-2 text-xs",
        blockMuted:
            "space-y-1 rounded-none bg-[rgba(255,255,255,0.02)] px-3 py-3",
        mutedInline: "ml-1 text-slate-400",
    },

    success: {
        footerActions: "mt-2 flex justify-end",
    },
} as const;
