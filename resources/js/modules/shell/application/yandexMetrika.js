/**
 * SPA-хиты Яндекс.Метрики (init уже в app.blade.php).
 * Первый afterEach пропускаем — первая страница уже учтена в ym(..., 'init').
 */

let skipNextHit = true;

function counterIds() {
    const raw = window.__YANDEX_METRIKA_COUNTERS__;
    if (!Array.isArray(raw)) {
        return [];
    }

    return raw
        .map((id) => Number(id))
        .filter((id) => Number.isFinite(id) && id > 0);
}

/**
 * @param {string} url
 */
export function trackYandexMetrikaSpaHit(url) {
    if (skipNextHit) {
        skipNextHit = false;
        return;
    }

    if (typeof window.ym !== "function") {
        return;
    }

    const path = typeof url === "string" && url !== "" ? url : window.location.href;

    for (const id of counterIds()) {
        window.ym(id, "hit", path);
    }
}
