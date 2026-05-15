/**
 * Презентация чекаута: все шаги и общие паттерны полей/кнопок.
 */

import { dockDesign } from "../layout/dock.design";
import { secondarySurfaces, shellColorRoles, shellTypography } from "../layout/shell.design";

const nestedCard = secondarySurfaces.nestedCard;
const chromePillInactive = dockDesign.shared.chromePillInactive;

export const checkoutDesign = {
    shared: {
        stepKicker: `${shellTypography.body.checkoutKicker} ${shellColorRoles.muted}`,
        stepKickerAccent: `${shellTypography.body.checkoutKickerAccent} ${shellColorRoles.accent}`,
        flowBody: `${shellTypography.body.checkoutFlowBody} ${shellColorRoles.canvasFg}`,
        headingSm: `${shellTypography.body.checkoutHeadingSm} ${shellColorRoles.accent}`,
        headingCardMuted: "text-[11px] font-semibold text-app-muted",
        subsectionKickerSm: `${shellTypography.body.checkoutSubsectionKicker} ${shellColorRoles.muted}`,
        subsectionKickerXs:
            "pt-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-app-muted",
        subsectionKickerXsSpaced:
            "pt-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-app-muted",
        pillRound: "rounded-none px-3 py-1 transition",
        pillRoundText: "rounded-none px-3 py-1 text-[11px] transition",
        pillActive:
            "bg-app-accent text-black shadow-[0_0_14px_rgba(198,36,36,0.7)]",
        pillInactive:
            "bg-black/5 text-app-canvas-fg hover:bg-black/8",
        introMuted: "text-xs text-app-muted",
        authTabRow: "flex gap-2 text-[11px] font-medium",
        guestIsland: `${nestedCard} space-y-2 px-3 py-3`,
        grid2: "grid grid-cols-2 gap-2",
        addressEmptyHint: `${nestedCard} border-dashed border-neutral-500/60 px-4 py-3 text-[11px] text-app-muted`,
        addressLi: `flex items-center gap-2 ${nestedCard} px-3 py-2`,
        radioField:
            "h-4 w-4 rounded-none border-neutral-400 text-app-accent focus:ring-app-accent",
        checkboxSm:
            "h-3.5 w-3.5 rounded-none border-black/20 bg-neutral-950/85 text-app-accent focus:ring-app-accent/60",
        labelAddress: "flex-1 cursor-pointer text-xs text-app-canvas-fg",
        addressTitle: "block font-medium text-app-canvas-fg",
        addressMeta: "block text-[11px] text-app-muted",
        borderSectionTop:
            "space-y-2 border-t border-app-divider-on-canvas pt-3",
        expandRowBtn:
            "flex w-full items-center justify-between rounded-none bg-black/5 px-3 py-2 text-[11px] font-medium text-app-canvas-fg hover:bg-black/8",
        expandRowChevronMuted: "text-[11px] text-app-muted",
        newAddressWrap: "space-y-2 pt-1",
        inputFieldCol2:
            "col-span-2 rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2 text-[11px] text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        inputFieldFull:
            "w-full rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2 text-[11px] text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        inputFieldGridCell:
            "rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2 text-[11px] text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        textareaAddress:
            "w-full rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2 text-[11px] text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        textareaFlow:
            "w-full rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2 text-xs text-app-canvas-fg placeholder:text-app-muted outline-none focus:border-app-accent",
        checkboxLabelRow:
            "flex items-center gap-2 text-[11px] text-app-muted",
        saveSecondaryBtn:
            "inline-flex w-full items-center justify-center rounded-none bg-black/5 px-3 py-1.5 text-[11px] font-medium text-app-canvas-fg transition hover:bg-black/8 disabled:opacity-50",
        navFooterRow:
            "mt-2 flex items-center justify-between text-[11px] text-app-muted",
        linkUnderline: "underline-offset-2 hover:underline",
        btnPrimarySm:
            "inline-flex items-center justify-center rounded-none bg-app-accent px-3 py-1.5 text-[11px] font-semibold text-black shadow-[0_0_14px_rgba(198,36,36,0.7)] transition hover:bg-app-accent-hover",
        btnPrimarySmBusy:
            "inline-flex items-center justify-center rounded-none bg-app-accent px-3 py-1.5 text-[11px] font-semibold text-black shadow-[0_0_14px_rgba(198,36,36,0.7)] transition hover:bg-app-accent-hover disabled:opacity-60",
        btnPrimaryMd:
            "inline-flex items-center justify-center rounded-none bg-app-accent px-4 py-2 text-xs font-semibold text-black shadow-[0_0_18px_rgba(198,36,36,0.7)] transition hover:bg-app-accent-hover",
        btnSecondaryOutline:
            "inline-flex w-full items-center justify-center rounded-none border border-black/15 bg-black/5 px-4 py-2 text-xs font-medium text-app-canvas-fg transition hover:bg-black/8",
        btnSecondaryOutlineCompact:
            "w-full rounded-none border border-app-border-on-surface bg-black/5 px-3 py-2 text-center text-[11px] font-medium text-app-canvas-fg transition hover:bg-black/8",
        errorBanner:
            "rounded-none border border-red-500/40 bg-red-950/40 px-3 py-2 text-[11px] text-red-200",
        errorLine: "text-[11px] text-red-400",
        mutedNote: "text-[11px] text-app-muted",
        textBodyXs: "text-xs text-app-canvas-fg",
        textMutedLine: "text-[11px] text-app-muted",
        textSuccessLead: "text-sm font-semibold text-app-canvas-fg",
        textSuccessBody: "text-xs text-app-muted",
        monoAccent: "font-mono text-app-accent",
        spacerAfterComment: "space-y-1",
    },

    cart: {
        emptyState:
            "rounded-none bg-app-accent-soft-bg px-4 py-5 text-sm text-app-muted",
        userList: "space-y-2 text-xs sm:text-sm text-app-canvas-fg",
        qtyBar:
            "inline-flex items-center justify-between rounded-none border border-app-accent/60 bg-neutral-950/88 px-2 py-1 text-xs text-app-canvas-fg",
        qtyBtn:
            "flex h-6 w-6 items-center justify-center rounded-none bg-neutral-950/88 text-[14px]",
        qtyLabel: "px-2 font-semibold",
        userLineItem:
            "flex items-center justify-between gap-3 rounded-none bg-app-accent-soft-bg px-3 py-2",
        lineTitle: "truncate font-medium text-app-canvas-fg",
        lineSub: "mt-0.5 text-[11px] text-app-muted",
        lineActions: "flex items-center gap-3",
        removeLink:
            "shrink-0 text-[11px] text-app-muted transition-colors hover:text-red-400",
        systemList:
            "mt-2 rounded-none border border-app-accent/25 bg-app-accent/8 px-2.5 py-2 text-[11px] text-app-canvas-fg",
        systemLine:
            "mt-1 flex items-center justify-between gap-2 rounded-none px-1 py-0.5",
        systemLineName: "min-w-0 truncate text-app-canvas-fg",
        systemLineMeta: "shrink-0 text-app-muted",
        totalsCard:
            "mt-3 space-y-1 rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2 text-xs sm:text-sm",
        totalsRow: "flex items-center justify-between",
        totalsLabelMuted: "text-app-muted/85",
        totalsLabelStrong: "font-medium text-app-muted/90",
        totalsValue: "text-app-canvas-fg",
        totalsDivider:
            "flex items-center justify-between border-t border-app-border-on-surface pt-1",
        grandTotal: "font-semibold text-app-accent",
        giftCard:
            "mt-3 rounded-none border border-app-accent/25 bg-app-accent/10 px-3 py-2 text-xs sm:text-sm",
        giftRow: "flex items-center justify-between gap-2",
        giftTitle: "font-semibold text-app-accent",
        giftSelectedHint: "mt-0.5 truncate text-[11px] text-app-muted",
        giftCta: `shrink-0 ${chromePillInactive} px-3 py-1 text-[11px] font-medium text-app-accent`,
        authActions: "mt-3 flex flex-col gap-2",
        giftModalList: "space-y-2",
        giftRadioLabel:
            "flex cursor-pointer items-center gap-3 rounded-none border border-app-border-on-surface bg-black/5 px-3 py-2 transition hover:border-app-accent/40",
        giftRadioInput: "h-4 w-4 accent-app-accent",
        giftRadioBody: "min-w-0 flex-1",
        giftRadioTitle: "truncate text-sm font-medium text-app-canvas-fg",
        giftRadioPrice: "text-xs text-app-muted",
        giftThumb: "h-10 w-10 rounded-none object-cover",
        giftFooterRow: "flex justify-end",
        giftApplyBtn:
            "inline-flex items-center justify-center rounded-none bg-app-accent px-4 py-2 text-xs font-semibold text-black transition hover:bg-app-accent-hover disabled:cursor-not-allowed disabled:opacity-60",
    },

    auth: {
        footerCol: "mt-2 flex flex-col gap-2 text-[11px] text-app-muted",
    },

    delivery: {
        methodRow: "flex flex-wrap gap-2",
    },

    confirm: {
        summaryCard:
            "space-y-2 rounded-none bg-app-accent-soft-bg px-3 py-3",
        orderList: "space-y-1 text-xs",
        orderLineRow: "flex items-center justify-between gap-2",
        orderLineTruncate: "truncate text-app-canvas-fg",
        orderLineMuted: "shrink-0 text-app-muted",
        systemLineAccent:
            "flex items-center justify-between gap-2 rounded-none border border-app-accent/25 bg-app-accent/10 px-2 py-1",
        badgeTiny: "ml-1 text-[10px] font-medium text-app-accent",
        totalsInset:
            "mt-2 space-y-1 border-t border-app-divider-on-canvas pt-2 text-xs",
        blockMuted:
            "space-y-1 rounded-none bg-app-glass-fill px-3 py-3",
        mutedInline: "ml-1 text-app-muted",
    },

    success: {
        footerActions: "mt-2 flex justify-end",
    },
} as const;
