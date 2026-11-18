import axios from "axios";
import { getClientAuthToken } from "../modules/client/authToken";

/**
 * HTTP transport for the public SPA.
 * Base URL is only /api/public — no overlap with backoffice.
 */
export const http = axios.create({
    baseURL: "/api/public",
    headers: {
        "X-Requested-With": "XMLHttpRequest",
    },
    withCredentials: true,
});

http.interceptors.request.use((config) => {
    const token = getClientAuthToken();
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    } else if (config.headers && "Authorization" in config.headers) {
        delete config.headers.Authorization;
    }
    return config;
});
