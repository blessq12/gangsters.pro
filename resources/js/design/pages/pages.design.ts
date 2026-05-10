/**
 * Презентация страниц-роутов (Vue Router).
 */

const searchInputClasses =
    "w-full rounded-none border border-white/10 bg-black/40 py-2.5 pl-10 pr-10 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/50";

export const pagesDesign = {
    home: {
        desktop: {
            root: "home-page mt-4 space-y-10",
            menuHeader: "mb-4 flex items-end justify-between gap-4",
            menuTitleBlock: "block",
            menuTitle: "text-xl font-semibold leading-tight text-slate-50",
            menuSubtitle: "text-sm text-slate-400",
            searchCol: "w-full max-w-xs shrink-0",
            srOnlyLabel: "sr-only",
            searchWrap: "relative",
            searchIconPos:
                "pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-500",
            searchInput: searchInputClasses,
            searchClear:
                "absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-none text-slate-400 transition hover:bg-white/10 hover:text-slate-200",
            catalogControlsRow: "-mt-6 mb-4 flex justify-end",
        },
        mobile: {
            root: "home-page mt-4 space-y-8",
            menuHeader: "mb-4 flex flex-col gap-3",
            menuTitle: "text-xl font-semibold text-slate-50",
            menuSubtitle: "text-sm text-slate-400",
            srOnlyLabel: "sr-only",
            searchWrapOuter: "w-full",
            searchWrap: "relative",
            searchIconPos:
                "pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-500",
            searchInput: searchInputClasses,
            searchClear:
                "absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-none text-slate-400 transition hover:bg-white/10 hover:text-slate-200",
            viewControls: "mb-4",
        },
    },

    resetPassword: {
        pageWrap: "mx-auto max-w-md py-8 text-slate-50",
        title: "mb-1 text-lg font-semibold text-slate-50",
        lead: "mb-6 text-xs text-slate-400",
        form:
            "space-y-4 rounded-none border border-amber-400/20 bg-[rgba(0,0,0,0.35)] px-4 py-5",
        label: "mb-1 block text-xs font-medium text-slate-300",
        input:
            "w-full rounded-none border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60",
        error: "text-xs text-red-400",
        submitBtn:
            "inline-flex w-full items-center justify-center rounded-none bg-amber-400 px-4 py-2 text-sm font-semibold text-black shadow-[0_0_18px_rgba(251,191,36,0.75)] transition hover:bg-amber-300 disabled:opacity-60",
        noTokenCard:
            "rounded-none border border-red-500/30 bg-black/30 px-4 py-5 text-sm text-slate-300",
        noTokenLead: "mb-3",
        homeLink:
            "text-sm font-medium text-amber-400 hover:text-amber-300",
    },

    about: {
        gridTop:
            "grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(280px,0.8fr)]",
        asideStack: "grid gap-4",
        spotlightArticle:
            "overflow-hidden rounded-none border border-white/10 bg-[rgba(255,255,255,0.04)] p-5 shadow-[0_16px_50px_rgba(0,0,0,0.35)]",
        spotlightHeader: "mb-4 flex items-center justify-between",
        eyebrowTag:
            "inline-flex rounded-none border border-amber-400/30 bg-amber-400/10 px-3 py-1 text-[11px] uppercase tracking-[0.24em] text-amber-200",
        spotlightTitle: "text-lg font-semibold text-slate-50",
        spotlightBody: "mt-2 text-sm leading-relaxed text-slate-300",
        tagsArticle:
            "rounded-none border border-white/10 bg-[rgba(255,255,255,0.03)] p-5",
        tagsKicker: "text-xs uppercase tracking-[0.24em] text-slate-400",
        tagsRow: "mt-4 flex flex-wrap gap-2 text-xs text-slate-200",
        tagPill:
            "rounded-none border border-white/10 bg-black/30 px-3 py-1.5",
        principlesGrid: "grid gap-4 md:grid-cols-3",
        pillarCard:
            "rounded-none border border-white/10 bg-black/20 p-5",
        pillarIconWrap:
            "mb-4 flex h-11 w-11 items-center justify-center rounded-none bg-amber-400 text-black",
        pillarTitle: "text-base font-semibold text-slate-50",
        pillarText: "mt-2 text-sm text-slate-300",
        gridBottom:
            "grid gap-6 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]",
        moodArticle:
            "overflow-hidden rounded-none border border-amber-400/20 bg-[linear-gradient(180deg,rgba(251,191,36,0.08),rgba(255,255,255,0.03))] p-6",
        moodEyebrow: "text-xs uppercase tracking-[0.26em] text-amber-200",
        moodTitle:
            "mt-4 text-2xl font-semibold leading-tight text-slate-50",
        moodBody: "mt-3 text-sm leading-relaxed text-slate-300",
        stepsGridInner: "grid gap-3 sm:grid-cols-2",
        stepMiniCard:
            "rounded-none border border-white/10 bg-black/20 p-4",
        stepMiniKicker:
            "text-[11px] uppercase tracking-[0.22em] text-slate-400",
        stepMiniTitle: "mt-2 font-medium text-slate-50",
        stepMiniBody: "mt-1 text-sm text-slate-300",
    },

    delivery: {
        gridTop:
            "grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(280px,0.85fr)]",
        factsStack: "grid gap-4",
        highlightCard:
            "rounded-none border border-white/10 bg-[rgba(255,255,255,0.04)] p-5",
        highlightKicker:
            "text-[11px] uppercase tracking-[0.22em] text-slate-400",
        highlightValue: "mt-3 text-3xl font-semibold text-amber-300",
        highlightSub: "mt-1 text-sm text-slate-300",
        stepsGrid: "grid gap-4 md:grid-cols-4",
        stepCard:
            "rounded-none border border-white/10 bg-black/20 p-5",
        stepIconWrap:
            "mb-4 flex h-10 w-10 items-center justify-center rounded-none bg-amber-400 text-black",
        stepTitle: "font-medium text-slate-50",
        stepBody: "mt-2 text-sm text-slate-300",
        gridBottom:
            "grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]",
        paymentRow:
            "flex items-start gap-4 rounded-none border border-white/10 bg-black/20 p-4",
        paymentTitle: "font-medium text-slate-50",
        paymentBody: "mt-1 text-sm text-slate-300",
        importantArticle:
            "overflow-hidden rounded-none border border-amber-400/20 bg-[linear-gradient(180deg,rgba(251,191,36,0.1),rgba(255,255,255,0.03))] p-6",
        importantEyebrow: "text-xs uppercase tracking-[0.26em] text-amber-200",
        importantTitle:
            "mt-4 text-2xl font-semibold leading-tight text-slate-50",
        importantBody: "mt-3 text-sm leading-relaxed text-slate-300",
        chipsRow: "mt-5 flex flex-wrap gap-2 text-xs text-slate-200",
        chip:
            "rounded-none border border-white/10 bg-black/30 px-3 py-1.5",
    },

    contacts: {
        apiError:
            "mb-4 rounded-none border border-red-500/30 bg-red-950/30 px-4 py-3 text-sm text-red-200",
        channelsGrid: "grid gap-4 md:grid-cols-3",
        channelArticle:
            "rounded-none border border-white/10 bg-[rgba(255,255,255,0.04)] p-5 shadow-[0_16px_50px_rgba(0,0,0,0.35)]",
        channelIconWrap:
            "mb-4 flex h-11 w-11 items-center justify-center rounded-none bg-amber-400 text-black",
        channelLabel:
            "text-xs uppercase tracking-[0.22em] text-slate-400",
        channelLoading: "mt-2 text-sm text-slate-500",
        channelValueRow: "mt-2 text-lg font-semibold text-slate-50",
        channelLinkHover: "transition-colors hover:text-amber-300",
        channelMutedValue: "text-slate-500",
        channelSubMuted: "mt-1 text-sm text-slate-400",
        channelLead: "mt-2 text-sm text-slate-300",
        waLink:
            "text-amber-300/90 underline-offset-2 hover:underline",
        emailLink: "break-all transition-colors hover:text-amber-300",
        mainGrid:
            "grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(280px,0.85fr)]",
        addressLoading: "text-sm text-slate-500",
        addressLineSpaced: "mt-3",
        coverageBox:
            "mt-4 rounded-none border border-white/10 bg-black/20 p-4",
        coverageKicker:
            "text-[11px] uppercase tracking-[0.22em] text-slate-400",
        coverageBody: "mt-2 text-sm text-slate-200",
        siteLinkPara: "mt-4 text-sm",
        scheduleArticle:
            "overflow-hidden rounded-none border border-amber-400/20 bg-[linear-gradient(180deg,rgba(251,191,36,0.1),rgba(255,255,255,0.03))] p-6",
        scheduleEyebrow: "text-xs uppercase tracking-[0.26em] text-amber-200",
        scheduleTime: "mt-4 text-3xl font-semibold text-slate-50",
        scheduleNote: "mt-3 text-sm leading-relaxed text-slate-300",
        feeStack: "mt-5 space-y-2 text-sm text-slate-200",
        feeRow:
            "flex items-center justify-between rounded-none border border-white/10 bg-black/25 px-4 py-3",
        feeValue: "font-medium text-amber-200",
        tipsGrid: "grid gap-4 md:grid-cols-3",
        tipTile:
            "rounded-none border border-white/10 bg-black/20 p-5",
        tipKicker:
            "text-[11px] uppercase tracking-[0.22em] text-slate-400",
        tipTitle: "mt-2 font-medium text-slate-50",
        tipBody: "mt-1 text-sm text-slate-300",
    },
} as const;
