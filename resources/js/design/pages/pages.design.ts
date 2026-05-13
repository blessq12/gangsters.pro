/**
 * Презентация страниц-роутов (Vue Router).
 */

const searchInputClasses =
    "w-full rounded-none border-0 bg-app-glass-fill py-2.5 pl-10 pr-10 text-sm text-app-canvas-fg placeholder:text-app-muted focus:outline-none focus:ring-1 focus:ring-app-accent/50";

export const pagesDesign = {
    home: {
        desktop: {
            root: "home-page mt-4 space-y-10",
            menuHeader: "mb-4 flex items-end justify-between gap-4",
            menuTitleBlock: "block",
            menuTitle:
                "text-2xl font-normal leading-tight text-app-accent sm:text-3xl lg:text-4xl",
            menuSubtitle: "text-sm text-app-muted",
            searchCol: "w-full max-w-xs shrink-0",
            srOnlyLabel: "sr-only",
            searchWrap: "relative",
            searchIconPos:
                "pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-lg text-app-muted",
            searchInput: searchInputClasses,
            searchClear:
                "absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-none text-app-muted transition hover:bg-black/8 hover:text-app-canvas-fg",
            catalogControlsRow: "-mt-6 mb-4 flex justify-end",
        },
        mobile: {
            root: "home-page mt-4 space-y-8",
            menuHeader: "mb-4 flex flex-col gap-3",
            menuTitle:
                "text-2xl font-normal text-app-accent sm:text-3xl lg:text-4xl",
            menuSubtitle: "text-sm text-app-muted",
            srOnlyLabel: "sr-only",
            searchWrapOuter: "w-full",
            searchWrap: "relative",
            searchIconPos:
                "pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-lg text-app-muted",
            searchInput: searchInputClasses,
            searchClear:
                "absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-none text-app-muted transition hover:bg-black/8 hover:text-app-canvas-fg",
            viewControls: "mb-4",
        },
    },

    resetPassword: {
        pageWrap: "mx-auto max-w-md py-8 text-app-canvas-fg",
        title: "mb-1 text-xl font-normal text-app-accent sm:text-2xl",
        lead: "mb-6 text-xs text-app-muted",
        form:
            "space-y-4 rounded-none border border-app-accent/20 bg-[rgba(0,0,0,0.35)] px-4 py-5",
        label: "mb-1 block text-xs font-medium text-app-muted",
        input:
            "w-full rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2 text-sm text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        error: "text-xs text-red-400",
        submitBtn:
            "inline-flex w-full items-center justify-center rounded-none bg-app-accent px-4 py-2 text-sm font-semibold text-black shadow-[0_0_18px_rgba(198,36,36,0.75)] transition hover:bg-app-accent-hover disabled:opacity-60",
        noTokenCard:
            "rounded-none border border-red-500/30 bg-app-surface px-4 py-5 text-sm text-neutral-700",
        noTokenLead: "mb-3",
        homeLink:
            "text-sm font-medium text-app-accent hover:text-app-accent",
    },

    about: {
        gridTop:
            "grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(280px,0.8fr)]",
        asideStack: "grid gap-4",
        spotlightArticle:
            "overflow-hidden rounded-none border border-app-border-on-surface bg-app-glass-fill p-5 shadow-[0_16px_50px_rgba(0,0,0,0.35)]",
        spotlightHeader: "mb-4 flex items-center justify-between",
        eyebrowTag:
            "inline-flex rounded-none border border-app-accent/30 bg-app-accent/10 px-3 py-1 text-[11px] uppercase tracking-[0.24em] text-app-accent",
        spotlightTitle:
            "font-heading text-xl font-normal text-app-accent sm:text-2xl",
        spotlightBody: "mt-2 text-sm leading-relaxed text-app-muted",
        tagsArticle:
            "rounded-none border border-app-border-on-surface bg-app-accent-soft-bg p-5",
        tagsKicker: "text-xs uppercase tracking-[0.24em] text-app-muted",
        tagsRow: "mt-4 flex flex-wrap gap-2 text-xs text-app-canvas-fg",
        tagPill:
            "rounded-none border border-app-border-on-surface bg-app-surface px-3 py-1.5",
        principlesGrid: "grid gap-4 md:grid-cols-3",
        pillarCard:
            "rounded-none border border-app-border-on-surface bg-app-surface p-5",
        pillarIconWrap:
            "mb-4 flex h-11 w-11 items-center justify-center rounded-none bg-app-accent text-black",
        pillarTitle: "text-lg font-normal text-app-surface-fg sm:text-xl",
        pillarText: "mt-2 text-sm text-app-muted",
        gridBottom:
            "grid gap-6 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]",
        moodArticle:
            "overflow-hidden rounded-none border border-app-accent/20 bg-[linear-gradient(180deg,rgba(198,36,36,0.08),rgba(0,0,0,0.04))] p-6",
        moodEyebrow: "text-xs uppercase tracking-[0.26em] text-app-accent",
        moodTitle:
            "font-heading mt-4 text-3xl font-normal leading-tight text-app-accent sm:text-4xl",
        moodBody: "mt-3 text-sm leading-relaxed text-app-muted",
        stepsGridInner: "grid gap-3 sm:grid-cols-2",
        stepMiniCard:
            "rounded-none border border-app-border-on-surface bg-app-surface p-4",
        stepMiniKicker:
            "text-[11px] uppercase tracking-[0.22em] text-app-muted",
        stepMiniTitle: "mt-2 font-medium text-app-surface-fg",
        stepMiniBody: "mt-1 text-sm text-app-muted",
    },

    delivery: {
        gridTop:
            "grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(280px,0.85fr)]",
        factsStack: "grid gap-4",
        highlightCard:
            "rounded-none border border-app-border-on-surface bg-app-glass-fill p-5",
        highlightKicker:
            "text-[11px] uppercase tracking-[0.22em] text-app-muted",
        highlightValue:
            "font-heading mt-3 text-4xl font-normal text-app-accent sm:text-5xl",
        highlightSub: "mt-1 text-sm text-app-muted",
        stepsGrid: "grid gap-4 md:grid-cols-4",
        stepCard:
            "rounded-none border border-app-border-on-surface bg-app-surface p-5",
        stepIconWrap:
            "mb-4 flex h-10 w-10 items-center justify-center rounded-none bg-app-accent text-black",
        stepTitle: "font-medium text-app-surface-fg",
        stepBody: "mt-2 text-sm text-app-muted",
        gridBottom:
            "grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]",
        paymentRow:
            "flex items-start gap-4 rounded-none border border-app-border-on-surface bg-app-surface p-4",
        paymentTitle: "font-medium text-app-surface-fg",
        paymentBody: "mt-1 text-sm text-app-muted",
        importantArticle:
            "overflow-hidden rounded-none border border-app-accent/20 bg-[linear-gradient(180deg,rgba(198,36,36,0.1),rgba(0,0,0,0.04))] p-6",
        importantEyebrow: "text-xs uppercase tracking-[0.26em] text-app-accent",
        importantTitle:
            "font-heading mt-4 text-3xl font-normal leading-tight text-app-accent sm:text-4xl",
        importantBody: "mt-3 text-sm leading-relaxed text-app-muted",
        chipsRow: "mt-5 flex flex-wrap gap-2 text-xs text-app-canvas-fg",
        chip:
            "rounded-none border border-app-border-on-surface bg-app-surface px-3 py-1.5",
    },

    contacts: {
        apiError:
            "mb-4 rounded-none border border-red-500/30 bg-red-950/30 px-4 py-3 text-sm text-red-200",
        channelsGrid: "grid gap-4 md:grid-cols-3",
        channelArticle:
            "rounded-none border border-app-border-on-surface bg-app-glass-fill p-5 shadow-[0_16px_50px_rgba(0,0,0,0.35)]",
        channelIconWrap:
            "mb-4 flex h-11 w-11 items-center justify-center rounded-none bg-app-accent text-black",
        channelLabel:
            "text-xs uppercase tracking-[0.22em] text-app-muted",
        channelLoading: "mt-2 text-sm text-app-muted",
        channelValueRow:
            "font-heading mt-2 text-xl font-normal text-app-accent sm:text-2xl",
        channelLinkHover: "transition-colors hover:text-app-accent",
        channelMutedValue: "text-app-muted",
        channelSubMuted: "mt-1 text-sm text-app-muted",
        channelLead: "mt-2 text-sm text-app-muted",
        waLink:
            "text-app-accent/90 underline-offset-2 hover:underline",
        emailLink: "break-all transition-colors hover:text-app-accent",
        mainGrid:
            "grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(280px,0.85fr)]",
        addressLoading: "text-sm text-app-muted",
        addressLineSpaced: "mt-3",
        coverageBox:
            "mt-4 rounded-none border border-app-border-on-surface bg-app-surface p-4",
        coverageKicker:
            "text-[11px] uppercase tracking-[0.22em] text-app-muted",
        coverageBody: "mt-2 text-sm text-app-surface-fg",
        siteLinkPara: "mt-4 text-sm",
        scheduleArticle:
            "overflow-hidden rounded-none border border-app-accent/20 bg-[linear-gradient(180deg,rgba(198,36,36,0.1),rgba(0,0,0,0.04))] p-6",
        scheduleEyebrow: "text-xs uppercase tracking-[0.26em] text-app-accent",
        scheduleTime:
            "font-heading mt-4 text-4xl font-normal text-app-accent sm:text-5xl",
        scheduleNote: "mt-3 text-sm leading-relaxed text-app-muted",
        feeStack: "mt-5 space-y-2 text-sm text-app-canvas-fg",
        feeRow:
            "flex items-center justify-between rounded-none border border-app-border-on-surface bg-app-surface px-4 py-3 text-app-surface-fg",
        feeValue: "font-medium text-app-accent",
        tipsGrid: "grid gap-4 md:grid-cols-3",
        tipTile:
            "rounded-none border border-app-border-on-surface bg-app-surface p-5",
        tipKicker:
            "text-[11px] uppercase tracking-[0.22em] text-app-muted",
        tipTitle: "mt-2 font-medium text-app-surface-fg",
        tipBody: "mt-1 text-sm text-app-muted",
    },
} as const;
