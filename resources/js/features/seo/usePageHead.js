import { siteMeta } from "../../config/siteMeta";

function absoluteUrl(pathOrUrl) {
    if (typeof window === "undefined") return pathOrUrl;
    if (/^https?:\/\//i.test(pathOrUrl)) return pathOrUrl;
    const origin = window.location.origin.replace(/\/$/, "");
    const path = pathOrUrl.startsWith("/") ? pathOrUrl : `/${pathOrUrl}`;
    return `${origin}${path}`;
}

function upsertMeta(selector, attributes) {
    if (typeof document === "undefined") return;

    let el = document.head.querySelector(selector);
    if (!el) {
        const tag = selector.startsWith("link") ? "link" : "meta";
        el = document.createElement(tag);
        document.head.appendChild(el);
    }

    for (const [key, value] of Object.entries(attributes)) {
        el.setAttribute(key, value);
    }
}

function resolveSeo(route) {
    const seo = route.meta?.seo;
    const title = seo?.title ?? siteMeta.defaultTitle;
    const description = seo?.description ?? siteMeta.defaultDescription;
    const robots = seo?.robots ?? siteMeta.defaultRobots;
    const ogImagePath = seo?.ogImage ?? siteMeta.ogImagePath;

    return {
        title,
        description,
        robots,
        canonicalUrl: absoluteUrl(route.fullPath || "/"),
        ogImageUrl: absoluteUrl(ogImagePath),
    };
}

/**
 * Синхронизирует document.title и SEO meta после навигации SPA.
 *
 * @param {import('vue-router').RouteLocationNormalizedLoaded} route
 */
export function applyPageHead(route) {
    if (typeof document === "undefined") return;

    const { title, description, robots, canonicalUrl, ogImageUrl } =
        resolveSeo(route);

    document.title = title;

    upsertMeta('meta[name="description"]', {
        name: "description",
        content: description,
    });
    upsertMeta('meta[name="robots"]', {
        name: "robots",
        content: robots,
    });
    upsertMeta('link[rel="canonical"]', {
        rel: "canonical",
        href: canonicalUrl,
    });
    upsertMeta('meta[property="og:title"]', {
        property: "og:title",
        content: title,
    });
    upsertMeta('meta[property="og:description"]', {
        property: "og:description",
        content: description,
    });
    upsertMeta('meta[property="og:url"]', {
        property: "og:url",
        content: canonicalUrl,
    });
    upsertMeta('meta[property="og:image"]', {
        property: "og:image",
        content: ogImageUrl,
    });
    upsertMeta('meta[name="twitter:title"]', {
        name: "twitter:title",
        content: title,
    });
    upsertMeta('meta[name="twitter:description"]', {
        name: "twitter:description",
        content: description,
    });
    upsertMeta('meta[name="twitter:image"]', {
        name: "twitter:image",
        content: ogImageUrl,
    });
}
