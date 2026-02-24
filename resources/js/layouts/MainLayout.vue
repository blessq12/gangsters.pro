<script setup>
import { onMounted, ref } from "vue";
import { useThemeStore } from "../stores/themeStore";
import { playIntroScene, playPageEnter, playPageLeave } from "../animations/animationManager";

const themeStore = useThemeStore();
themeStore.initTheme();

const introOverlayRef = ref(null);
const introLogoRef = ref(null);
const mainRef = ref(null);
const showIntro = ref(true);

onMounted(() => {
    playIntroScene({
        introOverlay: introOverlayRef.value,
        introLogo: introLogoRef.value,
        main: mainRef.value,
        onComplete: () => {
            showIntro.value = false;
        },
    });
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
