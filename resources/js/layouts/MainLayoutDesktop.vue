<script setup>
import { onMounted, onUnmounted, watch } from "vue";
import { useRoute } from "vue-router";
import { useThemeStore } from "../stores/themeStore";
import { useUserStore } from "../stores/userStore";
import { useFavoritesStore } from "../stores/favoritesStore";
import { useUiStore } from "../stores/uiStore";
import { useCatalogStore } from "../stores/catalogStore";
import { useStorefrontStore } from "../stores/storefrontStore";
import { playPageEnter, playPageLeave } from "../animations/animationManager";
import { useShellIntroDockTimeline } from "../composables/layout/useShellIntroDockTimeline";
import { useDockScrollScale } from "../composables/ui/useDockScrollScale";
import { useAppDesign } from "../design/useAppDesign";

const sh = useAppDesign().components.layoutShell;

const themeStore = useThemeStore();
const userStore = useUserStore();
const favoritesStore = useFavoritesStore();
const uiStore = useUiStore();
const catalogStore = useCatalogStore();
const storefrontStore = useStorefrontStore();
const route = useRoute();

themeStore.initTheme();
userStore.initFromStorage();
favoritesStore.initFromStorage();
uiStore.initFromStorage();
catalogStore.initFromStorage();

uiStore.setShowBottomNav(false);

const isHome = () => route.name === "home";

function syncBottomNavForRoute() {
    if (!bottomBarReady.value) return;

    if (isHome()) {
        uiStore.setShowBottomNav(true);
        return;
    }

    uiStore.setShowBottomNav(false);
    uiStore.setDockChromeScrollScale(1);
}

const {
    introOverlayRef,
    introGlowRef,
    introLogoRef,
    mainRef,
    showIntro,
    bottomBarReady,
    startIntroScene,
    dispose: disposeIntroTimeline,
} = useShellIntroDockTimeline({
    themeStore,
    onDockReveal: syncBottomNavForRoute,
});

useDockScrollScale({
    uiStore,
    bottomBarReady,
    isHome,
});

watch(
    () => route.name,
    () => {
        syncBottomNavForRoute();
    },
);

onMounted(() => {
    if (userStore.token) {
        userStore.fetchClientProfile().catch((e) => {
            console.error("Failed to fetch client profile on mount", e);
        });
    }

    void Promise.allSettled([
        storefrontStore.fetchBootstrap(),
    ]);
    startIntroScene();
});

onUnmounted(() => {
    disposeIntroTimeline();
});
</script>

<template>
    <div
        :class="[
            sh.shared.root,
            sh.shared.typographyRoot,
            sh.shared.themeDark,
        ]"
    >
        <!-- 1) стартовый оверлей с логотипом -->
        <div
            v-if="showIntro"
            ref="introOverlayRef"
            :class="sh.shared.introOverlay"
        >
            <div
                ref="introGlowRef"
                :class="sh.shared.introRadialGlow"
                aria-hidden="true"
            />
            <img
                ref="introLogoRef"
                src="/images/load_logo.svg"
                alt="Gangsters"
                :class="[sh.desktop.introLogo, 'relative z-10']"
            />
        </div>

        <AppNavbarDesktop />

        <WorkScheduleStrip />
        <main :class="sh.shared.mainGrow">
            <div
                ref="mainRef"
                :class="sh.desktop.mainContainer"
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
        <PwaInstallBanner :visible="!showIntro" />
        <AppBottomBarDesktop v-if="bottomBarReady" />
        <GiftSelectionModal />
        <BaseModal />
    </div>
</template>

<style scoped>
.intro-radial-glow {
    --intro-radial-x: 65%;
    --intro-radial-y: 47.5%;
    background: radial-gradient(
        ellipse var(--intro-radial-x) var(--intro-radial-y) at 50% 100%,
        color-mix(in srgb, var(--app-accent) 42%, transparent) 0%,
        color-mix(in srgb, var(--app-accent) 14%, transparent) 48%,
        transparent 72%
    );
}

.app-shell.theme-dark {
    --app-canvas: #191919;
    --app-surface: #ececec;
    --app-surface-fg: #191919;
    --app-accent: #c62424;
    --app-accent-hover: #9e1d1d;
    --app-canvas-fg: #ececec;
    --app-muted: #a3a3a3;
    --app-glass-fill: rgba(0, 0, 0, 0.55);
    --app-accent-soft-bg: rgba(198, 36, 36, 0.12);
    --app-border-on-surface: color-mix(
        in srgb,
        var(--app-canvas-fg) 18%,
        transparent
    );
    background: var(--app-canvas);
}
</style>
