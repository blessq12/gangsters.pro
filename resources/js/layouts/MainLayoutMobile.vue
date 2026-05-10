<script setup>
import { onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { useThemeStore } from "../stores/themeStore";
import { useUserStore } from "../stores/userStore";
import { useCartStore } from "../stores/cartStore";
import { useFavoritesStore } from "../stores/favoritesStore";
import { useUiStore } from "../stores/uiStore";
import { useCatalogStore } from "../stores/catalogStore";
import { useSystemStore } from "../stores/systemStore";
import { playIntroScene, playPageEnter, playPageLeave } from "../animations/animationManager";
import { useMobileDockScrollSuppression } from "../composables/ui/useMobileDockScrollSuppression";
import { useAppDesign } from "../design/useAppDesign";

const sh = useAppDesign().components.layoutShell;

const themeStore = useThemeStore();
const userStore = useUserStore();
const cartStore = useCartStore();
const favoritesStore = useFavoritesStore();
const uiStore = useUiStore();
const catalogStore = useCatalogStore();
const systemStore = useSystemStore();
const route = useRoute();

themeStore.initTheme();
userStore.initFromStorage();
cartStore.initFromStorage();
favoritesStore.initFromStorage();
uiStore.initFromStorage();
catalogStore.initFromStorage();

// На время интро нижний бар скрываем, чтобы он не подсвечивался под оверлеем
uiStore.setShowBottomNav(false);

const introOverlayRef = ref(null);
const introLogoRef = ref(null);
const mainRef = ref(null);
const showIntro = ref(true);
const bottomBarReady = ref(false);

const isHome = () => route.name === "home";

useMobileDockScrollSuppression({
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
        if (bottomBarReady.value) {
            uiStore.setShowBottomNav(true);
        }
    },
);

onMounted(() => {
    // После инициализации из localStorage подтягиваем актуальный профиль с бэка
    if (userStore.token) {
        userStore.fetchClientProfile().catch((e) => {
            console.error("Failed to fetch client profile on mount", e);
        });
    }

    void systemStore.fetchAll();

    playIntroScene({
        introOverlay: introOverlayRef.value,
        introLogo: introLogoRef.value,
        main: mainRef.value,
        onComplete: () => {
            showIntro.value = false;
            const stepDelay = 600;
            setTimeout(() => {
                bottomBarReady.value = true;
                if (isHome()) {
                    uiStore.setShowBottomNav(true);
                }
            }, stepDelay);
        },
    });
});
</script>

<template>
    <div
        :class="[
            sh.shared.root,
            themeStore.theme === 'dark'
                ? sh.shared.themeDark
                : sh.shared.themeLight,
        ]"
    >
        <!-- 1) стартовый оверлей с логотипом -->
        <div
            v-if="showIntro"
            ref="introOverlayRef"
            :class="sh.shared.introOverlay"
        >
            <img
                ref="introLogoRef"
                src="/images/logo.png"
                alt="Gangsters"
                :class="sh.mobile.introLogo"
            />
        </div>

        <AppNavbarMobile />
        <MobileMenu />

        <WorkScheduleStrip />

        <main :class="sh.shared.mainGrow">
            <div
                ref="mainRef"
                :class="sh.mobile.mainContainer"
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
        <AppBottomBarMobile />
        <BaseModal />
    </div>
</template>

<style scoped>
.app-shell.theme-dark {
    --app-canvas: #191919;
    --app-surface: #ececec;
    --app-surface-fg: #191919;
    --app-accent: #c62424;
    --app-accent-hover: #9e1d1d;
    --app-canvas-fg: #e8e8e8;
    --app-muted: #a3a3a3;
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
        var(--app-canvas);
}

.app-shell.theme-light {
    --app-canvas: #ececec;
    --app-surface: #fafafa;
    --app-surface-fg: #191919;
    --app-accent: #c62424;
    --app-accent-hover: #9e1d1d;
    --app-canvas-fg: #191919;
    --app-muted: #525252;
    background: var(--app-canvas);
}
</style>

