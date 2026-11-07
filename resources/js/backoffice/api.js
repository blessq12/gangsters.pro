function readCookie(name) {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
    return match ? decodeURIComponent(match[1]) : "";
}

function messageFrom(payload) {
    if (typeof payload?.message === "string" && payload.message !== "") {
        return payload.message;
    }
    const errors = payload?.errors;
    if (errors && typeof errors === "object") {
        const first = Object.values(errors)[0];
        if (Array.isArray(first) && typeof first[0] === "string") {
            return first[0];
        }
    }
    return "Не удалось связаться с сервером.";
}

export async function backofficeRequest(method, path, body) {
    const headers = {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
    };
    const token = readCookie("XSRF-TOKEN");
    if (token !== "") {
        headers["X-XSRF-TOKEN"] = token;
    }
    if (body !== undefined) {
        headers["Content-Type"] = "application/json";
    }

    const response = await fetch(path, {
        method,
        credentials: "same-origin",
        headers,
        body: body !== undefined ? JSON.stringify(body) : undefined,
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(messageFrom(payload));
    }
    return payload;
}

export async function backofficeForm(path, formData) {
    const headers = {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
    };
    const token = readCookie("XSRF-TOKEN");
    if (token !== "") {
        headers["X-XSRF-TOKEN"] = token;
    }

    const response = await fetch(path, {
        method: "POST",
        credentials: "same-origin",
        headers,
        body: formData,
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(messageFrom(payload));
    }
    return payload;
}

export function formatDateTime(value) {
    if (!value) {
        return "—";
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return String(value);
    }
    const pad = (part) => String(part).padStart(2, "0");
    return `${pad(date.getDate())}.${pad(date.getMonth() + 1)}.${date.getFullYear()} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

export function formatDate(value) {
    if (!value) {
        return null;
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return String(value);
    }
    const pad = (part) => String(part).padStart(2, "0");
    return `${pad(date.getDate())}.${pad(date.getMonth() + 1)}.${date.getFullYear()}`;
}
