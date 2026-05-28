/**
 * Fixed top banner for delivery/gift benefits.
 */
export const topBenefitsDesign = {
    root: "pointer-events-none fixed inset-x-0 z-[29]",
    inner: "pointer-events-auto mx-auto max-w-7xl px-4 sm:px-6 lg:px-8",
    mobileOffset: "top-[72px]",
    desktopOffset: "top-[108px]",
    bar: "mx-auto w-full max-w-xl rounded-none border border-app-accent/40 bg-app-canvas px-3 py-2 shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur",
    rows: "space-y-2",
    row: "space-y-1.5 rounded-none border border-black/20 bg-neutral-950/88 px-2 py-1.5",
    label: "text-xs text-app-canvas-fg/90",
    track: "h-2 w-full rounded-none border border-app-accent/40 bg-app-canvas",
    fill: "h-full rounded-none bg-app-accent transition-[width] duration-500 ease-out",
} as const;

export type TopBenefitsDesign = typeof topBenefitsDesign;
