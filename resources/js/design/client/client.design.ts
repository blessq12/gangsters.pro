/**
 * Профиль клиента: формы, адреса, история заказов, блок контактов.
 */

export const clientDesign = {
    shared: {
        formRoot: "space-y-4 text-slate-50",
        fieldStack: "space-y-3",
        headingH3: "text-base font-semibold text-slate-50",
        leadMuted: "text-xs text-slate-400",
        tabRow: "flex flex-wrap gap-2",
        tabPillBase: "rounded-none px-3 py-1 text-[11px] font-medium transition",
        tabPillActive:
            "bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]",
        tabPillInactive: "bg-white/5 text-slate-200 hover:bg-white/10",
        label: "mb-1 block text-xs font-medium text-slate-300",
        input:
            "w-full rounded-none border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60",
        inputGrid11:
            "rounded-none border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60",
        inputCol2:
            "col-span-2 rounded-none border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60",
        textarea:
            "w-full rounded-none border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60",
        checkbox:
            "h-4 w-4 rounded-none border-white/20 bg-black/60 text-amber-400 focus:ring-amber-400/60",
        checkboxSm:
            "h-3.5 w-3.5 rounded-none border-white/20 bg-black/60 text-amber-400 focus:ring-amber-400/60",
        checkboxRow: "flex items-center gap-2 text-xs text-slate-300",
        checkboxRowMuted: "flex items-center gap-2 text-xs text-slate-400",
        checkboxRow11: "flex items-center gap-2 text-[11px] text-slate-300",
        errorXs: "text-xs text-red-400",
        error11: "text-[11px] text-red-400",
        btnPrimaryWide:
            "inline-flex w-full items-center justify-center rounded-none bg-amber-400 px-4 py-2 text-sm font-semibold text-black shadow-[0_0_18px_rgba(251,191,36,0.75)] transition hover:bg-amber-300 disabled:opacity-60 disabled:shadow-none",
        btnPrimaryCompact:
            "inline-flex w-full items-center justify-center rounded-none bg-amber-400 px-3 py-1.5 text-[11px] font-semibold text-black shadow-[0_0_14px_rgba(251,191,36,0.7)] transition hover:bg-amber-300 disabled:opacity-60 disabled:shadow-none",
        forgotSectionTop: "space-y-2 border-t border-white/10 pt-3",
        forgotToggle:
            "text-[11px] text-amber-400/90 underline-offset-2 hover:text-amber-300 hover:underline",
        forgotIsland:
            "space-y-2 rounded-none border border-white/10 bg-black/30 p-3",
        forgotHint: "text-[11px] text-slate-400",
        forgotSubmitBtn:
            "inline-flex w-full items-center justify-center rounded-none border border-amber-400/50 bg-transparent px-3 py-1.5 text-xs font-medium text-amber-300 hover:bg-amber-400/10 disabled:opacity-50",
        addressGrid: "grid grid-cols-2 gap-2",
    },

    profileView: {
        root: "space-y-4 text-slate-50",
        sectionKicker:
            "text-xs font-medium uppercase tracking-wide text-slate-400",
        headerRow: "flex items-center justify-between gap-3",
        userRow: "flex items-center gap-3",
        avatar:
            "flex h-12 w-12 items-center justify-center rounded-none border border-amber-400/40 bg-black/70 text-base font-semibold text-amber-200 shadow-[0_0_20px_rgba(251,191,36,0.6)]",
        userTextCol: "min-w-0",
        nameStrong: "text-sm font-semibold text-slate-50",
        phoneLine: "text-xs text-slate-300/85",
        emailLine: "text-xs text-slate-400",
        btnLogout:
            "ml-3 inline-flex items-center rounded-none border border-red-500/70 bg-red-500/10 px-3 py-1 text-[11px] font-semibold text-red-200 transition hover:bg-red-500/20 hover:text-red-100",
        statsSection: "space-y-2",
        statsHint: "text-[10px] leading-snug text-slate-500",
        statLoading:
            "rounded-none border border-white/10 bg-black/30 px-3 py-4 text-center text-xs text-slate-400",
        statError:
            "rounded-none border border-amber-400/25 bg-black/35 px-3 py-3 text-[11px] text-slate-400",
        statErrorAccent: "text-amber-200/90",
        statErrorSub: "mt-1 block text-slate-500",
        statEmpty:
            "rounded-none border border-dashed border-white/12 bg-black/25 px-3 py-4 text-center text-xs text-slate-400",
        statGrid: "grid grid-cols-2 gap-2",
        statCard:
            "rounded-none border border-white/10 bg-black/35 px-3 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.04)]",
        statCardWide:
            "col-span-2 rounded-none border border-white/10 bg-black/35 px-3 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.04)]",
        statLabel:
            "text-[10px] font-medium uppercase tracking-wide text-slate-500",
        statValueAccent: "mt-1 text-xl font-semibold tabular-nums text-amber-300",
        statValueMain: "mt-1 text-lg font-semibold tabular-nums text-slate-50",
        statLastRow:
            "flex flex-wrap items-baseline justify-between gap-2",
        statDateLine: "mt-0.5 text-xs text-slate-200",
        statRightCol: "text-right",
        avgValue:
            "mt-0.5 text-sm font-semibold tabular-nums text-amber-200/95",
        footerHint: "text-[11px] leading-relaxed text-slate-500",
    },

    orderHistory: {
        root: "space-y-3 text-slate-50",
        stateLoading:
            "rounded-none border border-white/10 bg-black/30 px-4 py-6 text-center text-sm text-slate-400",
        stateError:
            "rounded-none border border-red-500/40 bg-red-950/30 px-4 py-3 text-sm text-red-200",
        stateEmpty:
            "rounded-none border border-dashed border-white/15 bg-black/25 px-4 py-6 text-center text-sm text-slate-400",
        list: "max-h-[min(28rem,55vh)] space-y-2 overflow-y-auto pr-1",
        card: "rounded-none border border-white/10 bg-black/35",
        cardHeadBtn:
            "flex w-full items-start justify-between gap-3 px-3 py-3 text-left transition hover:bg-white/[0.04] sm:px-4",
        cardHeadMain: "min-w-0 flex-1",
        cardHeadAside: "shrink-0 text-right",
        cardBody: "border-t border-white/5 px-3 pb-3 pt-2 sm:px-4",
        monoId: "text-[11px] font-mono text-amber-200/90",
        dateMuted: "mt-0.5 text-xs text-slate-400",
        statusLine: "mt-1 text-xs text-slate-300",
        mutedInline: "text-slate-500",
        sumStrong: "text-sm font-semibold text-amber-300",
        expandHint: "text-[11px] text-slate-500",
        itemsList: "space-y-2 text-xs text-slate-200",
        itemRow: "flex justify-between gap-2",
        itemName: "min-w-0 truncate",
        itemQtyMuted: "text-slate-500",
        itemPrice: "shrink-0 text-slate-300",
        paymentFoot:
            "mt-3 border-t border-white/5 pt-2 text-[11px] text-slate-500",
        moreHint: "text-center text-[11px] text-slate-500",
    },

    addresses: {
        root: "space-y-3 text-slate-50",
        listStack: "space-y-2 text-xs",
        card: "rounded-none border border-white/10 bg-black/40 px-3 py-2",
        cardRow: "flex items-center justify-between gap-2",
        titleStrong: "font-medium text-slate-50",
        metaLine: "mt-1 text-slate-300",
        actionsCol: "flex flex-col items-end gap-1",
        btnSelect:
            "rounded-none bg-white/5 px-2 py-0.5 text-[10px] text-slate-200 hover:bg-white/10",
        linkRemove: "text-[10px] text-slate-500 hover:text-red-400",
        commentLine: "mt-1 text-[11px] text-slate-400",
        emptyHint: "text-xs text-slate-400",
        addSection: "mt-3 border-t border-white/5 pt-2 text-xs",
        expandBtn:
            "flex w-full items-center justify-between rounded-none bg-white/5 px-3 py-2 text-[11px] font-medium text-slate-100 hover:bg-white/10",
        expandChevron: "text-[11px] text-slate-400",
        addForm: "mt-3 space-y-2 text-xs",
    },
} as const;
