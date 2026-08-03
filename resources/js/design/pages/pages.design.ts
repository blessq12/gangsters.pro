/**
 * Презентация страниц-роутов (Vue Router).
 */

import { secondarySurfaces } from "../layout/shell.design";

const gradientIsland = secondarySurfaces.gradientIsland;
const nestedCard = secondarySurfaces.nestedCard;
const nestedFg = secondarySurfaces.onNestedFg;

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

    notFound: {
        pageWrap:
            "mx-auto flex min-h-[50vh] max-w-lg flex-col items-center justify-center py-16 text-center text-app-canvas-fg",
        code: "font-heading text-7xl font-normal text-app-accent sm:text-8xl",
        title: "mt-4 text-xl font-normal text-app-canvas-fg sm:text-2xl",
        lead: "mt-3 text-sm text-app-muted",
        actions: "mt-8 flex flex-wrap items-center justify-center gap-3",
        primaryBtn:
            "inline-flex items-center justify-center rounded-none bg-app-accent px-5 py-2.5 text-sm font-semibold text-black transition hover:bg-app-accent-hover",
        secondaryBtn:
            "inline-flex items-center justify-center rounded-none border border-app-border-on-surface bg-app-glass-fill px-5 py-2.5 text-sm font-medium text-app-canvas-fg backdrop-blur-md transition hover:border-app-accent/40 hover:text-app-accent",
    },

    resetPassword: {
        pageWrap: "mx-auto max-w-md py-8 text-app-canvas-fg",
        title: "mb-1 text-xl font-normal text-app-accent sm:text-2xl",
        lead: "mb-6 text-xs text-app-muted",
        form:
            "space-y-4 rounded-none border border-app-accent/20 bg-app-glass-fill px-4 py-5 backdrop-blur-md",
        label: "mb-1 block text-xs font-medium text-app-muted",
        input:
            "w-full rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2 text-sm text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        error: "text-xs text-red-400",
        submitBtn:
            "inline-flex w-full items-center justify-center rounded-none bg-app-accent px-4 py-2 text-sm font-semibold text-black transition hover:bg-app-accent-hover disabled:opacity-60",
        noTokenCard:
            `rounded-none border border-red-500/30 bg-app-glass-fill px-4 py-5 text-sm backdrop-blur-md ${nestedFg}`,
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
            "inline-flex rounded-none border border-app-accent/30 bg-app-accent-soft-bg px-3 py-1 text-[11px] uppercase tracking-[0.24em] text-app-accent",
        spotlightTitle:
            "font-heading text-xl font-normal text-app-accent sm:text-2xl",
        spotlightBody: "mt-2 text-sm leading-relaxed text-app-muted",
        tagsArticle:
            "rounded-none border border-app-border-on-surface bg-app-accent-soft-bg p-5",
        tagsKicker: "text-xs uppercase tracking-[0.24em] text-app-muted",
        tagsRow: "mt-4 flex flex-wrap gap-2",
        tagPill: `${nestedCard} px-3 py-1.5 text-xs ${nestedFg}`,
        principlesGrid: "grid gap-4 md:grid-cols-3",
        pillarCard: `${nestedCard} p-5`,
        pillarIconWrap:
            "mb-4 flex h-11 w-11 items-center justify-center rounded-none bg-app-accent text-black",
        pillarTitle: `text-lg font-normal ${nestedFg} sm:text-xl`,
        pillarText: "mt-2 text-sm text-app-muted",
        gridBottom:
            "grid gap-6 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]",
        moodArticle: `${gradientIsland} p-6`,
        moodEyebrow: "text-xs uppercase tracking-[0.26em] text-app-accent",
        moodTitle:
            "font-heading mt-4 text-3xl font-normal leading-tight text-app-accent sm:text-4xl",
        moodBody: "mt-3 text-sm leading-relaxed text-app-muted",
        stepsGridInner: "grid gap-3 sm:grid-cols-2",
        stepMiniCard: `${nestedCard} p-4`,
        stepMiniKicker:
            "text-[11px] uppercase tracking-[0.22em] text-app-muted",
        stepMiniTitle: `mt-2 font-medium ${nestedFg}`,
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
        stepCard: `${nestedCard} p-5`,
        stepIconWrap:
            "mb-4 flex h-10 w-10 items-center justify-center rounded-none bg-app-accent text-black",
        stepTitle: `font-medium ${nestedFg}`,
        stepBody: "mt-2 text-sm text-app-muted",
        gridBottom:
            "grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]",
        paymentRow: `flex items-start gap-4 ${nestedCard} p-4`,
        paymentTitle: `font-medium ${nestedFg}`,
        paymentBody: "mt-1 text-sm text-app-muted",
        importantArticle: `${gradientIsland} p-6`,
        importantEyebrow: "text-xs uppercase tracking-[0.26em] text-app-accent",
        importantTitle:
            "font-heading mt-4 text-3xl font-normal leading-tight text-app-accent sm:text-4xl",
        importantBody: "mt-3 text-sm leading-relaxed text-app-muted",
        chipsRow: "mt-5 flex flex-wrap gap-2",
        chip: `${nestedCard} px-3 py-1.5 text-xs ${nestedFg}`,
        zoneMapStage:
            "relative min-h-[22rem] w-full overflow-hidden rounded-none border border-app-border-on-surface sm:min-h-[26rem]",
        zoneMapLayer: "absolute inset-0 z-0",
        zoneMapCanvas: "h-full min-h-[22rem] w-full border-0 sm:min-h-[26rem]",
        zoneMapFallback:
            "grid min-h-[22rem] place-content-center bg-app-glass-fill px-4 py-8 text-center text-sm text-app-muted sm:min-h-[26rem]",
        zoneMapFallbackProse: "mx-auto max-w-prose leading-relaxed",
        zoneMapLoading:
            "grid min-h-[22rem] place-content-center bg-app-glass-fill text-sm text-app-muted sm:min-h-[26rem]",
        zoneMapHint: "mt-3 text-xs text-app-muted",
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
            "grid gap-6 lg:grid-cols-2",
        addressLoading: "text-sm text-app-muted",
        addressLineSpaced: "mt-3",
        coverageBox: `mt-4 ${nestedCard} p-4`,
        coverageKicker:
            "text-[11px] uppercase tracking-[0.22em] text-app-muted",
        coverageBody: `mt-2 text-sm ${nestedFg}`,
        siteLinkPara: "mt-4 text-sm",
        kitchenMapStage:
            "relative mt-4 min-h-[16rem] w-full overflow-hidden rounded-none border border-app-border-on-surface sm:min-h-[18rem]",
        kitchenMapLayer: "absolute inset-0 z-0",
        kitchenMapCanvas: "h-full min-h-[16rem] w-full border-0 sm:min-h-[18rem]",
        kitchenMapFallback:
            "grid min-h-[16rem] place-content-center bg-app-glass-fill px-4 py-8 text-center text-sm text-app-muted sm:min-h-[18rem]",
        kitchenMapFallbackProse: "mx-auto max-w-prose leading-relaxed",
        kitchenMapLoading:
            "grid min-h-[16rem] place-content-center bg-app-glass-fill text-sm text-app-muted sm:min-h-[18rem]",
        kitchenMapHint: "mt-3 text-xs text-app-muted",
        scheduleList: "divide-y divide-app-border-on-surface",
        scheduleRow:
            "flex items-baseline justify-between gap-4 py-2.5 first:pt-0 last:pb-0",
        scheduleDay: "w-8 shrink-0 text-xs font-medium text-app-muted tabular-nums",
        scheduleDayToday:
            "w-8 shrink-0 text-xs font-medium text-app-accent tabular-nums",
        scheduleWork:
            "min-w-0 text-right tabular-nums text-app-canvas-fg",
        scheduleWorkToday:
            "min-w-0 text-right font-medium tabular-nums text-app-canvas-fg",
        scheduleFallback: "text-sm leading-relaxed text-app-muted",
        scheduleLoading: "text-sm text-app-muted",
        scheduleEmpty: "text-sm leading-relaxed text-app-muted",
        tipsGrid: "grid gap-4 md:grid-cols-3",
        tipTile: `${nestedCard} p-5`,
        tipKicker:
            "text-[11px] uppercase tracking-[0.22em] text-app-muted",
        tipTitle: `mt-2 font-medium ${nestedFg}`,
        tipBody: "mt-1 text-sm text-app-muted",
    },
} as const;
