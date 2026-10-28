import axios from "axios";
import { getClientAuthToken } from "../modules/client/authToken";

/**
 Единая точка HTTP для SPA: Laravel-заголовок + Bearer из clientAuthToken.
 */
export const httpClient = axios.create({
    headers: {
        "X-Requested-With": "XMLHttpRequest",
    },
    withCredentials: true,
});

httpClient.interceptors.request.use((config) => {
    const token = getClientAuthToken();
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    } else if (config.headers && "Authorization" in config.headers) {
        delete config.headers.Authorization;
    }
    return config;
});
