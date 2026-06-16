/**
 * Контент-панели дока (Cart / Profile / Favorites / Delivery).
 * Компоненты: resources/js/components/layout/dock/panels/*.vue
 * Оболочка: DockPanelLayout + shared.layout.
 */

import { shellColorRoles, shellTypography } from "./shell.design";

const dockPanelCardSurface =
    "rounded-none border border-app-accent/30 bg-[rgba(0,0,0,0.88)] shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur";

const dockPanelShell =
    `relative flex w-full min-w-0 max-w-full flex-col gap-3 min-h-0 h-full max-h-full overflow-hidden ${dockPanelCardSurface} px-4 py-4 sm:px-6 lg:px-8`;

export const dockPanelsDesign = {
    shared: {
        layout: {
            root: dockPanelShell,
            header: "shrink-0 space-y-2",
            headerRow:
                "flex min-w-0 items-start justify-between gap-3",
            title: `font-heading ${shellTypography.scale.heading.section} ${shellColorRoles.accent}`,
            description: `${shellTypography.body.secondaryDescription} ${shellColorRoles.muted}`,
            body: "min-h-0 flex-1 touch-pan-y space-y-4 overflow-y-auto text-xs text-app-canvas-fg sm:text-sm",
            footer:
                "shrink-0 border-t border-app-accent/15 pt-3 mt-1",
        },
        typography: {
            metaLine: "text-[11px] text-app-muted",
            sectionLabelUppercase:
                "mb-2 text-xs font-medium uppercase tracking-wide text-app-muted",
        },
        minWidth0: "min-w-0",
        contentStack: "space-y-3",
    },

    cart: {
        headerBadge:
            "flex h-8 shrink-0 items-center rounded-none bg-neutral-950/88 px-3 text-xs text-app-canvas-fg",
    },

    profile: {
        headerIdentity: "flex min-w-0 items-center gap-2",
        avatar:
            "flex h-10 w-10 shrink-0 items-center justify-center rounded-none border border-app-accent/40 bg-neutral-950/88 text-sm font-semibold text-app-accent shadow-[0_0_16px_rgba(198,36,36,0.6)]",
        nameLine: "text-xs font-semibold text-app-canvas-fg",
        tabRow: "flex flex-wrap gap-2 text-[11px] font-medium",
        tabs: {
            base: "rounded-none px-3 py-1 transition",
            active:
                "bg-app-accent text-black shadow-[0_0_14px_rgba(198,36,36,0.7)]",
            inactive: "bg-black/5 text-app-canvas-fg hover:bg-black/8",
        },
        ordersMinHeight: "min-h-[12rem]",
    },

    favorites: {
        emptyState:
            "rounded-none bg-app-accent-soft-bg px-4 py-5 text-sm text-app-muted",
        ul: "space-y-2 text-xs sm:text-sm text-app-canvas-fg",
        row: "flex items-center justify-between gap-3 rounded-none bg-app-accent-soft-bg px-3 py-2",
        productName: "truncate font-medium text-app-canvas-fg",
        productPrice: "mt-0.5 text-[11px] text-app-muted",
        actionRow: "flex items-center gap-3",
        actionAddToCart:
            "shrink-0 text-[11px] text-app-accent transition-colors hover:text-app-accent",
        actionRemove:
            "shrink-0 text-[11px] text-app-muted transition-colors hover:text-red-400",
    },

    delivery: {
        mapBodyOverride:
            "flex min-h-0 flex-1 flex-col overflow-hidden p-0 space-y-0",
        mapStage:
            "relative h-full min-h-[min(420px,50dvh)] w-full flex-1 overflow-hidden",
        mapLayer:
            "pointer-events-auto absolute inset-0 z-0",
        mapIframe: "h-full w-full border-0",
        mapFallback:
            "absolute inset-0 z-0 grid place-content-center bg-app-surface px-4 py-8 text-center text-sm text-neutral-600",
        mapFallbackProse: "mx-auto max-w-prose leading-relaxed",
        mapLoadingBox:
            "absolute inset-0 z-0 grid place-content-center bg-app-surface text-sm text-neutral-600",
        overlayTop:
            "pointer-events-none absolute inset-x-3 top-3 z-10 flex flex-wrap gap-2",
        island:
            "pointer-events-auto rounded-none bg-[rgba(0,0,0,0.82)] px-3 py-2 shadow-lg backdrop-blur",
        statCard:
            "min-w-0 flex-1 basis-[7.5rem] sm:flex-none sm:basis-auto",
        statSkeletonCard: "min-w-0 flex-1 basis-full sm:basis-auto",
        statLabel:
            "text-[11px] uppercase tracking-[0.2em] text-app-muted",
        statValueSkeleton: "mt-1 font-semibold text-app-accent/90",
        statValue:
            "mt-1 min-w-0 wrap-break-word text-left text-sm font-semibold leading-relaxed text-app-accent sm:text-base",
    },
} as const;

export type DockPanelsDesign = typeof dockPanelsDesign;
