import { seoPages } from "./seoPages";

const faviconBase = "/favicon";

/**
 * Публичный конфиг сайта / SEO-дефолты SPA.
 */
export const site = {
    defaultTitle: seoPages["/"].title,
    defaultDescription: seoPages["/"].description,
    defaultRobots: "index,follow",
    themeColor: "#191919",
    ogImagePath: `${faviconBase}/web-app-manifest-512x512.png`,
    ogImageSocialPath: "/images/og/og-default-1200x630.jpg",
};

/**
 * @returns {string|null}
 */
export function readYandexMapsApiKey() {
    const key = import.meta.env?.VITE_YANDEX_MAPS_API_KEY;
    return typeof key === "string" && key.trim() !== "" ? key.trim() : null;
}
