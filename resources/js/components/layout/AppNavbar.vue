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
</script>

<template>
    <header class="pt-4">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                ref="containerRef"
                class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-[rgba(255,255,255,0.03)] px-4 sm:px-6 lg:px-8 py-3"
            >
                <nav class="flex items-center gap-4 text-sm">
                    <RouterLink
                        :to="{ name: 'home' }"
                        :class="[
                            'transition-colors',
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
                            'transition-colors',
                            isActive('about')
                                ? 'text-amber-300'
                                : 'text-slate-200/80 hover:text-white',
                        ]"
                    >
                        О компании
                    </RouterLink>
                </nav>

                <div class="text-lg font-semibold text-center flex-1">
                    <RouterLink :to="{ name: 'home' }">Gangsters</RouterLink>
                </div>

                <nav class="flex items-center gap-4 text-sm justify-end">
                    <RouterLink
                        :to="{ name: 'delivery' }"
                        :class="[
                            'transition-colors',
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
                            'transition-colors',
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
    </header>
</template>

<style scoped></style>
