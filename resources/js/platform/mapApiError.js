export function mapApiError(error, fallbackMessage) {
    return error?.response?.data?.message || fallbackMessage;
}

export function isAxiosUnauthorized(error) {
    return error?.response?.status === 401;
}

export function isAxiosNotFound(error) {
    return error?.response?.status === 404;
}

export function isAxiosNetworkError(error) {
    return Boolean(error) && !error.response;
}
