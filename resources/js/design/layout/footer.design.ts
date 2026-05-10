/**
 * Классы футера: остров, ссылки, модалки с legal.
 */

export const footerDesign = {
    footer: "mt-10 pb-6",
    inner: "mx-auto max-w-7xl px-4 sm:px-6 lg:px-8",
    bar: "flex items-center justify-between gap-4 rounded-none border border-amber-400/30 bg-[rgba(255,255,255,0.035)] px-4 sm:px-6 lg:px-8 py-4 flex-wrap text-sm shadow-[0_0_22px_rgba(0,0,0,0.65)] backdrop-blur",
    navLinks: "flex flex-wrap gap-3 text-slate-200/85",
    navLink:
        "hover:text-amber-300 transition-colors duration-200",
    legalLinks: "flex flex-wrap gap-3 text-slate-300/85",
    legalButton:
        "hover:text-amber-300 transition-colors duration-200",
    copyright: "opacity-70 text-slate-300/80 text-xs sm:text-sm",
    legalHtml:
        "legal-doc text-sm text-slate-200/90 [&_p]:mb-3 [&_p:last-child]:mb-0 [&_ul]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:mb-3 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-amber-300 [&_a]:underline-offset-2 hover:[&_a]:underline [&_strong]:text-slate-100",
    modalFallback: "space-y-3 text-sm text-slate-200/90",
} as const;

export type FooterDesign = typeof footerDesign;
