/**
 * Медленное соединение или режим экономии трафика.
 */
export function isSlowConnection() {
    if (typeof navigator === "undefined") {
        return false;
    }

    const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
    if (!connection) {
        return false;
    }

    if (connection.saveData) {
        return true;
    }

    const type = String(connection.effectiveType || "");
    return type === "slow-2g" || type === "2g" || type === "3g";
}

/**
 * @param {() => void} task
 * @param {{ timeoutMs?: number }} [options]
 */
export function scheduleIdleTask(task, { timeoutMs = 3000 } = {}) {
    if (typeof window === "undefined" || typeof task !== "function") {
        return;
    }

    if (isSlowConnection()) {
        return;
    }

    if (typeof window.requestIdleCallback === "function") {
        window.requestIdleCallback(() => task(), { timeout: timeoutMs });
        return;
    }

    window.setTimeout(task, Math.min(timeoutMs, 2000));
}
