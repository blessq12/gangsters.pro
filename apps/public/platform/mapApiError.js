export function mapApiError(error, fallbackMessage) {
    return error?.response?.data?.message || fallbackMessage;
}

export function isAxiosUnauthorized(error) {
    return error?.response?.status === 401;
}
