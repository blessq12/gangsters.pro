<script setup>
import { ref, watch, onBeforeUnmount } from "vue";
import { useUiStore } from "../../stores/uiStore";
import { useCartStore } from "../../stores/cartStore";
import {
    playBottomBarShow,
    playBottomBarHide,
    playDockContentShow,
    playDockContentHide,
} from "../../animations/animationManager";
import { useBottomDockState } from "../../composables/ui/useBottomDockState";
import { dockItems as dockItemsMobile } from "../../dock/dockRegistryMobile";
import {
    pushBodyScrollLock,
    popBodyScrollLock,
} from "../../utils/system/bodyScrollLock";

const uiStore = useUiStore();
const cartStore = useCartStore();

const { activeDockItem, getBadge, dockItems } = useBottomDockState({
    uiStore,
    cartStore,
    dockItems: dockItemsMobile,
});

const dockPanelOuterRef = ref(null);
const touchStart = ref({ x: 0, y: 0 });
let touchStartTargetEl = null;

const SWIPE_CLOSE_MIN_DISTANCE_PX = 80;
const SWIPE_CLOSE_MAX_X_RATIO = 0.5;

function elementFromTouchTarget(target) {
    if (!target) return null;
    return target.nodeType === Node.TEXT_NODE ? target.parentElement : target;
}

function isScrollableY(el) {
    if (!el || !(el instanceof HTMLElement)) return false;
    const style = window.getComputedStyle(el);
    const oy = style.overflowY;
    if (oy !== "auto" && oy !== "scroll") return false;
    return el.scrollHeight > el.clientHeight + 1;
}

/**
 * Ближайший вертикально прокручиваемый предок от точки жеста внутри панели.
 */
function findScrollableAncestorFrom(startEl, boundary) {
    if (!boundary || !startEl) return null;
    let node = elementFromTouchTarget(startEl);
    while (node && node !== boundary) {
        if (node instanceof HTMLElement && isScrollableY(node)) {
            return node;
        }
        node = node.parentElement;
    }
    if (boundary instanceof HTMLElement && isScrollableY(boundary)) {
        return boundary;
    }
    return null;
}

function onDockPanelTouchStart(e) {
    if (!uiStore.dockActiveId) return;
    const t = e?.touches?.[0];
    if (!t) return;
    touchStart.value = { x: t.clientX, y: t.clientY };
    touchStartTargetEl = e.target;
}

function onDockPanelTouchEnd(e) {
    if (!uiStore.dockActiveId) return;
    const t = e?.changedTouches?.[0];
    if (!t) return;

    const dx = t.clientX - touchStart.value.x;
    const dy = t.clientY - touchStart.value.y;
    const absDy = Math.abs(dy);
    const absDx = Math.abs(dx);

    if (dy <= SWIPE_CLOSE_MIN_DISTANCE_PX) return;
    if (absDx >= absDy * SWIPE_CLOSE_MAX_X_RATIO) return;

    const boundary = dockPanelOuterRef.value;
    const scroller = findScrollableAncestorFrom(touchStartTargetEl, boundary);
    if (scroller && scroller.scrollTop > 0) {
        return;
    }

    touchStartTargetEl = null;
    uiStore.closeDockPanel();
}

const handleDockClick = (id) => {
    uiStore.setDockActive(id);
};

watch(
    () => uiStore.dockActiveId,
    (id, prevId) => {
        if (prevId && !id) {
            popBodyScrollLock();
        } else if (!prevId && id) {
            pushBodyScrollLock();
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    if (uiStore.dockActiveId) {
        popBodyScrollLock();
    }
});

const handleEnter = (el, done) => {
    playBottomBarShow(el);
    if (done) {
        done();
    }
};

const handleLeave = (el, done) => {
    playBottomBarHide(el, done);
};
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 bottom-4 z-30">
        <Transition
            name="bottom-bar"
            @enter="handleEnter"
            @leave="handleLeave"
        >
            <div
                v-if="uiStore.showBottomNav"
                class="pointer-events-auto mx-auto max-w-7xl px-4 sm:px-6"
            >
                <!-- Контент панели дока над самим баром -->
                <Transition
                    name="dock-panel"
                    mode="out-in"
                    @enter="(el, done) => playDockContentShow(el, done)"
                    @leave="(el, done) => playDockContentHide(el, done)"
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

                <!-- Сам нижний остров -->
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
</template>

<style scoped></style>

