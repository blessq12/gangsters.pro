<script setup>
import { computed, onMounted, onUnmounted, watch } from "vue";
import { useRoute } from "vue-router";
import { useThemeStore } from "../stores/themeStore";
import { useUserStore } from "../stores/userStore";
import { useCartStore } from "../stores/cartStore";
import { useFavoritesStore } from "../stores/favoritesStore";
import { useUiStore } from "../stores/uiStore";
import { useCatalogStore } from "../stores/catalogStore";
import { useSystemStore } from "../stores/systemStore";
import { useCompanyStore } from "../stores/companyStore";
import { useDeliveryStore } from "../stores/deliveryStore";
import { playPageEnter, playPageLeave } from "../animations/animationManager";
import { useShellIntroDockTimeline } from "../composables/layout/useShellIntroDockTimeline";
import { useAppDesign } from "../design/useAppDesign";

const sh = useAppDesign().components.layoutShell;

const themeStore = useThemeStore();
const userStore = useUserStore();
const cartStore = useCartStore();
const favoritesStore = useFavoritesStore();
const uiStore = useUiStore();
const catalogStore = useCatalogStore();
const systemStore = useSystemStore();
const companyStore = useCompanyStore();
const deliveryStore = useDeliveryStore();
const route = useRoute();

themeStore.initTheme();
userStore.initFromStorage();
cartStore.initFromStorage();
favoritesStore.initFromStorage();
uiStore.initFromStorage();
catalogStore.initFromStorage();

// На время интро нижний бар скрываем, чтобы он не подсвечивался под оверлеем
uiStore.setShowBottomNav(false);

/** Скрываем док у верха главной, чтобы не перекрывать баннеры/герой */
const TOP_BANNER_THRESHOLD = 240;
const isHome = () => route.name === "home";
const isBenefitsRoute = computed(() => route.name === "home");

function updateBottomBarFromScroll() {
    if (typeof window === "undefined") return;
    if (!bottomBarReady.value) return;

    if (!isHome()) {
        uiStore.setShowBottomNav(false);
        return;
    }

    if (cartStore.cartTotalItems > 0) {
        uiStore.setShowBottomNav(true);
        return;
    }

    const atTop = window.scrollY < TOP_BANNER_THRESHOLD;
    uiStore.setShowBottomNav(!atTop);
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
    onDockReveal: updateBottomBarFromScroll,
});

watch(
    () => route.name,
    (name) => {
        if (name !== "home") {
            uiStore.setShowBottomNav(false);
            return;
        }
        if (bottomBarReady.value) {
            updateBottomBarFromScroll();
        }
    },
);

onMounted(() => {
    if (userStore.token) {
        userStore.fetchClientProfile().catch((e) => {
            console.error("Failed to fetch client profile on mount", e);
        });
    }

    void Promise.allSettled([
        systemStore.fetchAll(),
        companyStore.fetchAll(),
        deliveryStore.fetchAll(),
    ]);
    startIntroScene();

    window.addEventListener("scroll", updateBottomBarFromScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener("scroll", updateBottomBarFromScroll);
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
        <TopBenefitsBanner
            v-if="isBenefitsRoute"
            :show-intro="showIntro"
            :bottom-bar-ready="bottomBarReady"
            variant="desktop"
        />
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
