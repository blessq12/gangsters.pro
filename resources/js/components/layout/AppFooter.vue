<script setup>
import { ref } from "vue";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";
import { getLegalTexts } from "../../content/legalTexts";

const year = new Date().getFullYear();
const legal = getLegalTexts();

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
                        {{ legal.privacy.title }}
                    </button>
                    <button
                        type="button"
                        class="hover:text-amber-300 transition-colors duration-200"
                        @click="showRules = true"
                    >
                        {{ legal.rules.title }}
                    </button>
                    <button
                        type="button"
                        class="hover:text-amber-300 transition-colors duration-200"
                        @click="showAgreement = true"
                    >
                        {{ legal.agreement.title }}
                    </button>
                </div>
                <p class="opacity-70 text-slate-300/80 text-xs sm:text-sm">
                    © Gangsters, {{ year }}
                </p>
            </div>
        </div>

        <BaseModal v-model="showPrivacy">
            <template #header>{{ legal.privacy.title }}</template>
            <div class="space-y-3 text-sm text-slate-200/90">
                <p v-for="(para, i) in legal.privacy.content" :key="i">
                    {{ para }}
                </p>
            </div>
        </BaseModal>

        <BaseModal v-model="showRules">
            <template #header>{{ legal.rules.title }}</template>
            <div class="space-y-3 text-sm text-slate-200/90">
                <p v-for="(para, i) in legal.rules.content" :key="i">
                    {{ para }}
                </p>
            </div>
        </BaseModal>

        <BaseModal v-model="showAgreement">
            <template #header>{{ legal.agreement.title }}</template>
            <div class="space-y-3 text-sm text-slate-200/90">
                <p v-for="(para, i) in legal.agreement.content" :key="i">
                    {{ para }}
                </p>
            </div>
        </BaseModal>
    </footer>
</template>

<style scoped></style>
