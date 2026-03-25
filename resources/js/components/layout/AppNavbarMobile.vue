<script setup>
import { ref } from "vue";
import { useRoute } from "vue-router";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";
import { useUiStore } from "../../stores/uiStore";

const route = useRoute();
const uiStore = useUiStore();

const containerRef = ref(null);

const isActive = (name) => route.name === name;

useEnterSlide(containerRef, {
    y: -40,
    delay: 0.8,
});

const toggleMobileMenu = () => {
    uiStore.toggleMobileMenu();
};

// true = работаем (зелёный), false = закрыты (красный)
const isStoreOpen = ref(true);
</script>

<template>
    <header class="pt-4">
        <div class="mx-auto max-w-7xl px-4">
            <div
                ref="containerRef"
                class="flex items-center justify-between gap-4 rounded-2xl border border-amber-400/40 bg-[rgba(255,255,255,0.04)]/80 px-4 py-3.5 shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="h-3 w-3 shrink-0 rounded-full"
                        :class="
                            isStoreOpen
                                ? 'bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.7)]'
                                : 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.7)]'
                        "
                        :title="isStoreOpen ? 'Работаем' : 'Закрыты'"
                    />
                </div>

                <div class="text-lg font-semibold flex-1 flex justify-center">
                    <RouterLink
                        :to="{ name: 'home' }"
                        class="inline-flex items-center justify-center group"
                    >
                        <img
                            src="/images/logo-text.png"
                            alt="Gangsters"
                            class="h-9 min-h-9 w-auto min-w-[7rem] max-w-full mx-auto object-contain drop-shadow-[0_0_15px_rgba(251,191,36,0.45)] group-hover:scale-105 group-hover:drop-shadow-[0_0_22px_rgba(251,191,36,0.7)] transition-transform duration-200"
                        />
                    </RouterLink>
                </div>

                <div class="flex items-center justify-end">
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-black/70 text-slate-200 transition-colors"
                        :class="
                            uiStore.isMobileMenuOpen
                                ? 'border-amber-400/70 text-amber-200'
                                : 'hover:border-amber-400/50 hover:text-amber-200'
                        "
                        @click="toggleMobileMenu"
                    >
                        <i
                            :class="[
                                'mdi text-lg',
                                uiStore.isMobileMenuOpen ? 'mdi-close' : 'mdi-menu',
                            ]"
                        />
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped></style>

