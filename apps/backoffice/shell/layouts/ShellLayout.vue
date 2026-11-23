<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "../store/authStore";
import ShellSidebar from "../components/ShellSidebar.vue";
import ShellTopbar from "../components/ShellTopbar.vue";
import ToastHost from "../components/ToastHost.vue";

const COLLAPSE_KEY = "bo-sidebar-collapsed";
const DESKTOP_QUERY = "(min-width: 1024px)";

const route = useRoute();
const auth = useAuthStore();

const collapsed = ref(readCollapsed());
const mobileOpen = ref(false);
const isDesktop = ref(true);

/** @type {MediaQueryList|null} */
let media = null;

const showChrome = computed(() => !route.meta.guest && auth.isAuthenticated);

/** Курьер — отдельный mobile PWA-контур без sidebar/topbar. */
const isCourierApp = computed(() => showChrome.value && auth.role === "courier");

function readCollapsed() {
    try {
        return localStorage.getItem(COLLAPSE_KEY) === "1";
    } catch {
        return false;
    }
}

function syncMedia() {
    isDesktop.value = media ? media.matches : true;
    if (isDesktop.value) {
        mobileOpen.value = false;
    }
}

function toggleSidebar() {
    if (isDesktop.value) {
        collapsed.value = !collapsed.value;
        return;
    }
    mobileOpen.value = !mobileOpen.value;
}

watch(collapsed, (value) => {
    try {
        localStorage.setItem(COLLAPSE_KEY, value ? "1" : "0");
    } catch {
        // localStorage недоступен — состояние живёт до перезагрузки
    }
});

watch(() => route.fullPath, () => {
    mobileOpen.value = false;
});

onMounted(() => {
    media = window.matchMedia(DESKTOP_QUERY);
    syncMedia();
    media.addEventListener("change", syncMedia);
});

onUnmounted(() => {
    media?.removeEventListener("change", syncMedia);
});
</script>

<template>
    <div class="min-h-screen bg-bo-canvas text-bo-fg">
        <main
            v-if="!showChrome"
            class="flex min-h-screen items-center justify-center p-4"
        >
            <RouterView />
        </main>

        <div
            v-else-if="isCourierApp"
            class="flex h-dvh flex-col overflow-hidden"
        >
            <RouterView />
        </div>

        <div
            v-else
            class="flex h-screen overflow-hidden"
        >
            <ShellSidebar
                :collapsed="collapsed && isDesktop"
                :mobile-open="mobileOpen"
                @close="mobileOpen = false"
            />

            <div class="flex min-w-0 flex-1 flex-col overflow-auto">
                <ShellTopbar
                    :collapsed="collapsed"
                    :is-desktop="isDesktop"
                    :mobile-open="mobileOpen"
                    @toggle-sidebar="toggleSidebar"
                />
                <main class="flex flex-1 flex-col">
                    <RouterView />
                </main>
            </div>
        </div>

        <ToastHost />
    </div>
</template>
