/**
 * Публичные SEO/бренд-константы SPA (дублируют config/site.php; canonical на клиенте — из window.location).
 */

export const siteMeta = {
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
    twitterCard: "summary_large_image",
    defaultRobots: "index,follow",
    pwaInstallDismissKey: "gangsters_pwa_install_dismissed",
};
