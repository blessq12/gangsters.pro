<script setup>
import { computed } from "vue";
import { useUiStore } from "../../../stores/uiStore";
import { useCheckoutStore } from "../../../stores/checkoutStore";
import { useFavoritesStore } from "../../../stores/favoritesStore";
import {
    playBottomBarShow,
    playBottomBarHide,
    playDockContentShow,
    playDockContentHide,
} from "../../../animations/animationManager";
import { useBottomDockState } from "../../../composables/ui/useBottomDockState";
import { useDockDismiss } from "../../../composables/ui/useDockDismiss";
import { useDockMobileInteractions } from "./composables/useDockMobileInteractions";
import { useAppDesign } from "../../../design/useAppDesign";
import DockDismissConfirmModal from "./DockDismissConfirmModal.vue";

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

const isMobile = computed(() => uiStore.deviceMode === "mobile");

const {
    showScrim,
    confirmOpen,
    pendingConfirm,
    requestDockDismiss,
    confirmDismiss,
    cancelDismiss,
} = useDockDismiss();

const {
    dockPanelOuterRef,
    onDockPanelTouchStart,
    onDockPanelTouchEnd,
} = useDockMobileInteractions(uiStore, () => isMobile.value, requestDockDismiss);

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
                        ref="dockPanelOuterRef"
                        :key="activeDockItem.id"
                        :class="[
                            chrome.panelOuter,
                            chrome.panelOuterExpanded,
                        ]"
                        @touchstart.passive="onDockPanelTouchStart"
                        @touchend="onDockPanelTouchEnd"
                    >
                        <component :is="activeDockItem.content" />
                    </div>
                </Transition>

                <div :class="chrome.dockIsland">
                    <div :class="chrome.tabRow">
                        <button
                            v-for="item in dockItems"
                            :key="item.id"
                            type="button"
                            :class="chrome.tabButton"
                            :title="item.label"
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
                            <span
                                :class="[
                                    chrome.tabLabelVisibility,
                                    uiStore.dockActiveId === item.id
                                        ? chrome.tabLabelActive
                                        : chrome.tabLabelInactive,
                                ]"
                            >
                                {{ item.label }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped></style>
