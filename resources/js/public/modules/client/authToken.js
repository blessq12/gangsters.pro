/** Bearer для API SPA; синхронизируется из userStore.setToken */
const USER_STORAGE_KEY = "gangsters_user";

let clientToken = null;

export function setClientAuthToken(value) {
    clientToken = value || null;
}

export function getClientAuthToken() {
    if (clientToken) {
        return clientToken;
    }

    if (typeof window === "undefined") {
        return null;
    }

    try {
        const raw = window.localStorage.getItem(USER_STORAGE_KEY);
        if (!raw) {
            return null;
        }

        const parsed = JSON.parse(raw);
        const token = parsed?.token;
        if (typeof token === "string" && token !== "") {
            clientToken = token;
            return clientToken;
        }
    } catch {
        // storage битый — считаем, что токена нет
    }

    return null;
}
