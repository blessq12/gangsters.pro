<script setup>
import { onMounted, onUnmounted, watch } from "vue";
import { useRoute } from "vue-router";
import { playPageEnter, playPageLeave } from "../animations/animationManager";
import { useShellIntroDockTimeline } from "../composables/layout/useShellIntroDockTimeline";
import { useDockScrollScale } from "../composables/ui/useDockScrollScale";
import { useAppDesign } from "../design/useAppDesign";
import { useShellStore } from "../stores/shellStore";
import { useContentStore } from "../stores/contentStore";
import { useCatalogStore } from "../stores/catalogStore";
import { useThemeStore } from "../stores/themeStore";
import { useUiStore } from "../stores/uiStore";
import { useUserStore } from "../stores/userStore";
import { scheduleIdlePrefetchDockPanels } from "../features/shell/prefetchDockPanels";

const sh = useAppDesign().components.layoutShell;

const themeStore = useThemeStore();
const userStore = useUserStore();
const uiStore = useUiStore();
const catalogStore = useCatalogStore();
const contentStore = useContentStore();
const shellStore = useShellStore();
const route = useRoute();

themeStore.initTheme();

uiStore.setShowBottomNav(false);

const isHome = () => route.name === "home";

const {
    introOverlayRef,
    introGlowRef,
    introLogoRef,
    mainRef,
    showIntro,
    bottomBarReady,
    presentShellContent,
    dispose: disposeIntroTimeline,
} = useShellIntroDockTimeline({
    themeStore,
    onDockReveal() {
        if (isHome()) {
            uiStore.setShowBottomNav(true);
        }
    },
});

useDockScrollScale({
    uiStore,
    bottomBarReady,
    isHome,
});

watch(
    () => route.name,
    (name) => {
        if (name !== "home") {
            uiStore.setShowBottomNav(false);
            return;
        }
        if (shellStore.dockReady) {
            uiStore.setShowBottomNav(true);
        }
    },
);

watch(
    () => catalogStore.hasLoaded,
    (loaded) => {
        if (!loaded || !userStore.token) {
            return;
        }

        userStore.fetchClientProfile().catch((e) => {
            console.error("Failed to fetch client profile after catalog load", e);
        });
    },
    { immediate: true },
);

onMounted(() => {
    void (async () => {
        shellStore.markDataLoading();

        try {
            const tasks = [];

            if (!catalogStore.hasLoaded && !catalogStore.loading) {
                tasks.push(catalogStore.fetchAll());
            }

            if (!contentStore.loaded) {
                tasks.push(contentStore.fetchBootstrap());
            }

            await Promise.all(tasks);
        } finally {
            shellStore.markDataReady();
            scheduleIdlePrefetchDockPanels();
        }
    })();

    void presentShellContent();
});

onUnmounted(() => {
    disposeIntroTimeline();
});
</script>

<template>
    <div
        :class="[sh.shared.root, sh.shared.typographyRoot, sh.shared.themeDark]"
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
                :class="[sh.mobile.introLogo, 'relative z-10']"
            />
        </div>

        <AppNavbarMobile />
        <MobileMenu />
        <main :class="sh.shared.mainGrow">
            <div ref="mainRef" :class="sh.mobile.mainContainer">
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

        <AppBottomBar v-if="bottomBarReady" />
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
