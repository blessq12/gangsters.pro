<script setup>
import { ref } from "vue";
import { useRoute } from "vue-router";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";
import { useUiStore } from "../../stores/uiStore";

const route = useRoute();
const uiStore = useUiStore();

const containerRef = ref(null);

const isActive = (name) => route.name === name;

useEnterSlide(containerRef);

const toggleMobileMenu = () => {
    uiStore.toggleMobileMenu();
};
</script>

<template>
    <header class="pt-4">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                ref="containerRef"
                class="flex items-center justify-between gap-4 rounded-2xl border border-amber-400/40 bg-[rgba(255,255,255,0.04)]/80 px-4 sm:px-6 lg:px-8 py-3.5 shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur"
            >
                <!-- Левая зона: баланс с бургером / десктопное меню -->
                <div class="flex min-w-0 items-center gap-2 sm:gap-3 w-24 sm:w-auto">
                    <div
                        class="w-10 shrink-0 md:hidden"
                        aria-hidden="true"
                    />

                    <div
                        class="hidden min-w-0 items-center md:flex"
                    >
                        <nav
                            class="flex items-center gap-4 text-sm font-medium tracking-wide"
                        >
                        <RouterLink
                            :to="{ name: 'home' }"
                            :class="[
                                'transition-colors duration-200',
                                isActive('home')
                                    ? 'text-amber-300'
                                    : 'text-slate-200/80 hover:text-white',
                            ]"
                        >
                            Главная
                        </RouterLink>
                        <RouterLink
                            :to="{ name: 'about' }"
                            :class="[
                                'transition-colors duration-200',
                                isActive('about')
                                    ? 'text-amber-300'
                                    : 'text-slate-200/80 hover:text-white',
                            ]"
                        >
                            О компании
                        </RouterLink>
                        </nav>
                    </div>
                </div>

                <!-- Центр: логотип всегда по центру -->
                <div class="text-lg font-semibold flex-1 flex justify-center">
                    <RouterLink
                        :to="{ name: 'home' }"
                        class="inline-flex items-center justify-center group"
                    >
                        <img
                            src="/images/logo.png"
                            alt="Gangsters"
                            class="h-9 min-h-9 sm:h-10 sm:min-h-10 md:h-11 md:min-h-11 w-auto min-w-[7rem] max-w-full mx-auto object-contain drop-shadow-[0_0_15px_rgba(251,191,36,0.45)] group-hover:scale-105 group-hover:drop-shadow-[0_0_22px_rgba(251,191,36,0.7)] transition-transform duration-200"
                        />
                    </RouterLink>
                </div>

                <!-- Правая зона: бургер / десктопное меню -->
                <div
                    class="flex items-center justify-end gap-2 sm:gap-3 w-24 sm:w-auto"
                >
                    <!-- mobile: бургер -->
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-black/70 text-slate-200 transition-colors md:hidden"
                        :class="
                            uiStore.isMobileMenuOpen
                                ? 'border-amber-400/70 text-amber-200'
                                : 'hover:border-amber-400/50 hover:text-amber-200'
                        "
                        @click="toggleMobileMenu"
                    >
                        <i
                            :class="[
                                'mdi text-lg',
                                uiStore.isMobileMenuOpen ? 'mdi-close' : 'mdi-menu',
                            ]"
                        />
                    </button>

                    <!-- desktop: правое меню -->
                    <nav
                        class="hidden md:flex items-center gap-4 text-sm justify-end font-medium tracking-wide"
                    >
                        <RouterLink
                            :to="{ name: 'delivery' }"
                            :class="[
                                'transition-colors duration-200',
                                isActive('delivery')
                                    ? 'text-amber-300'
                                    : 'text-slate-200/80 hover:text-white',
                            ]"
                        >
                            Оплата и доставка
                        </RouterLink>
                        <RouterLink
                            :to="{ name: 'contacts' }"
                            :class="[
                                'transition-colors duration-200',
                                isActive('contacts')
                                    ? 'text-amber-300'
                                    : 'text-slate-200/80 hover:text-white',
                            ]"
                        >
                            Контакты
                        </RouterLink>
                    </nav>
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped></style>
