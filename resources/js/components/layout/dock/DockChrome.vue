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
    variant: {
        type: String,
        required: true,
        validator: (v) => v === "mobile" || v === "desktop",
    },
    dockItems: {
        type: Array,
        required: true,
    },
});

const dock = useAppDesign().components.dock;

const uiStore = useUiStore();
const cartStore = useCheckoutStore();
const favoritesStore = useFavoritesStore();

const { activeDockItem, getBadge, dockItems } = useBottomDockState({
    uiStore,
    cartStore,
    favoritesStore,
    dockItems: props.dockItems,
});

const isMobile = computed(() => props.variant === "mobile");

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

const animVariant = computed(() => (props.variant === "desktop" ? "desktop" : "mobile"));

const isMobilePanelOpen = computed(
    () => isMobile.value && Boolean(activeDockItem.value),
);
const isDesktopPanelOpen = computed(
    () => !isMobile.value && Boolean(activeDockItem.value),
);

const chromeScaleStyle = computed(() => {
    if (isMobilePanelOpen.value) {
        return {};
    }
    const scale = uiStore.dockChromeScrollScale;
    const origin = isMobile.value ? "bottom center" : "center left";
    const opacity = scale < 1 ? 0.88 : 1;

    return {
        transform: `scale(${scale})`,
        transformOrigin: origin,
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
    playBottomBarShow(el, done, animVariant.value);
}

function handleChromeLeave(el, done) {
    playBottomBarHide(el, done, animVariant.value);
}

function handlePanelEnter(el, done) {
    playDockContentShow(el, done, animVariant.value);
}

function handlePanelLeave(el, done) {
    playDockContentHide(el, done, animVariant.value);
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

    <!-- Mobile: панель над нижним островом -->
    <div
        v-if="variant === 'mobile'"
        :class="dock.mobile.fixedRoot"
    >
        <Transition
            name="bottom-bar"
            @enter="handleChromeEnter"
            @leave="handleChromeLeave"
        >
            <div
                v-if="uiStore.showBottomNav"
                :class="[
                    dock.mobile.visibleInner,
                    isMobilePanelOpen ? dock.mobile.visibleInnerWithPanel : '',
                    !isMobilePanelOpen ? dock.shared.chromeScrollTransform : '',
                ]"
                :style="isMobilePanelOpen ? undefined : chromeScaleStyle"
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
                            dock.mobile.panelOuter,
                            dock.mobile.panelOuterExpanded,
                        ]"
                        @touchstart.passive="onDockPanelTouchStart"
                        @touchend="onDockPanelTouchEnd"
                    >
                        <component :is="activeDockItem.content" />
                    </div>
                </Transition>

                <div :class="dock.mobile.dockIsland">
                    <div :class="dock.mobile.tabRow">
                        <button
                            v-for="item in dockItems"
                            :key="item.id"
                            type="button"
                            :class="dock.mobile.tabButton"
                            :data-dock-target="item.id"
                            @click="handleDockClick(item.id)"
                        >
                            <span
                                :class="[
                                    dock.mobile.tabIconWrap,
                                    tabIconTone(item.id),
                                ]"
                                :data-dock-bump-root="item.id"
                            >
                                <i
                                    :class="[
                                        'mdi',
                                        item.iconClass,
                                        dock.mobile.tabIconMdiSize,
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
                                    dock.mobile.tabLabelVisibility,
                                    uiStore.dockActiveId === item.id
                                        ? dock.mobile.tabLabelActive
                                        : dock.mobile.tabLabelInactive,
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

    <!-- Desktop: слева, по вертикали по центру вьюпорта; pl больше контента — воздух от края -->
    <div
        v-else
        :class="dock.desktop.fixedRoot"
    >
        <Transition
            name="bottom-bar"
            @enter="handleChromeEnter"
            @leave="handleChromeLeave"
        >
            <div
                v-if="uiStore.showBottomNav"
                :class="[
                    dock.desktop.chromeIsland,
                    isDesktopPanelOpen ? dock.desktop.chromeIslandWithPanel : '',
                    dock.shared.chromeScrollTransform,
                ]"
                :style="chromeScaleStyle"
            >
                <div :class="dock.desktop.tabColumn">
                    <button
                        v-for="item in dockItems"
                        :key="item.id"
                        type="button"
                        :class="dock.desktop.tabButton"
                        :title="item.label"
                        :data-dock-target="item.id"
                        @click="handleDockClick(item.id)"
                    >
                        <span
                            :class="[
                                dock.desktop.tabIconWrap,
                                tabIconTone(item.id),
                            ]"
                            :data-dock-bump-root="item.id"
                        >
                            <i
                                :class="[
                                    'mdi',
                                    item.iconClass,
                                    dock.desktop.tabIconMdiSize,
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
                                dock.desktop.tabLabelHidden,
                                uiStore.dockActiveId === item.id
                                    ? dock.desktop.tabLabelActive
                                    : dock.desktop.tabLabelInactiveMuted,
                            ]"
                        >
                            {{ item.label }}
                        </span>
                    </button>
                </div>

                <Transition
                    name="dock-panel"
                    mode="out-in"
                    @enter="handlePanelEnter"
                    @leave="handlePanelLeave"
                >
                    <div
                        v-if="activeDockItem"
                        :key="activeDockItem.id"
                        :class="[
                            dock.desktop.desktopPanelOuter,
                            dock.desktop.desktopPanelOuterExpanded,
                        ]"
                    >
                        <component :is="activeDockItem.content" />
                    </div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped></style>
