/** Bearer for backoffice API; synced from authStore */
const STORAGE_KEY = "gangsters_backoffice_auth";

let backofficeToken = null;

function setBackofficeAuthToken(value) {
    backofficeToken = value || null;
}

export function getBackofficeAuthToken() {
    if (backofficeToken) {
        return backofficeToken;
    }

    if (typeof window === "undefined") {
        return null;
    }

    try {
        const raw = window.localStorage.getItem(STORAGE_KEY);
        if (!raw) {
            return null;
        }

        const parsed = JSON.parse(raw);
        const token = parsed?.token;
        if (typeof token === "string" && token !== "") {
            backofficeToken = token;
            return backofficeToken;
        }
    } catch {
        // broken storage — treat as logged out
    }

    return null;
}

export function persistBackofficeAuthToken(token) {
    setBackofficeAuthToken(token);
    if (typeof window === "undefined") {
        return;
    }
    if (!token) {
        window.localStorage.removeItem(STORAGE_KEY);
        return;
    }
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify({ token }));
}
