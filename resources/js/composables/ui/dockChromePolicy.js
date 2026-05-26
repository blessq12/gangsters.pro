/**
 * Shell-политика видимости dock chrome (без привязки к route).
 */
export function ensureDockChromeVisible(uiStore) {
    uiStore.setShowBottomNav(true);
    uiStore.setMobileDockScrollSuppressed(false);
}

/** Mobile home: scroll-suppression разрешён только при пустой корзине. */
export function shouldAllowMobileDockScrollSuppression(cartItemCount) {
    return (Number(cartItemCount) || 0) === 0;
}
