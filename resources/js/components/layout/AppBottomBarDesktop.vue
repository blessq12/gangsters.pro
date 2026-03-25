<script setup>
import { useUiStore } from "../../stores/uiStore";
import { useCartStore } from "../../stores/cartStore";
import {
    playBottomBarShow,
    playBottomBarHide,
    playDockContentShow,
    playDockContentHide,
} from "../../animations/animationManager";
import { useBottomDockState } from "../../composables/ui/useBottomDockState";
import { dockItems as dockItemsDesktop } from "../../dock/dockRegistryDesktop";

const uiStore = useUiStore();
const cartStore = useCartStore();

const { activeDockItem, getBadge, dockItems } = useBottomDockState({
    uiStore,
    cartStore,
    dockItems: dockItemsDesktop,
});

const handleDockClick = (id) => {
    uiStore.setDockActive(id);
};

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
    <div class="pointer-events-none fixed left-0 top-0 z-30">
        <Transition
            name="bottom-bar"
            @enter="handleEnter"
            @leave="handleLeave"
        >
            <div
                v-if="uiStore.showBottomNav"
                class="pointer-events-auto fixed left-6 top-24 z-30 flex items-start gap-4 rounded-2xl border border-amber-400/30 bg-[rgba(0,0,0,0.65)] px-3 py-3 backdrop-blur"
            >
                <!-- Сайдбар кнопок дока -->
                <div class="flex flex-col items-center gap-2">
                    <button
                        v-for="item in dockItems"
                        :key="item.id"
                        type="button"
                        class="group flex flex-col items-center gap-2 transition-colors"
                        @click="handleDockClick(item.id)"
                        :title="item.label"
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

                <!-- Контент дока -->
                <Transition
                    name="dock-panel"
                    mode="out-in"
                    @enter="(el, done) => playDockContentShow(el, done)"
                    @leave="(el, done) => playDockContentHide(el, done)"
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

