/**
 * Публичные бренд/SEO-дефолты SPA — реэкспорт из site-конфига.
 */

import { site } from "./site";

export const siteMeta = {
    defaultTitle: site.defaultTitle,
    defaultDescription: site.defaultDescription,
    themeColor: site.themeColor,
    ogImagePath: site.ogImagePath,
    ogImageSocialPath: site.ogImageSocialPath,
    defaultRobots: site.defaultRobots,
};
