<script setup>
import { ref } from "vue";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";

const year = new Date().getFullYear();

const showPrivacy = ref(false);
const showRules = ref(false);
const showAgreement = ref(false);

const containerRef = ref(null);

useEnterSlide(containerRef, {
    y: 40,
    delay: 1.2,
});
</script>

<template>
    <footer class="mt-10 pb-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                ref="containerRef"
                class="flex items-center justify-between gap-4 rounded-2xl border border-amber-400/30 bg-[rgba(255,255,255,0.035)] px-4 sm:px-6 lg:px-8 py-4 flex-wrap text-sm shadow-[0_0_22px_rgba(0,0,0,0.65)] backdrop-blur"
            >
                <div class="flex flex-wrap gap-3 text-slate-200/85">
                    <RouterLink
                        :to="{ name: 'about' }"
                        class="hover:text-amber-300 transition-colors duration-200"
                    >
                        О компании
                    </RouterLink>
                    <RouterLink
                        :to="{ name: 'delivery' }"
                        class="hover:text-amber-300 transition-colors duration-200"
                    >
                        Оплата и доставка
                    </RouterLink>
                    <RouterLink
                        :to="{ name: 'contacts' }"
                        class="hover:text-amber-300 transition-colors duration-200"
                    >
                        Контакты
                    </RouterLink>
                </div>
                <div class="flex flex-wrap gap-3 text-slate-300/85">
                    <button
                        type="button"
                        class="hover:text-amber-300 transition-colors duration-200"
                        @click="showPrivacy = true"
                    >
                        Политика конфиденциальности
                    </button>
                    <button
                        type="button"
                        class="hover:text-amber-300 transition-colors duration-200"
                        @click="showRules = true"
                    >
                        Правила использования
                    </button>
                    <button
                        type="button"
                        class="hover:text-amber-300 transition-colors duration-200"
                        @click="showAgreement = true"
                    >
                        Пользовательское соглашение
                    </button>
                </div>
                <p class="opacity-70 text-slate-300/80 text-xs sm:text-sm">
                    © Gangsters, {{ year }}
                </p>
            </div>
        </div>

        <BaseModal v-model="showPrivacy">
            <template #header>Политика конфиденциальности</template>
            <p class="text-sm text-slate-200/90">
                Здесь позже появится полный текст политики конфиденциальности.
            </p>
        </BaseModal>

        <BaseModal v-model="showRules">
            <template #header>Правила использования</template>
            <p class="text-sm text-slate-200/90">
                Здесь позже появятся правила использования сервиса.
            </p>
        </BaseModal>

        <BaseModal v-model="showAgreement">
            <template #header>Пользовательское соглашение</template>
            <p class="text-sm text-slate-200/90">
                Здесь позже появится текст пользовательского соглашения.
            </p>
        </BaseModal>
    </footer>
</template>

<style scoped></style>
