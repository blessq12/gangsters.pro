<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useThemeStore } from "../../stores/themeStore";
import { useAppDesign } from "../../design/useAppDesign";

const themeStore = useThemeStore();
const ds = useAppDesign().components.uiPrimitives.scrollToTop;

const visible = ref(false);

/** Показываем после заметного скролла, чтобы не шуметь у «шапки» */
const SHOW_AFTER_PX = 320;

const themeBtn = computed(() =>
    themeStore.theme === "dark" ? ds.themeDark : ds.themeLight,
);

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
            :class="[ds.btnBase, themeBtn]"
            aria-label="Наверх"
            @click="scrollToTop"
        >
            <i :class="ds.icon" aria-hidden="true" />
        </button>
    </Transition>
</template>
