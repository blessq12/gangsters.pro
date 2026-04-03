<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import { useThemeStore } from "../../stores/themeStore";

const themeStore = useThemeStore();

const visible = ref(false);

/** Показываем после заметного скролла, чтобы не шуметь у «шапки» */
const SHOW_AFTER_PX = 320;

function syncVisibility() {
    if (typeof window === "undefined") return;
    visible.value = window.scrollY > SHOW_AFTER_PX;
}

function scrollToTop() {
    if (typeof window === "undefined") return;
    window.scrollTo({ top: 0, behavior: "smooth" });
}

onMounted(() => {
    syncVisibility();
    window.addEventListener("scroll", syncVisibility, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener("scroll", syncVisibility);
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <button
            v-show="visible"
            type="button"
            class="fixed z-[32] flex h-12 w-12 items-center justify-center rounded-full border backdrop-blur-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400/70 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent md:bottom-8 md:right-8 max-md:bottom-32 max-md:right-4"
            :class="
                themeStore.theme === 'dark'
                    ? 'border-white/10 bg-[rgba(255,255,255,0.06)] text-amber-300 hover:border-amber-400/40 hover:bg-[rgba(255,255,255,0.1)]'
                    : 'border-slate-200/90 bg-white/95 text-amber-600 shadow-md shadow-slate-300/30 hover:border-amber-400/50 hover:bg-amber-50/90'
            "
            aria-label="Наверх"
            @click="scrollToTop"
        >
            <i class="mdi mdi-chevron-up text-2xl leading-none" aria-hidden="true" />
        </button>
    </Transition>
</template>
