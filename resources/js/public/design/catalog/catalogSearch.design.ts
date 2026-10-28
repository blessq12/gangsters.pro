/** Fullscreen overlay поиска по каталогу. */

export const catalogSearchDesign = {
    overlay:
        "fixed inset-0 z-[9998] flex flex-col bg-app-canvas text-app-canvas-fg will-change-transform",
    header:
        "shrink-0 border-b border-app-border-on-surface bg-app-canvas/95 px-4 py-3 backdrop-blur-md transition-[box-shadow] sm:px-6",
    headerFocused: "shadow-[inset_0_-2px_0_0_var(--app-accent)]",
    headerRow: "mx-auto flex w-full max-w-7xl items-center gap-3",
    headerTitle:
        "hidden shrink-0 font-heading text-sm font-normal uppercase tracking-[0.18em] text-app-accent sm:block",
    closeBtn:
        "inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-none border border-app-border-on-surface bg-black/5 text-app-canvas-fg transition hover:bg-black/8 active:scale-[0.98]",
    searchWrap: "relative min-w-0 flex-1",
    searchIcon:
        "pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-lg text-app-muted",
    searchInput:
        "w-full rounded-none border border-app-border-on-surface bg-app-glass-fill py-2.5 pl-10 pr-10 text-sm text-app-canvas-fg placeholder:text-app-muted transition focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
    clearBtn:
        "absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-none text-app-muted transition hover:bg-black/8 hover:text-app-canvas-fg active:rotate-90",
    body: "min-h-0 flex-1 overflow-y-auto px-4 py-5 sm:px-6",
    bodyInner: "mx-auto w-full max-w-7xl",
    panelHero:
        "relative mx-auto max-w-lg overflow-hidden py-10 text-center sm:py-16",
    panelGlow: "hidden",
    panelContent: "relative z-[1]",
    kicker: "font-heading text-3xl font-normal text-app-accent sm:text-4xl",
    title: "mt-3 text-xl font-normal text-app-canvas-fg sm:text-2xl",
    lead: "mt-3 text-sm leading-relaxed text-app-muted",
    sectionLabel:
        "mt-8 text-[11px] font-medium uppercase tracking-[0.22em] text-app-muted",
    chips: "mt-3 flex flex-wrap items-center justify-center gap-2",
    chip:
        "rounded-none border border-app-border-on-surface bg-black/5 px-3 py-1.5 text-xs text-app-canvas-fg transition hover:border-app-accent/40 hover:text-app-accent active:scale-[0.98]",
    chipHistory:
        "inline-flex max-w-full items-center gap-1.5 rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-1.5 text-xs text-app-canvas-fg backdrop-blur-sm transition hover:border-app-accent/40 hover:text-app-accent active:scale-[0.98]",
    chipHistoryIcon: "mdi mdi-history text-sm text-app-muted",
    chipHistoryText: "truncate",
    resultsMeta: "mb-4 text-xs tabular-nums text-app-muted",
    loadingWrap: "py-16 text-center text-sm text-app-muted",
    idleRoot: "space-y-3",
    idleIntro: "space-y-1",
    idleTitle: "font-heading text-lg font-normal text-app-accent",
    idleLead: "text-sm text-app-muted",
    idleSectionLabel:
        "mt-3 text-[11px] font-medium uppercase tracking-[0.22em] text-app-muted",
    idleChips: "mt-2 flex flex-wrap items-center gap-2",
    discoverSection: "mt-5 border-t border-app-border-on-surface pt-5",
    discoverTitle: "mb-4 text-[11px] font-medium uppercase tracking-[0.22em] text-app-muted",
    discoverSentinel: "h-px w-full",
    triggerAffordance:
        "pointer-events-none absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center text-app-muted",
    triggerWrap:
        "relative cursor-pointer transition-transform active:scale-[0.99]",
} as const;
