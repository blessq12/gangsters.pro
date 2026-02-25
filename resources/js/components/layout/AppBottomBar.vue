<script setup>
import { computed, ref } from "vue";
import { useUserStore } from "../../stores/userStore";
import {
    playBottomBarShow,
    playBottomBarHide,
    playDockContentShow,
    playDockContentHide,
} from "../../animations/animationManager";
import { dockItems } from "../../dock/dockRegistry";

const userStore = useUserStore();
const barRef = ref(null);
const panelRef = ref(null);

const hasProduct = computed(
    () => !!userStore.catalogSelectedProduct && !!userStore.catalogSelectedProduct.name,
);

const activeDockItem = computed(() =>
    dockItems.find((item) => item.id === userStore.dockActiveId) || null,
);

const title = computed(() => {
    if (activeDockItem.value) {
        return activeDockItem.value.label;
    }

    if (hasProduct.value) {
        return userStore.catalogSelectedProduct.name;
    }

    return "Выберите блюдо из меню, чтобы собрать заказ.";
});

const subtitle = computed(() => {
    if (activeDockItem.value) {
        return "Нажмите ещё раз по иконке дока, чтобы свернуть панель.";
    }

    const p = userStore.catalogSelectedProduct;
    if (!p) return "Когда выберешь позицию, сможешь оформить заказ здесь.";

    const parts = [];
    if (p.weight) parts.push(`${p.weight} г`);
    if (p.price) parts.push(`${p.price} ₽`);
    return parts.length ? parts.join(" • ") : "Проверь состав и нажми оформить.";
});

const handleCheckout = () => {
    // Пока только подсвечиваем, что панель живая.
    // Позже сюда прикрутим переход в корзину / оформление.
    userStore.setShowBottomNav(true);
};

const handleDockClick = (id) => {
    userStore.setDockActive(id);
};

const getBadge = (id) => {
    return userStore.dockBadges?.[id] ?? 0;
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
    <div class="pointer-events-none fixed inset-x-0 bottom-4 z-30">
        <Transition
            name="bottom-bar"
            @enter="handleEnter"
            @leave="handleLeave"
        >
            <div
                v-if="userStore.showBottomNav"
                ref="barRef"
                class="pointer-events-auto mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
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
                        :key="activeDockItem.id"
                        ref="panelRef"
                        class="mb-3 mx-auto w-full max-w-4xl"
                    >
                        <component :is="activeDockItem.content" />
                    </div>
                </Transition>

                <!-- Сам нижний остров -->
                <div
                    class="mx-auto flex max-w-3xl items-center justify-center gap-4 rounded-2xl border border-amber-400/30 bg-[rgba(255,255,255,0.035)] px-4 sm:px-6 lg:px-8 py-3 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur"
                >
                    <div class="flex items-center gap-3 sm:gap-4">
                        <button
                            v-for="item in dockItems"
                            :key="item.id"
                            type="button"
                            class="group flex flex-col items-center gap-1 text-[11px] sm:text-xs transition-colors"
                            @click="handleDockClick(item.id)"
                        >
                            <span
                                class="relative flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full border transition-colors"
                                :class="
                                    userStore.dockActiveId === item.id
                                        ? 'border-amber-400/70 bg-black/80 text-amber-200 shadow-[0_0_18px_rgba(251,191,36,0.7)]'
                                        : 'border-white/20 bg-black/70 text-slate-200 group-hover:border-amber-400/50 group-hover:text-amber-200'
                                "
                            >
                                <i :class="['mdi', item.iconClass, 'text-base sm:text-lg']" />
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
                                    userStore.dockActiveId === item.id
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

