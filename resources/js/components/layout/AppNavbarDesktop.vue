<script setup>
import { ref } from "vue";
import { useRoute } from "vue-router";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";

const route = useRoute();
const containerRef = ref(null);

const isActive = (name) => route.name === name;

useEnterSlide(containerRef, {
    y: -40,
    delay: 0.8,
});

// true = работаем (зелёный), false = закрыты (красный). Позже брать из API/стора.
const isStoreOpen = ref(true);
</script>

<template>
    <header class="pt-4">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div
                ref="containerRef"
                class="flex items-center justify-between gap-4 rounded-2xl border border-amber-400/40 bg-[rgba(255,255,255,0.04)]/80 px-4 sm:px-6 lg:px-8 py-3.5 shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur"
            >
                <div class="flex items-center gap-3 w-24 sm:w-auto">
                    <span
                        class="h-3 w-3 shrink-0 rounded-full"
                        :class="
                            isStoreOpen
                                ? 'bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.7)]'
                                : 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.7)]'
                        "
                        :title="isStoreOpen ? 'Работаем' : 'Закрыты'"
                    />

                    <nav
                        class="flex items-center gap-4 text-sm font-medium tracking-wide ml-2"
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

                <div class="text-lg font-semibold flex-1 flex justify-center">
                    <RouterLink
                        :to="{ name: 'home' }"
                        class="inline-flex items-center justify-center group"
                    >
                        <img
                            src="/images/logo.png"
                            alt="Gangsters"
                            class="h-10 min-h-10 w-auto min-w-[7rem] max-w-full mx-auto object-contain drop-shadow-[0_0_15px_rgba(251,191,36,0.45)] group-hover:scale-105 group-hover:drop-shadow-[0_0_22px_rgba(251,191,36,0.7)] transition-transform duration-200"
                        />
                    </RouterLink>
                </div>

                <div class="flex items-center justify-end gap-4 w-48">
                    <nav class="flex items-center gap-4 text-sm font-medium tracking-wide">
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

