/**
 * SEO по путям SPA. Единственный источник правды для title/description/robots.
 */

export const seoPages = {
    "/": {
        title: "Доставка суши и роллов в Томске | Gangster's Sushi",
        description:
            "Закажи суши, роллы и горячие блюда с доставкой по Томску. Актуальное меню, быстрая доставка и удобный заказ онлайн.",
        robots: "index,follow",
    },
    "/about": {
        title: "О компании | Gangster's Sushi",
        description:
            "Gangster's Sushi — доставка с характером: тёмная эстетика, сочное меню и сервис, который не рассыпается на мелочах.",
        robots: "index,follow",
    },
    "/delivery": {
        title: "Доставка еды в Томске | Gangster's Sushi",
        description:
            "Условия доставки Gangster's Sushi: зоны, сроки, оплата и минимальный заказ. Закажи суши и роллы с доставкой по Томску.",
        robots: "index,follow",
    },
    "/contacts": {
        title: "Контакты | Gangster's Sushi",
        description:
            "Телефон, адрес и режим работы Gangster's Sushi в Томске. Свяжись с нами по заказу и вопросам доставки.",
        robots: "index,follow",
    },
    "/reset-password": {
        title: "Сброс пароля | Gangster's Sushi",
        description: "Установи новый пароль для личного кабинета Gangster's Sushi.",
        robots: "noindex,nofollow",
    },
    "/404": {
        title: "Страница не найдена | Gangster's Sushi",
        description:
            "Запрашиваемая страница не найдена. Перейди на главную Gangster's Sushi и закажи суши с доставкой по Томску.",
        robots: "noindex,nofollow",
    },
};

/**
 * @param {string} path
 * @returns {{ title: string, description: string, robots: string }}
 */
export function seoForPath(path) {
    return seoPages[path] ?? seoPages["/"];
}
