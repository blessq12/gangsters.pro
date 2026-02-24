<script setup>
import { ref } from "vue";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";
import BaseModal from "../ui/BaseModal.vue";

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
    <footer class="mt-8 pb-4">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                ref="containerRef"
                class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-[rgba(255,255,255,0.03)] px-4 sm:px-6 lg:px-8 py-4 flex-wrap text-sm"
            >
                <div class="flex flex-wrap gap-3">
                    <RouterLink :to="{ name: 'about' }">О компании</RouterLink>
                    <RouterLink :to="{ name: 'delivery' }">
                        Оплата и доставка
                    </RouterLink>
                    <RouterLink :to="{ name: 'contacts' }">
                        Контакты
                    </RouterLink>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="text-slate-300/80 hover:text-amber-300 transition-colors"
                        @click="showPrivacy = true"
                    >
                        Политика конфиденциальности
                    </button>
                    <button
                        type="button"
                        class="text-slate-300/80 hover:text-amber-300 transition-colors"
                        @click="showRules = true"
                    >
                        Правила использования
                    </button>
                    <button
                        type="button"
                        class="text-slate-300/80 hover:text-amber-300 transition-colors"
                        @click="showAgreement = true"
                    >
                        Пользовательское соглашение
                    </button>
                </div>
                <p class="opacity-70">© Gangsters, {{ year }}</p>
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
