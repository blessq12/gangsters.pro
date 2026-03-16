<script setup>
import { onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { useThemeStore } from "../stores/themeStore";
import { useUserStore } from "../stores/userStore";
import { playIntroScene, playPageEnter, playPageLeave } from "../animations/animationManager";

const themeStore = useThemeStore();
const userStore = useUserStore();
const route = useRoute();

themeStore.initTheme();
userStore.initFromStorage();

// На время интро нижний бар всегда скрыт, чтобы он не подсвечивался под оверлеем
userStore.showBottomNav = false;

const introOverlayRef = ref(null);
const introLogoRef = ref(null);
const mainRef = ref(null);
const showIntro = ref(true);
const bottomBarReady = ref(false);

const BOTTOM_THRESHOLD = 80;
const isHome = () => route.name === "home";

function updateBottomBarFromScroll() {
    if (typeof window === "undefined") return;
    if (!bottomBarReady.value) return;

    if (!isHome()) {
        userStore.setShowBottomNav(false);
        return;
    }

    const atBottom =
        window.scrollY + window.innerHeight >=
        document.documentElement.scrollHeight - BOTTOM_THRESHOLD;

    userStore.setShowBottomNav(!atBottom);
}

watch(
    () => route.name,
    (name) => {
        if (name !== "home") {
            userStore.setShowBottomNav(false);
        }
    },
);

onMounted(() => {
    // После инициализации из localStorage подтягиваем актуальный профиль с бэка,
    // если есть токен, чтобы сразу получить свежие адреса и другие данные.
    if (userStore.token) {
        userStore.fetchClientProfile().catch((e) => {
            console.error("Failed to fetch client profile on mount", e);
        });
    }

    playIntroScene({
        introOverlay: introOverlayRef.value,
        introLogo: introLogoRef.value,
        main: mainRef.value,
        onComplete: () => {
            showIntro.value = false;
            // Шаг появления нижнего бара — только на главной, после интро (delay ~0.8)
            const stepDelay = 600;
            setTimeout(() => {
                bottomBarReady.value = true;
                if (isHome()) {
                    userStore.setShowBottomNav(true);
                    updateBottomBarFromScroll();
                }
            }, stepDelay);
        },
    });

    window.addEventListener("scroll", updateBottomBarFromScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener("scroll", updateBottomBarFromScroll);
});
</script>

<template>
    <div
        class="app-shell min-h-screen flex flex-col"
        :class="[
            themeStore.theme === 'dark'
                ? 'theme-dark text-slate-50'
                : 'theme-light text-slate-900',
        ]"
    >
        <!-- 1) стартовый оверлей с логотипом -->
        <div
            v-if="showIntro"
            ref="introOverlayRef"
            class="fixed inset-0 z-40 flex items-center justify-center pointer-events-none"
        >
            <img
                ref="introLogoRef"
                src="/images/logo.png"
                alt="Gangsters"
                class="h-40 md:h-48 w-auto"
            />
        </div>

        <AppNavbar />
        <MobileMenu />

        <main class="flex-1">
            <div
                ref="mainRef"
                class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 opacity-0"
            >
                <router-view v-slot="{ Component, route }">
                    <Transition
                        mode="out-in"
                        @enter="(el, done) => playPageEnter(el, done)"
                        @leave="(el, done) => playPageLeave(el, done)"
                    >
                        <div :key="route.fullPath">
                            <component :is="Component" />
                        </div>
                    </Transition>
                </router-view>
            </div>
        </main>

        <AppFooter />

        <AppBottomBar />

        <!-- глобальная модалка-прослойка, позже привяжем стейт -->
        <BaseModal />
    </div>
</template>

<style scoped>
.app-shell.theme-dark {
    background:
        radial-gradient(
            circle at top left,
            rgba(255, 255, 255, 0.02),
            transparent 60%
        ),
        radial-gradient(
            circle at bottom right,
            rgba(255, 255, 255, 0.015),
            transparent 60%
        ),
        repeating-linear-gradient(
            135deg,
            rgba(255, 255, 255, 0.01) 0,
            rgba(255, 255, 255, 0.01) 2px,
            transparent 2px,
            transparent 6px
        ),
        #1f1f23;
}

.app-shell.theme-light {
    background: #f9fafb;
}
</style>
