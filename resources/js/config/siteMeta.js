/**
 * Публичные бренд/SEO-дефолты SPA — реэкспорт из site-конфига.
 */

import { site } from "./site";

export const siteMeta = {
    name: site.name,
    shortName: site.shortName,
    pwaDisplayName: site.pwaDisplayName,
    defaultTitle: site.defaultTitle,
    defaultDescription: site.defaultDescription,
    themeColor: site.themeColor,
    backgroundColor: site.backgroundColor,
    ogLocale: site.ogLocale,
    ogType: site.ogType,
    ogImagePath: site.ogImagePath,
    ogImageSocialPath: site.ogImageSocialPath,
    twitterCard: site.twitterCard,
    defaultRobots: site.defaultRobots,
    pwaInstallDismissKey: site.pwaInstallDismissKey,
};
