/**
 * Content bootstrap already running or done — legacy company/marketing fetch not needed.
 *
 * @param {{ loaded: boolean, loading: boolean }} contentStore
 */
export function isContentBootstrapPending(contentStore) {
    return contentStore.loaded || contentStore.loading;
}
