<script setup>
import { computed } from "vue";
import { useUiStore } from "../../../stores/uiStore";
import { useCheckoutPricingStore } from "../../../stores/checkoutPricingStore";
import { useFavoritesStore } from "../../../stores/favoritesStore";
import {
    playBottomBarShow,
    playBottomBarHide,
    playDockContentShow,
    playDockContentHide,
} from "../../../animations/animationManager";
import { useBottomDockState } from "../../../composables/ui/useBottomDockState";
import { useDockMobileInteractions } from "./composables/useDockMobileInteractions";
import { useAppDesign } from "../../../design/useAppDesign";

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
const cartStore = useCheckoutPricingStore();
const favoritesStore = useFavoritesStore();

const { activeDockItem, getBadge, dockItems } = useBottomDockState({
    uiStore,
    cartStore,
    favoritesStore,
    dockItems: props.dockItems,
});

const isMobile = computed(() => props.variant === "mobile");

const {
    dockPanelOuterRef,
    onDockPanelTouchStart,
    onDockPanelTouchEnd,
} = useDockMobileInteractions(uiStore, () => isMobile.value);

const animVariant = computed(() => (props.variant === "desktop" ? "desktop" : "mobile"));

const chromeVisible = computed(() => {
    if (!isMobile.value) {
        return uiStore.showBottomNav;
    }
    return uiStore.showBottomNav && !uiStore.mobileDockSuppressedByScroll;
});

function tabIconTone(id) {
    return uiStore.dockActiveId === id
        ? dock.shared.tabIconActive
        : dock.shared.tabIconInactive;
}

function handleDockClick(id) {
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
                v-if="chromeVisible"
                :class="dock.mobile.visibleInner"
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
                        :class="dock.mobile.panelOuter"
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
                :class="dock.desktop.chromeIsland"
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
                        :class="dock.desktop.desktopPanelOuter"
                    >
                        <component :is="activeDockItem.content" />
                    </div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped></style>
