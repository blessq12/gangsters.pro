<script setup>
import { onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { useThemeStore } from "../stores/themeStore";
import { useUserStore } from "../stores/userStore";
import { useCartStore } from "../stores/cartStore";
import { useFavoritesStore } from "../stores/favoritesStore";
import { useUiStore } from "../stores/uiStore";
import { useCatalogStore } from "../stores/catalogStore";
import { useSystemStore } from "../stores/systemStore";
import { playIntroScene, playPageEnter, playPageLeave } from "../animations/animationManager";
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

/** Скрываем док у верха главной, чтобы не перекрывать баннеры/герой */
const TOP_BANNER_THRESHOLD = 240;
const isHome = () => route.name === "home";

function updateBottomBarFromScroll() {
    if (typeof window === "undefined") return;
    if (!bottomBarReady.value) return;

    if (!isHome()) {
        uiStore.setShowBottomNav(false);
        return;
    }

    const atTop = window.scrollY < TOP_BANNER_THRESHOLD;
    uiStore.setShowBottomNav(!atTop);
}

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
                updateBottomBarFromScroll();
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
        :class="[
            sh.shared.root,
            sh.shared.typographyRoot,
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
                src="/images/load_logo.svg"
                alt="Gangsters"
                :class="sh.desktop.introLogo"
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
        <AppBottomBarDesktop />
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
    --app-canvas-fg: #ececec;
    --app-muted: #a3a3a3;
    background: var(--app-canvas);
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

