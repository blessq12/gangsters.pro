/**
 * App bootstrap уже выполняется или завершён — legacy autoload каталога не нужен.
 *
 * @param {{ loaded: boolean, loading: boolean }} appBootstrapStore
 */
export function isAppBootstrapPending(appBootstrapStore) {
    return appBootstrapStore.loaded || appBootstrapStore.loading;
}
