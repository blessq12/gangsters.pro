/**
 * Презентация чекаута: все шаги и общие паттерны полей/кнопок.
 */

import { dockDesign } from "../layout/dock.design";
import {
    secondarySurfaces,
    shellColorRoles,
    shellTypography,
} from "../layout/shell.design";
const nestedCard = secondarySurfaces.nestedCard;
const chromePillInactive = dockDesign.shared.chromePillInactive;

export const checkoutDesign = {
    shared: {
        stepKicker: `${shellTypography.body.checkoutKicker} ${shellColorRoles.muted}`,
        stepKickerAccent: `${shellTypography.body.checkoutKickerAccent} ${shellColorRoles.accent}`,
        flowBody: `${shellTypography.body.checkoutFlowBody} ${shellColorRoles.canvasFg}`,
        headingSm: `${shellTypography.body.checkoutHeadingSm} ${shellColorRoles.accent}`,
        /** Заголовки ключевых секций визарда — акцент, не muted. */
        headingCardMuted:
            "text-xs font-bold uppercase tracking-[0.12em] text-app-accent",
        subsectionKickerSm: `${shellTypography.body.checkoutSubsectionKicker} ${shellColorRoles.muted}`,
        subsectionKickerXs:
            "pt-0.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-app-muted",
        subsectionKickerXsSpaced:
            "pt-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-app-muted",
        pillRound: "rounded-none px-3 py-1 transition",
        pillRoundText: "rounded-none px-3 py-1 text-xs transition",
        pillActive: "bg-app-accent text-white",
        pillInactive: "bg-black/5 text-app-canvas-fg hover:bg-black/8",
        introMuted: "text-sm text-app-muted",
        stepHint: "text-sm leading-snug text-app-muted",
        storyHeader: "space-y-2 border-b border-app-accent/25 pb-3",
        storyProgress:
            "text-xs font-bold uppercase tracking-[0.14em] text-app-accent",
        storyWaiterLine:
            "text-base font-medium leading-snug text-app-canvas-fg sm:text-lg",
        offerCard:
            "flex items-center gap-3 rounded-none border border-app-accent/20 bg-app-accent/8 px-3 py-2.5",
        offerCardCompact:
            "flex items-center justify-between gap-2 rounded-none border border-app-accent/30 bg-app-accent/10 px-3 py-3",
        offerCardGrid: "grid grid-cols-1 gap-2 sm:grid-cols-2",
        offerCardTitle: "truncate text-sm font-semibold text-app-canvas-fg",
        offerCardMeta: "mt-0.5 text-xs text-app-muted",
        offerCardBody: "min-w-0 flex-1 space-y-1",
        offerCardTitleRow: "flex min-w-0 items-center gap-2",
        offerFreeBadge:
            "inline-flex shrink-0 items-center border border-emerald-400/50 bg-emerald-500/20 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-[0.12em] text-emerald-200",
        offerPaidBadge:
            "inline-flex shrink-0 items-center border border-amber-400/55 bg-amber-400/20 px-1.5 py-0.5 text-[10px] font-bold tabular-nums tracking-wide text-amber-200",
        offerPaidMeta: "text-xs font-medium text-app-muted",
        offerSectionHint: "text-sm leading-snug text-app-muted",
        teaserCard:
            "rounded-none border border-app-accent/30 bg-app-accent/10 px-3 py-2.5 text-sm leading-snug text-app-canvas-fg",
        teaserCardAccent: "font-medium text-app-accent",
        registerPitchCard:
            "space-y-3 rounded-none border border-app-accent/30 bg-app-accent/8 px-3.5 py-3.5",
        registerPitchEyebrow:
            "text-[11px] font-bold uppercase tracking-[0.14em] text-app-accent",
        registerPitchTitle:
            "text-base font-semibold leading-snug text-app-canvas-fg",
        registerPitchList:
            "space-y-1.5 text-sm leading-snug text-app-muted",
        registerPitchListItem: "flex gap-2",
        registerPitchListMark: "mt-0.5 shrink-0 text-app-accent",
        registerPitchBtn:
            "inline-flex w-full items-center justify-center rounded-none bg-app-accent px-4 py-3 text-sm font-bold text-white transition hover:bg-app-accent-hover",
        sectionInset: `${nestedCard} space-y-2 px-3 py-3`,
        /** Светлая панель формы адреса (курьер) — без тёмного glass-island. */
        sectionForm:
            "space-y-2.5 rounded-none border border-app-accent/25 bg-white/[0.07] px-3.5 py-3.5",
        fieldLabel: "text-sm font-medium text-app-canvas-fg",
        guestIsland: `${nestedCard} space-y-2 px-3 py-3`,
        grid2: "grid grid-cols-1 gap-2 md:grid-cols-2",
        grid3: "grid grid-cols-3 gap-2",
        addressEmptyHint: `${nestedCard} border-dashed border-neutral-500/60 px-4 py-3 text-sm text-app-muted`,
        addressLi: `flex items-center gap-2 ${nestedCard} px-3 py-2`,
        labelAddress: "flex-1 cursor-pointer text-sm text-app-canvas-fg",
        addressTitle: "block font-medium text-app-canvas-fg",
        addressMeta: "block text-xs text-app-muted",
        borderSectionTop:
            "space-y-2 border-t border-app-divider-on-canvas pt-3",
        expandRowBtn:
            "flex w-full items-center justify-between rounded-none bg-black/5 px-3 py-2.5 text-sm font-medium text-app-canvas-fg hover:bg-black/8",
        expandRowChevronMuted: "text-xs text-app-muted",
        newAddressWrap: "space-y-2 pt-1",
        inputFieldCol2:
            "col-span-2 w-full rounded-none border border-app-border-on-surface bg-white/[0.1] px-3 py-3 text-sm text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        inputFieldFull:
            "w-full rounded-none border border-app-border-on-surface bg-white/[0.1] px-3 py-3 text-sm text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        inputFieldGridCell:
            "w-full min-w-0 rounded-none border border-app-border-on-surface bg-white/[0.1] px-3 py-3 text-sm text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        textareaAddress:
            "w-full rounded-none border border-app-border-on-surface bg-white/[0.1] px-3 py-3 text-sm text-app-canvas-fg placeholder:text-app-muted focus:border-app-accent focus:outline-none focus:ring-1 focus:ring-app-accent/60",
        textareaFlow:
            "w-full rounded-none border border-app-border-on-surface bg-white/[0.1] px-3 py-3 text-sm text-app-canvas-fg placeholder:text-app-muted outline-none focus:border-app-accent",
        checkboxLabelRow: "flex items-center gap-2 text-sm text-app-muted",
        saveSecondaryBtn:
            "inline-flex w-full items-center justify-center rounded-none bg-black/5 px-3 py-2 text-sm font-medium text-app-canvas-fg transition hover:bg-black/8 disabled:opacity-50",
        navFooterRow:
            "mt-2 flex items-center justify-between text-xs text-app-muted",
        linkUnderline:
            "text-sm underline-offset-2 hover:underline text-app-accent hover:text-app-accent",
        btnPrimarySm:
            "inline-flex items-center justify-center rounded-none bg-app-accent px-3 py-2 text-sm font-semibold text-white transition hover:bg-app-accent-hover",
        btnPrimarySmBusy:
            "inline-flex items-center justify-center rounded-none bg-app-accent px-3 py-2 text-sm font-semibold text-white transition hover:bg-app-accent-hover disabled:opacity-60",
        /** Единая primary-кнопка навигации визарда (nav footer, success). */
        btnPrimaryNav:
            "inline-flex items-center justify-center rounded-none bg-app-accent px-4 py-2.5 text-sm font-bold text-white transition hover:bg-app-accent-hover disabled:opacity-60",
        btnPrimaryNavBusy:
            "inline-flex items-center justify-center rounded-none bg-app-accent px-4 py-2.5 text-sm font-bold text-white transition hover:bg-app-accent-hover disabled:opacity-60",
        btnPrimaryMd:
            "inline-flex items-center justify-center rounded-none bg-app-accent px-4 py-2.5 text-sm font-bold text-white transition hover:bg-app-accent-hover",
        btnAuthSecondary:
            "inline-flex w-full items-center justify-center rounded-none border border-black/15 bg-black/5 px-4 py-2.5 text-sm font-medium text-app-canvas-fg transition hover:bg-black/8",
        btnSecondaryOutline:
            "inline-flex w-full items-center justify-center rounded-none border border-black/15 bg-black/5 px-4 py-2.5 text-sm font-medium text-app-canvas-fg transition hover:bg-black/8",
        btnSecondaryOutlineCompact:
            "w-full rounded-none border border-app-border-on-surface bg-black/5 px-3 py-2.5 text-center text-sm font-medium text-app-canvas-fg transition hover:bg-black/8",
        errorBanner:
            "rounded-none border border-red-500/40 bg-red-950/40 px-3 py-2.5 text-sm text-red-200",
        errorLine: "text-sm text-red-400",
        mutedNote: "text-sm text-app-muted",
        textBodyXs: "text-sm text-app-canvas-fg",
        textMutedLine: "text-sm text-app-muted",
        textSuccessLead: "text-base font-semibold text-app-canvas-fg",
        textSuccessBody: "text-sm text-app-muted",
        monoAccent: "font-mono text-app-accent",
        spacerAfterComment: "space-y-1",
    },

    cart: {
        emptyState:
            "rounded-none bg-app-accent-soft-bg px-4 py-5 text-base text-app-muted",
        userList: "space-y-2 text-sm text-app-canvas-fg",
        qtyBar: "inline-flex items-center justify-between rounded-none border border-app-accent/60 bg-neutral-950/88 px-2 py-1.5 text-sm text-app-canvas-fg",
        qtyBtn: "flex h-7 w-7 items-center justify-center rounded-none bg-neutral-950/88 text-base",
        qtyLabel: "px-2 font-bold tabular-nums",
        userLineItem:
            "flex items-center justify-between gap-3 rounded-none bg-app-accent-soft-bg px-3 py-2.5",
        lineTitle: "truncate text-sm font-semibold text-app-canvas-fg",
        lineSub: "mt-0.5 text-xs text-app-muted",
        lineActions: "flex items-center gap-3",
        removeLink:
            "shrink-0 text-xs font-medium text-app-muted transition-colors hover:text-red-400",
        systemList:
            "mt-2 rounded-none border border-app-accent/25 bg-app-accent/8 px-2.5 py-2 text-sm text-app-canvas-fg",
        systemLine:
            "mt-1 flex items-center justify-between gap-2 rounded-none px-1 py-0.5",
        systemLineName: "min-w-0 truncate text-app-canvas-fg",
        systemLineMeta: "shrink-0 text-app-muted",
        totalsCard:
            "mt-3 space-y-1.5 rounded-none border border-app-border-on-surface bg-app-glass-fill px-3 py-2.5 text-sm",
        totalsRow: "flex items-center justify-between",
        totalsLabelMuted: "text-app-muted",
        totalsLabelStrong: "font-semibold text-app-canvas-fg",
        totalsValue: "font-medium text-app-canvas-fg",
        totalsDivider:
            "flex items-center justify-between border-t border-app-accent/30 pt-2",
        grandTotal: "text-base font-bold text-app-accent",
        giftCard:
            "mt-3 rounded-none border border-app-accent/30 bg-app-accent/10 px-3 py-2.5 text-sm",
        giftRow: "flex items-center justify-between gap-2",
        giftTitle: "text-sm font-bold text-app-accent",
        giftSelectedHint: "mt-0.5 truncate text-xs text-app-muted",
        giftCta: `shrink-0 ${chromePillInactive} px-3 py-1.5 text-xs font-bold text-app-accent`,
        authActions: "mt-3 flex flex-col gap-2",
        authCtaBtn:
            "checkout-auth-cta relative inline-flex w-full items-center justify-center overflow-hidden rounded-none bg-app-accent px-4 py-3 text-sm font-bold text-white transition hover:bg-app-accent-hover",
        complementProgressCard:
            "mt-3 space-y-1 rounded-none border border-app-accent/25 bg-app-accent/8 px-3 py-2",
        zoneStatusIn:
            "rounded-none border border-emerald-500/30 bg-emerald-950/20 px-3 py-2 text-[11px] text-emerald-200",
        zoneStatusOut:
            "rounded-none border border-amber-500/30 bg-amber-950/20 px-3 py-2 text-[11px] text-amber-100",
        previewLoading:
            "rounded-none border border-app-border-on-surface bg-black/5 px-3 py-2 text-[11px] text-app-muted animate-pulse",
        loginLink:
            "text-center text-xs text-app-muted underline-offset-2 hover:text-app-accent hover:underline",
        giftModalList: "space-y-3",
        giftCandidateCard:
            "group relative flex w-full cursor-pointer overflow-hidden border bg-black/5 text-left shadow-[0_10px_32px_rgba(0,0,0,0.45)] transition hover:border-app-accent/50",
        giftCandidateCardSelected:
            "border-app-accent ring-1 ring-app-accent/60",
        giftCandidateCardIdle: "border-app-border-on-surface",
        drinkUpsellList: "space-y-2 text-xs sm:text-sm text-app-canvas-fg",
        drinkUpsellRow:
            "flex items-center gap-3 rounded-none bg-app-accent-soft-bg px-3 py-2",
        drinkUpsellThumb: "h-12 w-12 shrink-0 object-cover bg-black/20",
        drinkUpsellThumbPlaceholder:
            "flex h-12 w-12 shrink-0 items-center justify-center bg-black/20 text-[10px] text-app-muted",
        giftCandidateThumbCol:
            "relative w-28 shrink-0 overflow-hidden aspect-[4/3] sm:w-32",
        giftCandidateBody:
            "flex min-w-0 flex-1 flex-col justify-center gap-1.5 p-2.5 sm:p-3",
        giftCandidateTitle:
            "text-sm font-medium leading-snug text-app-canvas-fg",
        giftCandidateComposition:
            "text-[11px] leading-snug text-app-muted line-clamp-3",
        giftCandidateBadge:
            "absolute right-2 top-2 inline-flex items-center border border-app-accent/50 bg-app-accent/15 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-app-accent",
        giftFooterRow:
            "flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between",
        giftFooterHint: "text-xs text-app-muted",
        giftApplyBtn:
            "inline-flex items-center justify-center rounded-none bg-app-accent px-4 py-2 text-xs font-semibold text-white transition hover:bg-app-accent-hover disabled:cursor-not-allowed disabled:opacity-60 w-full",
    },

    inlineOption: {
        group: "flex gap-2",
        groupVertical: "flex flex-col md:flex-row gap-2",
        btn: "inline-flex flex-1 items-center justify-center border px-3 py-2 text-xs font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-app-accent/60",
        btnIdle:
            "border-app-border-on-surface bg-black/5 text-app-canvas-fg hover:border-app-accent/40 hover:bg-black/10",
        btnSelected:
            "border-app-accent bg-app-accent/15 text-app-accent ring-1 ring-app-accent/50",
    },

    /** Один dual-state контрол (оплата / получение), не набор чипов. */
    methodState: {
        shell:
            "flex flex-row overflow-hidden rounded-none border border-app-accent/35 bg-black/10",
        cell:
            "relative flex min-h-[2.75rem] flex-1 flex-row items-center justify-center gap-1.5 px-2 py-2 text-center transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-app-accent/60 md:min-h-[3.75rem] md:gap-2 md:px-3 md:py-3",
        cellIdle:
            "bg-transparent text-app-muted hover:bg-black/10 hover:text-app-canvas-fg",
        cellSelected: "bg-app-accent text-white",
        cellDivider: "border-r border-app-border-on-surface last:border-r-0",
        icon: "shrink-0 text-lg leading-none md:text-2xl",
        label: "text-xs font-bold leading-none tracking-wide md:text-sm",
    },

    optionCard: {
        listStack: "flex flex-col gap-2",
        card: "group relative flex w-full cursor-pointer border text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-app-accent/60",
        cardSelected:
            "border-app-accent bg-app-accent/10 ring-1 ring-app-accent/50 ",
        cardIdle:
            "border-app-border-on-surface bg-app-glass-fill hover:border-app-accent/40 hover:bg-black/5",
        inner: "flex w-full items-start gap-3 px-3 py-3 sm:px-4 sm:py-3.5",
        iconWrap:
            "flex h-10 w-10 shrink-0 items-center justify-center border text-lg transition",
        iconWrapIdle:
            "border-app-border-on-surface bg-black/10 text-app-canvas-fg",
        iconWrapSelected: "border-app-accent bg-app-accent/15 text-app-accent",
        body: "min-w-0 flex-1 space-y-0.5",
        title: "text-sm font-semibold leading-snug text-app-canvas-fg",
        hint: "text-[11px] leading-snug text-app-muted",
        badge: "absolute right-2.5 top-2.5 inline-flex items-center border border-app-accent/50 bg-app-accent/15 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-app-accent",
        addressCard:
            "relative flex w-full cursor-pointer border text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-app-accent/60",
        addressInner: "flex w-full flex-col gap-0.5 px-3 py-3",
        addressTitle: "text-base font-semibold text-app-canvas-fg",
        addressMeta: "text-sm leading-snug text-app-muted",
    },

    nav: {
        row: "mt-4 flex w-full items-stretch gap-2",
        backBtn:
            "flex h-auto w-12 shrink-0 items-center justify-center rounded-none border border-app-accent/50 bg-neutral-950/88 text-app-canvas-fg transition hover:border-app-accent hover:text-app-accent",
        backIcon: "mdi mdi-arrow-left text-2xl leading-none",
        sheen: "checkout-wizard-cta-sheen pointer-events-none absolute inset-0 z-0",
        primaryContent:
            "relative z-10 flex w-full items-center justify-center gap-3 px-4",
        primaryLabel: "text-base font-bold leading-none",
        totalLabel:
            "text-base font-bold tabular-nums leading-none text-white/85",
        btnPrimary:
            "checkout-wizard-cta relative flex min-h-14 flex-1 items-center justify-center overflow-hidden rounded-none bg-app-accent py-4 text-white transition hover:bg-app-accent-hover",
        btnPrimaryBusy:
            "checkout-wizard-cta relative flex min-h-14 flex-1 items-center justify-center overflow-hidden rounded-none bg-app-accent py-4 text-white transition hover:bg-app-accent-hover disabled:opacity-60",
    },

    summaryRow: {
        row: "flex items-start justify-between gap-3 py-3 first:pt-0 last:pb-0",
        body: "min-w-0 space-y-1",
        label: "text-[11px] font-bold uppercase tracking-[0.12em] text-app-accent",
        value: "text-base leading-snug text-app-canvas-fg",
        editLink:
            "shrink-0 pt-0.5 text-sm font-bold text-app-accent underline-offset-2 hover:underline",
    },

    promo: {
        wrap: "space-y-2",
        cartBody: "space-y-2",
    },

    payment: {
        optionList: "flex flex-col gap-2",
        cashExtra:
            "space-y-2 rounded-none border border-app-border-on-surface bg-black/5 px-3 py-3",
        cashExtraLabel: "text-sm font-medium text-app-canvas-fg",
        cashExtraHint: "text-xs text-app-muted",
    },

    delivery: {
        zonePanel: "rounded-none border px-3 py-2.5 text-sm leading-snug",
        zoneStatusIdle:
            "border-app-border-on-surface bg-black/5 text-app-muted",
        zoneStatusPending:
            "border-app-border-on-surface bg-black/5 text-app-muted animate-pulse",
        zoneStatusUnknown: "border-amber-500/30 bg-amber-950/20 text-amber-100",
        emptyHero: `${nestedCard} space-y-3 border border-app-accent/25 bg-app-accent/8 px-4 py-4`,
        emptyTitle: `${shellTypography.body.checkoutHeadingSm} ${shellColorRoles.accent}`,
        emptyLead: "text-sm leading-snug text-app-muted",
        profileLink:
            "text-sm text-app-accent underline-offset-2 hover:underline",
        savePrimaryBtn:
            "inline-flex w-full items-center justify-center rounded-none bg-app-accent px-3 py-2.5 text-sm font-bold text-white transition hover:bg-app-accent-hover disabled:opacity-60",
        listSection: "space-y-2",
    },

    confirm: {
        stack: "space-y-3.5",
        summaryCard:
            "space-y-1 rounded-none border border-app-accent/25 bg-white/[0.07] px-3.5 py-2.5",
        summaryList: "divide-y divide-app-divider-on-canvas",
        benefitsCard:
            "space-y-2 rounded-none border border-app-accent/20 bg-app-accent/5 px-3 py-3 text-sm",
        benefitLine: "text-sm text-app-canvas-fg",
        benefitLineMuted: "text-sm text-app-muted",
        orderList: "divide-y divide-app-divider-on-canvas text-sm",
        orderLineRow: "flex items-center justify-between gap-3 py-2.5 first:pt-0 last:pb-0",
        orderLineTruncate: "min-w-0 truncate text-base text-app-canvas-fg",
        orderLineMuted: "shrink-0 text-sm tabular-nums text-app-muted",
        systemLineAccent:
            "flex items-center justify-between gap-3 py-2.5 first:pt-0 last:pb-0",
        badgeTiny: "ml-1 text-xs font-bold text-app-accent",
        totalsCard:
            "space-y-2 rounded-none border border-app-accent/25 bg-white/[0.07] px-3.5 py-3 text-sm",
        totalsInset:
            "mt-2 space-y-1 border-t border-app-divider-on-canvas pt-2 text-sm",
        blockMuted:
            "space-y-1 rounded-none border border-app-border-on-surface/80 bg-white/[0.07] px-3 py-3",
        metaRow: "grid grid-cols-1 gap-3 md:grid-cols-2",
        giftCard:
            "rounded-none border border-app-accent/35 bg-app-accent/10 px-3.5 py-3 text-sm",
        giftCardPrompt: "checkout-gift-prompt",
        mutedInline: "ml-1 text-app-muted",
    },

    success: {
        orderTitle: "text-2xl font-bold tracking-tight text-app-accent",
        supportHint: "text-sm leading-relaxed text-app-muted",
        metaLine: "text-sm text-app-muted",
        summaryStack: "mt-4 space-y-3",
        totalRow:
            "flex items-center justify-between gap-2 border-t border-app-accent/30 pt-2 text-base font-bold text-app-canvas-fg",
        footerActions: "mt-4 flex justify-end",
    },
} as const;
