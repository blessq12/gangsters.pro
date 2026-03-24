export function mapApiError(error, fallbackMessage) {
    return error?.response?.data?.message || fallbackMessage;
}
