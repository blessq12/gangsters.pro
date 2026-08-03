/**
 * Профиль клиента: формы, адреса, история заказов, блок контактов.
 */

import { secondarySurfaces } from "../layout/shell.design";

const nestedCard = secondarySurfaces.nestedCard;
const profileDivider = "border-app-divider-on-canvas";
const profileStateEmpty = `${nestedCard} border-dashed border-neutral-500/60`;

export const clientDesign = {
    shared: {
        formRoot: "space-y-4 text-app-canvas-fg",
        fieldStack: "space-y-3",
        headingH3: "text-lg font-normal text-app-canvas-fg sm:text-xl",
        leadMuted: "text-xs text-app-muted",
        tabRow: "flex flex-wrap gap-2",
        tabPillBase:
            "rounded-none px-3 py-1 text-[11px] font-medium transition",
        tabPillActive: "bg-app-accent text-white ",
        tabPillInactive: "bg-black/5 text-app-canvas-fg hover:bg-black/8",
        label: "mb-1 block text-xs font-medium text-app-muted",
        input: "w-full rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2 text-sm text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        inputGrid11:
            "rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2 text-[11px] text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        inputCol2:
            "col-span-2 rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2 text-[11px] text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        textarea:
            "w-full rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2 text-[11px] text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        checkboxRow: "flex items-center gap-2 text-xs text-app-muted",
        checkboxRowMuted: "flex items-center gap-2 text-xs text-app-muted",
        checkboxRow11: "flex items-center gap-2 text-[11px] text-app-muted",
        errorXs: "text-xs text-red-400",
        error11: "text-[11px] text-red-400",
        btnPrimaryWide:
            "inline-flex w-full items-center justify-center rounded-none bg-app-accent px-4 py-2 text-sm font-semibold text-white transition hover:bg-app-accent-hover disabled:opacity-60",
        btnPrimaryCompact:
            "inline-flex w-full items-center justify-center rounded-none bg-app-accent px-3 py-1.5 text-[11px] font-semibold text-white transition hover:bg-app-accent-hover disabled:opacity-60",
        forgotSectionTop: `space-y-2 border-t ${profileDivider} pt-3`,
        forgotToggle:
            "text-[11px] text-app-accent/90 underline-offset-2 hover:text-app-accent hover:underline",
        forgotIsland: `${nestedCard} space-y-2 p-3`,
        forgotHint: "text-[11px] text-app-muted",
        forgotSubmitBtn:
            "inline-flex w-full items-center justify-center rounded-none border border-app-accent/50 bg-transparent px-3 py-1.5 text-xs font-medium text-app-accent hover:bg-app-accent/10 disabled:opacity-50",
        loginFooter: "flex flex-wrap items-center gap-x-2 gap-y-1",
        loginFooterLink:
            "text-[11px] text-app-accent/90 underline-offset-2 hover:text-app-accent hover:underline",
        loginFooterSep: "text-[11px] text-app-muted",
        addressGrid: "grid grid-cols-2 gap-2",
        addressDetailsGrid: "grid grid-cols-3 gap-2",
    },

    profileView: {
        root: "space-y-4 text-app-canvas-fg",
        welcome: "text-sm leading-relaxed text-app-canvas-fg",
        statsSection: "space-y-2",
        statLoading: `${nestedCard} px-3 py-4 text-center text-xs text-app-muted`,
        statError:
            "rounded-none border border-red-500/40 bg-red-950/40 px-3 py-3 text-[11px] text-red-200",
        statGrid: "grid grid-cols-2 gap-2",
        statCard: `${nestedCard} px-3 py-3`,
        statCardWide: `col-span-2 ${nestedCard} px-3 py-3`,
        statLabel:
            "text-[10px] font-medium uppercase tracking-wide text-app-muted",
        statValueAccent:
            "mt-1 text-xl font-semibold tabular-nums text-app-accent",
        statValueMain:
            "mt-1 text-lg font-semibold tabular-nums text-app-canvas-fg",
        offersBlock: `${profileStateEmpty} space-y-1.5 px-3 py-4`,
        offersTitle: "text-xs font-medium text-app-muted",
        offersHint: "text-[11px] leading-snug text-app-muted/80",
        btnLogout:
            "inline-flex w-full items-center justify-center rounded-none border border-red-600 bg-red-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-600 hover:text-white",
    },

    orderHistory: {
        root: "space-y-3 text-app-canvas-fg",
        stateLoading: `${nestedCard} px-4 py-6 text-center text-sm text-app-muted`,
        stateError:
            "rounded-none border border-red-500/40 bg-red-950/30 px-4 py-3 text-sm text-red-200",
        stateEmpty: `${profileStateEmpty} px-4 py-6 text-center text-sm text-app-muted`,
        list: "max-h-[min(28rem,55vh)] space-y-2 overflow-y-auto pr-1",
        card: nestedCard,
        cardHeadBtn:
            "flex w-full items-start justify-between gap-3 px-3 py-3 text-left transition hover:bg-black/8 sm:px-4",
        cardHeadMain: "min-w-0 flex-1",
        cardHeadAside: "shrink-0 text-right",
        cardBody: `border-t ${profileDivider} px-3 pb-3 pt-2 sm:px-4`,
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
        paymentFoot: `mt-3 border-t ${profileDivider} pt-2 text-[11px] text-app-muted`,
        moreHint: "text-center text-[11px] text-app-muted",
        repeatBtn:
            "mt-3 inline-flex w-full items-center justify-center rounded-none bg-app-accent px-3 py-2 text-[11px] font-semibold text-white transition hover:bg-app-accent-hover disabled:opacity-60",
        promoBadge: "text-[10px] text-app-muted",
    },

    repeatOrderModal: {
        lead: "text-sm text-app-canvas-fg",
        actions: "flex flex-col gap-2",
        replaceBtn:
            "inline-flex w-full items-center justify-center rounded-none border border-app-accent/50 bg-transparent px-3 py-1.5 text-xs font-medium text-app-accent hover:bg-app-accent/10 disabled:opacity-50",
        cancelBtn:
            "inline-flex w-full items-center justify-center rounded-none bg-black/5 px-3 py-1.5 text-[11px] font-medium text-app-muted transition hover:bg-black/8 hover:text-app-canvas-fg disabled:opacity-50",
    },

    addresses: {
        root: "space-y-3 text-app-canvas-fg",
        listStack: "space-y-2 text-xs",
        card: `${nestedCard} px-3 py-2`,
        cardRow: "flex items-center justify-between gap-2",
        titleStrong: "font-medium text-app-canvas-fg",
        metaLine: "mt-1 text-app-muted",
        actionsCol: "flex flex-col items-end gap-1",
        btnSelect:
            "rounded-none bg-black/5 px-2 py-0.5 text-[10px] text-app-canvas-fg hover:bg-black/8",
        linkRemove: "text-[10px] text-app-muted hover:text-red-400",
        commentLine: "mt-1 text-[11px] text-app-muted",
        emptyHint: "text-xs text-app-muted",
        addSection: `mt-3 border-t ${profileDivider} pt-2 text-xs`,
        expandBtn:
            "flex w-full items-center justify-between rounded-none bg-black/5 px-3 py-2 text-[11px] font-medium text-app-canvas-fg hover:bg-black/8",
        expandChevron: "text-[11px] text-app-muted",
        addForm: "mt-3 space-y-2 text-xs",
    },
} as const;
