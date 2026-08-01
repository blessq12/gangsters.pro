import { seoPages } from "./seoPages";

const faviconBase = "/favicon";

/**
 * Публичный конфиг сайта / PWA / SEO-дефолты.
 * Единственный источник правды для shell SPA (бывший config/site.php).
 */
export const site = {
    name: "Gangster's Sushi",
    shortName: "Гангстерс Суши",
    pwaDisplayName: "Гангстерс Суши",
    defaultTitle: seoPages["/"].title,
    defaultDescription: seoPages["/"].description,
    defaultRobots: "index,follow",
    themeColor: "#191919",
    backgroundColor: "#191919",
    ogLocale: "ru_RU",
    ogType: "website",
    ogImagePath: `${faviconBase}/web-app-manifest-512x512.png`,
    ogImageSocialPath: "/images/og/og-default-1200x630.jpg",
    twitterCard: "summary_large_image",
    pwaInstallDismissKey: "gangsters_pwa_install_dismissed",
    lang: "ru",

    favicon: {
        base: faviconBase,
        svg: `${faviconBase}/favicon.svg`,
        ico: `${faviconBase}/favicon.ico`,
        png96: `${faviconBase}/favicon-96x96.png`,
        appleTouchIcon: `${faviconBase}/apple-touch-icon.png`,
        browserconfig: `${faviconBase}/browserconfig.xml`,
        manifest: `${faviconBase}/site.webmanifest`,
    },

    pwa: {
        id: "/",
        startUrl: "/?utm_source=pwa",
        scope: "/",
        display: "standalone",
        icon192: `${faviconBase}/web-app-manifest-192x192.png`,
        icon512: `${faviconBase}/web-app-manifest-512x512.png`,
        icons: [
            {
                src: `${faviconBase}/web-app-manifest-192x192.png`,
                sizes: "192x192",
                type: "image/png",
                purpose: "any",
            },
            {
                src: `${faviconBase}/web-app-manifest-512x512.png`,
                sizes: "512x512",
                type: "image/png",
                purpose: "any",
            },
            {
                src: `${faviconBase}/web-app-manifest-512x512.png`,
                sizes: "512x512",
                type: "image/png",
                purpose: "maskable",
            },
        ],
        shortcuts: [
            {
                name: "Меню",
                short_name: "Меню",
                url: "/",
                icon: `${faviconBase}/web-app-manifest-192x192.png`,
            },
            {
                name: "Доставка",
                short_name: "Доставка",
                url: "/delivery",
                icon: `${faviconBase}/web-app-manifest-192x192.png`,
            },
        ],
    },
};

/**
 * @returns {string|null}
 */
export function readYandexMapsApiKey() {
    const key = import.meta.env?.VITE_YANDEX_MAPS_API_KEY;
    return typeof key === "string" && key.trim() !== "" ? key.trim() : null;
}
