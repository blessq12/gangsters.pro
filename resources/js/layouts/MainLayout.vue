<script setup>
import { onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { useThemeStore } from "../stores/themeStore";
import { useUserStore } from "../stores/userStore";
import { useUiStore } from "../stores/uiStore";
import { useSystemStore } from "../stores/systemStore";
import { playIntroScene, playPageEnter, playPageLeave } from "../animations/animationManager";
import { useAppBootstrap } from "../processes/bootstrap/useAppBootstrap";
import { useAppDesign } from "../design/useAppDesign";

const sh = useAppDesign().components.layoutShell;

const themeStore = useThemeStore();
const userStore = useUserStore();
const uiStore = useUiStore();
const systemStore = useSystemStore();
const route = useRoute();

useAppBootstrap();

// На время интро нижний бар всегда скрыт, чтобы он не подсвечивался под оверлеем
uiStore.setShowBottomNav(false);

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
        uiStore.setShowBottomNav(false);
        return;
    }

    const atBottom =
        window.scrollY + window.innerHeight >=
        document.documentElement.scrollHeight - BOTTOM_THRESHOLD;

    uiStore.setShowBottomNav(!atBottom);
}

watch(
    () => route.name,
    (name) => {
        if (name !== "home") {
            uiStore.setShowBottomNav(false);
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

    void systemStore.fetchAll();

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
                    uiStore.setShowBottomNav(true);
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
                :class="sh.core.introLogo"
            />
        </div>

        <AppNavbar />
        <MobileMenu />

        <WorkScheduleStrip />

        <main :class="sh.shared.mainGrow">
            <div
                ref="mainRef"
                :class="sh.core.mainContainer"
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
