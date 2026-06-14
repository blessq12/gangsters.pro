/**
 * Публичные SEO/бренд-константы SPA: window.__SITE__ из Blade (config/site.php).
 * Fallback — для import без DOM (сборка/тесты).
 */

const siteMetaFallback = {
    name: "Gangster's Sushi",
    shortName: "Гангстерс Суши",
    pwaDisplayName: "Гангстерс Суши",
    defaultTitle: "Доставка суши и роллов в Томске | Gangster's Sushi",
    defaultDescription:
        "Закажи суши, роллы и горячие блюда с доставкой по Томску. Gangster's Sushi — быстрая доставка и актуальное меню онлайн.",
    themeColor: "#191919",
    backgroundColor: "#191919",
    ogLocale: "ru_RU",
    ogType: "website",
    ogImagePath: "/favicon/web-app-manifest-512x512.png",
    ogImageSocialPath: "/images/og/og-default-1200x630.jpg",
    twitterCard: "summary_large_image",
    defaultRobots: "index,follow",
    pwaInstallDismissKey: "gangsters_pwa_install_dismissed",
};

function resolveSiteMeta() {
    const fromWindow =
        typeof window !== "undefined" &&
        window.__SITE__ &&
        typeof window.__SITE__ === "object" &&
        !Array.isArray(window.__SITE__)
            ? window.__SITE__
            : {};

    return {
        ...siteMetaFallback,
        ...fromWindow,
    };
}

export const siteMeta = resolveSiteMeta();
