<script setup>
import { computed } from "vue";
import { useUiStore } from "../../stores/uiStore";
import { useCartStore } from "../../stores/cartStore";
import { useFavoritesStore } from "../../stores/favoritesStore";
import {
    playBottomBarShow,
    playBottomBarHide,
    playDockContentShow,
    playDockContentHide,
} from "../../animations/animationManager";
import { useBottomDockState } from "../../composables/ui/useBottomDockState";
import { useDockMobileInteractions } from "../../composables/dock/useDockMobileInteractions";

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

const uiStore = useUiStore();
const cartStore = useCartStore();
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

function handleDockClick(id) {
    uiStore.setDockActive(id);
}

function handleChromeEnter(el, done) {
    playBottomBarShow(el, animVariant.value);
    if (done) done();
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
        class="pointer-events-none fixed inset-x-0 bottom-4 z-30"
    >
        <Transition
            name="bottom-bar"
            @enter="handleChromeEnter"
            @leave="handleChromeLeave"
        >
            <div
                v-if="chromeVisible"
                class="pointer-events-auto mx-auto max-w-7xl px-4 sm:px-6"
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
                        class="mb-3 mx-auto w-full max-w-4xl"
                        @touchstart.passive="onDockPanelTouchStart"
                        @touchend="onDockPanelTouchEnd"
                    >
                        <component :is="activeDockItem.content" />
                    </div>
                </Transition>

                <div
                    class="mx-auto flex max-w-3xl items-center justify-center gap-4 rounded-2xl border border-amber-400/30 bg-[rgba(255,255,255,0.035)] px-5 sm:px-6 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur"
                >
                    <div class="flex items-center gap-3 sm:gap-4">
                        <button
                            v-for="item in dockItems"
                            :key="item.id"
                            type="button"
                            class="group flex flex-col items-center gap-1.5 text-xs sm:text-xs transition-colors"
                            @click="handleDockClick(item.id)"
                        >
                            <span
                                class="relative flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full border transition-colors"
                                :class="
                                    uiStore.dockActiveId === item.id
                                        ? 'border-amber-400/70 bg-black/80 text-amber-200 shadow-[0_0_18px_rgba(251,191,36,0.7)]'
                                        : 'border-white/20 bg-black/70 text-slate-200 group-hover:border-amber-400/50 group-hover:text-amber-200'
                                "
                            >
                                <i :class="['mdi', item.iconClass, 'text-lg sm:text-xl']" />
                                <span
                                    v-if="getBadge(item.id) > 0"
                                    class="absolute -top-1.5 -right-1.5 flex min-h-[1.1rem] min-w-[1.1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white shadow-[0_0_8px_rgba(239,68,68,0.65)]"
                                >
                                    {{ getBadge(item.id) }}
                                </span>
                            </span>
                            <span
                                class="hidden lg:block"
                                :class="
                                    uiStore.dockActiveId === item.id
                                        ? 'text-amber-200'
                                        : 'text-slate-300 group-hover:text-amber-200'
                                "
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
        class="pointer-events-none fixed inset-y-0 left-0 z-30 flex items-center pl-6 sm:pl-10 lg:pl-12 xl:pl-14"
    >
        <Transition
            name="bottom-bar"
            @enter="handleChromeEnter"
            @leave="handleChromeLeave"
        >
            <div
                v-if="uiStore.showBottomNav"
                class="pointer-events-auto flex max-h-[min(88vh,920px)] max-w-[min(96vw,960px)] items-start gap-4 overflow-y-auto rounded-2xl border border-amber-400/30 bg-[rgba(0,0,0,0.65)] px-3 py-3 backdrop-blur"
            >
                <div class="flex flex-col items-center gap-2">
                    <button
                        v-for="item in dockItems"
                        :key="item.id"
                        type="button"
                        class="group flex flex-col items-center gap-2 transition-colors"
                        :title="item.label"
                        @click="handleDockClick(item.id)"
                    >
                        <span
                            class="relative flex h-10 w-10 items-center justify-center rounded-full border transition-colors"
                            :class="
                                uiStore.dockActiveId === item.id
                                    ? 'border-amber-400/70 bg-black/80 text-amber-200 shadow-[0_0_18px_rgba(251,191,36,0.7)]'
                                    : 'border-white/20 bg-black/70 text-slate-200 group-hover:border-amber-400/50 group-hover:text-amber-200'
                            "
                        >
                            <i :class="['mdi', item.iconClass, 'text-lg']" />
                            <span
                                v-if="getBadge(item.id) > 0"
                                class="absolute -top-1.5 -right-1.5 flex min-h-[1.1rem] min-w-[1.1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white shadow-[0_0_8px_rgba(239,68,68,0.65)]"
                            >
                                {{ getBadge(item.id) }}
                            </span>
                        </span>
                        <span
                            class="hidden lg:block text-[11px] text-slate-300 group-hover:text-amber-200"
                            :class="
                                uiStore.dockActiveId === item.id
                                    ? 'text-amber-200'
                                    : 'text-slate-300'
                            "
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
                        class="mt-0 mb-0 w-[520px] max-w-[70vw]"
                    >
                        <component :is="activeDockItem.content" />
                    </div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped></style>
