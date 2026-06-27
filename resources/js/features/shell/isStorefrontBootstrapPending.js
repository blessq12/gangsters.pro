/**
 * Bootstrap витрины уже выполняется или завершён — legacy autoload не нужен.
 *
 * @param {{ loaded: boolean, loading: boolean }} storefrontStore
 */
export function isStorefrontBootstrapPending(storefrontStore) {
    return storefrontStore.loaded || storefrontStore.loading;
}
