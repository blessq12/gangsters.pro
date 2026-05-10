/**
 * Профиль клиента: формы, адреса, история заказов, блок контактов.
 */

export const clientDesign = {
    shared: {
        formRoot: "space-y-4 text-app-canvas-fg",
        fieldStack: "space-y-3",
        headingH3: "text-base font-semibold text-app-canvas-fg",
        leadMuted: "text-xs text-app-muted",
        tabRow: "flex flex-wrap gap-2",
        tabPillBase: "rounded-none px-3 py-1 text-[11px] font-medium transition",
        tabPillActive:
            "bg-app-accent text-black shadow-[0_0_14px_rgba(198,36,36,0.7)]",
        tabPillInactive: "bg-black/5 text-app-canvas-fg hover:bg-black/8",
        label: "mb-1 block text-xs font-medium text-app-muted",
        input:
            "w-full rounded-none border border-black/12 bg-app-surface px-3 py-2 text-sm text-app-surface-fg placeholder:text-neutral-600 focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        inputGrid11:
            "rounded-none border border-black/12 bg-app-surface px-3 py-2 text-[11px] text-app-surface-fg placeholder:text-neutral-600 focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        inputCol2:
            "col-span-2 rounded-none border border-black/12 bg-app-surface px-3 py-2 text-[11px] text-app-surface-fg placeholder:text-neutral-600 focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        textarea:
            "w-full rounded-none border border-black/12 bg-app-surface px-3 py-2 text-[11px] text-app-surface-fg placeholder:text-neutral-600 focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        checkbox:
            "h-4 w-4 rounded-none border-black/20 bg-neutral-950/85 text-app-accent focus:ring-app-accent/60",
        checkboxSm:
            "h-3.5 w-3.5 rounded-none border-black/20 bg-neutral-950/85 text-app-accent focus:ring-app-accent/60",
        checkboxRow: "flex items-center gap-2 text-xs text-app-muted",
        checkboxRowMuted: "flex items-center gap-2 text-xs text-app-muted",
        checkboxRow11: "flex items-center gap-2 text-[11px] text-app-muted",
        errorXs: "text-xs text-red-400",
        error11: "text-[11px] text-red-400",
        btnPrimaryWide:
            "inline-flex w-full items-center justify-center rounded-none bg-app-accent px-4 py-2 text-sm font-semibold text-black shadow-[0_0_18px_rgba(198,36,36,0.75)] transition hover:bg-app-accent-hover disabled:opacity-60 disabled:shadow-none",
        btnPrimaryCompact:
            "inline-flex w-full items-center justify-center rounded-none bg-app-accent px-3 py-1.5 text-[11px] font-semibold text-black shadow-[0_0_14px_rgba(198,36,36,0.7)] transition hover:bg-app-accent-hover disabled:opacity-60 disabled:shadow-none",
        forgotSectionTop: "space-y-2 border-t border-black/12 pt-3",
        forgotToggle:
            "text-[11px] text-app-accent/90 underline-offset-2 hover:text-app-accent hover:underline",
        forgotIsland:
            "space-y-2 rounded-none border border-black/12 bg-app-surface p-3",
        forgotHint: "text-[11px] text-app-muted",
        forgotSubmitBtn:
            "inline-flex w-full items-center justify-center rounded-none border border-app-accent/50 bg-transparent px-3 py-1.5 text-xs font-medium text-app-accent hover:bg-app-accent/10 disabled:opacity-50",
        addressGrid: "grid grid-cols-2 gap-2",
    },

    profileView: {
        root: "space-y-4 text-app-canvas-fg",
        sectionKicker:
            "text-xs font-medium uppercase tracking-wide text-app-muted",
        headerRow: "flex items-center justify-between gap-3",
        userRow: "flex items-center gap-3",
        avatar:
            "flex h-12 w-12 items-center justify-center rounded-none border border-app-accent/40 bg-neutral-950/88 text-base font-semibold text-app-accent shadow-[0_0_20px_rgba(198,36,36,0.6)]",
        userTextCol: "min-w-0",
        nameStrong: "text-sm font-semibold text-app-canvas-fg",
        phoneLine: "text-xs text-app-muted/85",
        emailLine: "text-xs text-app-muted",
        btnLogout:
            "ml-3 inline-flex items-center rounded-none border border-red-500/70 bg-red-500/10 px-3 py-1 text-[11px] font-semibold text-red-200 transition hover:bg-red-500/20 hover:text-red-100",
        statsSection: "space-y-2",
        statsHint: "text-[10px] leading-snug text-app-muted",
        statLoading:
            "rounded-none border border-black/12 bg-app-surface px-3 py-4 text-center text-xs text-neutral-600",
        statError:
            "rounded-none border border-app-accent/25 bg-app-surface px-3 py-3 text-[11px] text-neutral-600",
        statErrorAccent: "text-app-accent/90",
        statErrorSub: "mt-1 block text-app-muted",
        statEmpty:
            "rounded-none border border-dashed border-white/12 bg-app-surface px-3 py-4 text-center text-xs text-neutral-600",
        statGrid: "grid grid-cols-2 gap-2",
        statCard:
            "rounded-none border border-black/12 bg-app-surface px-3 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.04)]",
        statCardWide:
            "col-span-2 rounded-none border border-black/12 bg-app-surface px-3 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.04)]",
        statLabel:
            "text-[10px] font-medium uppercase tracking-wide text-app-muted",
        statValueAccent: "mt-1 text-xl font-semibold tabular-nums text-app-accent",
        statValueMain: "mt-1 text-lg font-semibold tabular-nums text-app-canvas-fg",
        statLastRow:
            "flex flex-wrap items-baseline justify-between gap-2",
        statDateLine: "mt-0.5 text-xs text-app-canvas-fg",
        statRightCol: "text-right",
        avgValue:
            "mt-0.5 text-sm font-semibold tabular-nums text-app-accent/95",
        footerHint: "text-[11px] leading-relaxed text-app-muted",
    },

    orderHistory: {
        root: "space-y-3 text-app-canvas-fg",
        stateLoading:
            "rounded-none border border-black/12 bg-app-surface px-4 py-6 text-center text-sm text-neutral-600",
        stateError:
            "rounded-none border border-red-500/40 bg-red-950/30 px-4 py-3 text-sm text-red-200",
        stateEmpty:
            "rounded-none border border-dashed border-black/15 bg-app-surface px-4 py-6 text-center text-sm text-neutral-600",
        list: "max-h-[min(28rem,55vh)] space-y-2 overflow-y-auto pr-1",
        card: "rounded-none border border-black/12 bg-app-surface",
        cardHeadBtn:
            "flex w-full items-start justify-between gap-3 px-3 py-3 text-left transition hover:bg-white/[0.04] sm:px-4",
        cardHeadMain: "min-w-0 flex-1",
        cardHeadAside: "shrink-0 text-right",
        cardBody: "border-t border-white/5 px-3 pb-3 pt-2 sm:px-4",
        monoId: "text-[11px] font-mono text-app-accent/90",
        dateMuted: "mt-0.5 text-xs text-app-muted",
        statusLine: "mt-1 text-xs text-app-muted",
        mutedInline: "text-app-muted",
        sumStrong: "text-sm font-semibold text-app-accent",
        expandHint: "text-[11px] text-app-muted",
        itemsList: "space-y-2 text-xs text-app-canvas-fg",
        itemRow: "flex justify-between gap-2",
        itemName: "min-w-0 truncate",
        itemQtyMuted: "text-app-muted",
        itemPrice: "shrink-0 text-app-muted",
        paymentFoot:
            "mt-3 border-t border-white/5 pt-2 text-[11px] text-app-muted",
        moreHint: "text-center text-[11px] text-app-muted",
    },

    addresses: {
        root: "space-y-3 text-app-canvas-fg",
        listStack: "space-y-2 text-xs",
        card: "rounded-none border border-black/12 bg-app-surface px-3 py-2",
        cardRow: "flex items-center justify-between gap-2",
        titleStrong: "font-medium text-app-canvas-fg",
        metaLine: "mt-1 text-app-muted",
        actionsCol: "flex flex-col items-end gap-1",
        btnSelect:
            "rounded-none bg-black/5 px-2 py-0.5 text-[10px] text-app-canvas-fg hover:bg-black/8",
        linkRemove: "text-[10px] text-app-muted hover:text-red-400",
        commentLine: "mt-1 text-[11px] text-app-muted",
        emptyHint: "text-xs text-app-muted",
        addSection: "mt-3 border-t border-white/5 pt-2 text-xs",
        expandBtn:
            "flex w-full items-center justify-between rounded-none bg-black/5 px-3 py-2 text-[11px] font-medium text-app-canvas-fg hover:bg-black/8",
        expandChevron: "text-[11px] text-app-muted",
        addForm: "mt-3 space-y-2 text-xs",
    },
} as const;
