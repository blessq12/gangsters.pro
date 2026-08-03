<script setup>
import { computed } from "vue";
import {
    playBottomBarHide,
    playBottomBarShow,
    playDockContentHide,
    playDockContentShow,
} from "../../../animations/animationManager";
import { useBottomDockState } from "../../../modules/shell/application/dockUi";
import { useDockDismiss } from "../../../modules/shell/application/dockUi";
import { useAppDesign } from "../../../design/useAppDesign";
import { useCheckoutStore } from "../../../modules/checkout/store";
import { useFavoritesStore } from "../../../modules/client/store/favoritesStore";
import { useUiStore } from "../../../modules/shell/store/uiStore";
import { useDockMobileInteractions } from "./composables/useDockMobileInteractions";
import DockCartSummary from "./DockCartSummary.vue";
import DockDismissConfirmModal from "./DockDismissConfirmModal.vue";
import DockFavoritesTab from "./DockFavoritesTab.vue";
import DockProfileTab from "./DockProfileTab.vue";

const props = defineProps({
    dockItems: {
        type: Array,
        required: true,
    },
});

const dock = useAppDesign().components.dock;
const chrome = dock.chrome;

const uiStore = useUiStore();
const cartStore = useCheckoutStore();
const favoritesStore = useFavoritesStore();

const { activeDockItem, getBadge, dockItems } = useBottomDockState({
    uiStore,
    cartStore,
    favoritesStore,
    dockItems: props.dockItems,
});

/** Nav tabs only — cart / favorites / profile имеют свои компоненты. */
const navDockItems = computed(() =>
    dockItems.filter(
        (item) =>
            item.id !== "cart" &&
            item.id !== "favorites" &&
            item.id !== "profile",
    ),
);

const favoritesDockItem = computed(
    () => dockItems.find((item) => item.id === "favorites") ?? null,
);

const profileDockItem = computed(
    () => dockItems.find((item) => item.id === "profile") ?? null,
);

const isMobile = computed(() => uiStore.deviceMode === "mobile");

const {
    showScrim,
    confirmOpen,
    pendingConfirm,
    requestDockDismiss,
    confirmDismiss,
    cancelDismiss,
} = useDockDismiss();

useDockMobileInteractions(uiStore, () => isMobile.value);

const isPanelOpen = computed(() => Boolean(activeDockItem.value));

const chromeScaleStyle = computed(() => {
    if (isPanelOpen.value) {
        return {};
    }
    const scale = uiStore.dockChromeScrollScale;
    const opacity = scale < 1 ? 0.88 : 1;

    return {
        transform: `scale(${scale})`,
        transformOrigin: "bottom center",
        opacity,
    };
});

function tabIconTone(id) {
    return uiStore.dockActiveId === id
        ? dock.shared.tabIconActive
        : dock.shared.tabIconInactive;
}

function handleDockClick(id) {
    if (uiStore.dockActiveId === id) {
        requestDockDismiss();
        return;
    }
    uiStore.setDockActive(id);
}

function handleChromeEnter(el, done) {
    playBottomBarShow(el, done);
}

function handleChromeLeave(el, done) {
    playBottomBarHide(el, done);
}

function handlePanelEnter(el, done) {
    playDockContentShow(el, done);
}

function handlePanelLeave(el, done) {
    playDockContentHide(el, done);
}
</script>

<template>
    <teleport to="body">
        <button
            v-if="showScrim"
            type="button"
            :class="dock.shared.panelScrim"
            aria-label="Свернуть панель дока"
            @click="requestDockDismiss"
        />
    </teleport>

    <DockDismissConfirmModal
        v-model="confirmOpen"
        :title="pendingConfirm?.title ?? ''"
        :message="pendingConfirm?.message ?? ''"
        :confirm-label="pendingConfirm?.confirmLabel ?? ''"
        :cancel-label="pendingConfirm?.cancelLabel ?? ''"
        @confirm="confirmDismiss"
        @cancel="cancelDismiss"
    />

    <!-- Bottom dock: panel above horizontal island -->
    <div :class="chrome.fixedRoot">
        <Transition
            name="bottom-bar"
            @enter="handleChromeEnter"
            @leave="handleChromeLeave"
        >
            <div
                v-if="uiStore.showBottomNav"
                :class="[
                    chrome.visibleInner,
                    isPanelOpen ? chrome.visibleInnerWithPanel : '',
                    !isPanelOpen ? dock.shared.chromeScrollTransform : '',
                ]"
                :style="isPanelOpen ? undefined : chromeScaleStyle"
            >
                <Transition
                    name="dock-panel"
                    mode="out-in"
                    @enter="handlePanelEnter"
                    @leave="handlePanelLeave"
                >
                    <div
                        v-if="activeDockItem"
                        :key="activeDockItem.id"
                        :class="[chrome.panelOuter, chrome.panelOuterExpanded]"
                    >
                        <component :is="activeDockItem.content" />
                    </div>
                </Transition>

                <div :class="chrome.dockIsland">
                    <DockCartSummary @toggle="handleDockClick('cart')" />

                    <div :class="chrome.islandDivider" aria-hidden="true" />

                    <div :class="chrome.tabRow">
                        <DockFavoritesTab
                            v-if="favoritesDockItem"
                            :icon-class="favoritesDockItem.iconClass"
                            @toggle="handleDockClick('favorites')"
                        />

                        <DockProfileTab
                            v-if="profileDockItem"
                            :icon-class="profileDockItem.iconClass"
                            @toggle="handleDockClick('profile')"
                        />

                        <button
                            v-for="item in navDockItems"
                            :key="item.id"
                            type="button"
                            :class="chrome.tabButton"
                            :title="item.label"
                            :aria-label="item.label"
                            :data-dock-target="item.id"
                            @click="handleDockClick(item.id)"
                        >
                            <span
                                :class="[
                                    chrome.tabIconWrap,
                                    tabIconTone(item.id),
                                ]"
                                :data-dock-bump-root="item.id"
                            >
                                <i
                                    :class="[
                                        'mdi',
                                        item.iconClass,
                                        chrome.tabIconMdiSize,
                                    ]"
                                />
                                <span
                                    v-if="getBadge(item.id) > 0"
                                    :class="dock.shared.badge"
                                    :data-dock-badge="item.id"
                                >
                                    {{ getBadge(item.id) }}
                                </span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped></style>
