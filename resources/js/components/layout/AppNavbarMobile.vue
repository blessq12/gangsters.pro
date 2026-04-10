<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import { playMobileNavbarLogoPulse } from "../../animations/animationManager";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";
import { useCompanyOpenStatus } from "../../composables/system/useCompanyOpenStatus";
import { useUiStore } from "../../stores/uiStore";
import { useSystemStore } from "../../stores/systemStore";

const uiStore = useUiStore();
const systemStore = useSystemStore();

const containerRef = ref(null);
const logoPulseRef = ref(null);

/** @type {{ kill: () => void } | null} */
let logoPulseControl = null;

useEnterSlide(containerRef, {
    y: -40,
    delay: 0.8,
});

const { openNow } = useCompanyOpenStatus(() => systemStore.company);

onMounted(() => {
    if (!systemStore.company && !systemStore.loadingCompany) {
        void systemStore.fetchCompany();
    }

    void nextTick().then(() => {
        if (logoPulseRef.value) {
            logoPulseControl?.kill();
            logoPulseControl = playMobileNavbarLogoPulse(logoPulseRef.value);
        }
    });
});

onUnmounted(() => {
    logoPulseControl?.kill();
    logoPulseControl = null;
});

const toggleMobileMenu = () => {
    uiStore.toggleMobileMenu();
};
</script>

<template>
    <header class="pt-4">
        <div class="mx-auto max-w-7xl px-4">
            <div
                ref="containerRef"
                class="flex items-center justify-between gap-4 rounded-2xl border border-amber-400/40 bg-[rgba(255,255,255,0.06)] px-4 py-3.5 shadow-[0_0_25px_rgba(0,0,0,0.7)]"
            >
                <div
                    class="flex w-10 shrink-0 items-center justify-start"
                    :title="openNow ? 'Открыто' : 'Закрыто'"
                >
                    <span
                        class="h-3 w-3 rounded-full"
                        :class="
                            openNow
                                ? 'bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.7)]'
                                : 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.7)]'
                        "
                        role="img"
                        :aria-label="openNow ? 'Открыто' : 'Закрыто'"
                    />
                </div>

                <div class="text-lg font-semibold flex-1 flex justify-center">
                    <RouterLink
                        :to="{ name: 'home' }"
                        class="inline-flex items-center justify-center group"
                    >
                        <span
                            ref="logoPulseRef"
                            class="inline-flex origin-center will-change-transform"
                        >
                            <img
                                src="/images/logo.png"
                                alt="Gangsters"
                                class="h-9 min-h-9 w-auto min-w-[7rem] max-w-full mx-auto object-contain transition-transform duration-200 group-hover:scale-105"
                            />
                        </span>
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

