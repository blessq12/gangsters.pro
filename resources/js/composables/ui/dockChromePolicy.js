/**
 * Shell-политика видимости dock chrome (без привязки к route).
 */
export function ensureDockChromeVisible(uiStore) {
    uiStore.setShowBottomNav(true);
    uiStore.setDockChromeScrollScale(1);
}
