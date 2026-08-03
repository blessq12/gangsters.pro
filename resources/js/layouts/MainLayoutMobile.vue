<script setup>
import { onMounted, onUnmounted, watch } from "vue";
import { useRoute } from "vue-router";
import { playPageEnter, playPageLeave } from "../animations/animationManager";
import { useShellIntroDockTimeline } from "../modules/shell/application/dockIntro";
import { useDockScrollScale } from "../modules/shell/application/dockUi";
import { useAppDesign } from "../design/useAppDesign";
import { useShellStore } from "../modules/shell/store/shellStore";
import { useContentStore } from "../modules/content/store";
import { useCatalogStore } from "../modules/catalog/store";
import { useThemeStore } from "../modules/shell/store/themeStore";
import { useUiStore } from "../modules/shell/store/uiStore";
import { useUserStore } from "../modules/client/store/userStore";
import { scheduleIdlePrefetchDockPanels } from "../modules/shell/application/useAppBootstrap";

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
            >
                <div
                    :class="sh.shared.introPoliceSpotRed"
                    data-intro-glow="red"
                />
                <div
                    :class="sh.shared.introPoliceSpotBlue"
                    data-intro-glow="blue"
                />
            </div>
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
.intro-police-glow {
    opacity: 0;
}

.intro-police-glow__spot {
    pointer-events: none;
    position: absolute;
    inset: -35%;
    mix-blend-mode: screen;
    will-change: opacity, --intro-glow-x, --intro-glow-y;
    background: radial-gradient(
        ellipse 72% 64% at var(--intro-glow-x, 50%) var(--intro-glow-y, 50%),
        var(--intro-glow-color) 0%,
        color-mix(in srgb, var(--intro-glow-color) 45%, transparent) 28%,
        transparent 72%
    );
}

.intro-police-glow__spot--red {
    --intro-glow-x: 30%;
    --intro-glow-y: 50%;
    --intro-glow-color: rgba(239, 68, 68, 0.48);
    animation: intro-police-strobe-red 0.95s ease-in-out infinite;
}

.intro-police-glow__spot--blue {
    --intro-glow-x: 70%;
    --intro-glow-y: 48%;
    --intro-glow-color: rgba(59, 130, 246, 0.42);
    animation: intro-police-strobe-blue 0.95s ease-in-out infinite;
}

@keyframes intro-police-strobe-red {
    0%,
    100% {
        opacity: 0.12;
    }
    12% {
        opacity: 0.58;
    }
    24%,
    48% {
        opacity: 0.14;
    }
    60% {
        opacity: 0.45;
    }
    72% {
        opacity: 0.12;
    }
}

@keyframes intro-police-strobe-blue {
    0%,
    100% {
        opacity: 0.12;
    }
    12%,
    36% {
        opacity: 0.12;
    }
    48% {
        opacity: 0.58;
    }
    60% {
        opacity: 0.16;
    }
    72% {
        opacity: 0.48;
    }
    84% {
        opacity: 0.12;
    }
}

@media (prefers-reduced-motion: reduce) {
    .intro-police-glow__spot--red,
    .intro-police-glow__spot--blue {
        animation: none;
        opacity: 0.35;
    }
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
