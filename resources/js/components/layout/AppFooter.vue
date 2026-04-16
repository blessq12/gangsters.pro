<script setup>
import { computed, ref } from "vue";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";
import { getLegalTexts } from "../../content/legalTexts";
import { useSystemReadModel } from "../../features/system/useSystemReadModel";
import { hasDocumentBody } from "../../utils/system/documentBody";

const FOOTER_DOC_KEYS = {
    privacy: "privacy_policy",
    rules: "terms_of_use",
    agreement: "user_agreement",
};

const year = new Date().getFullYear();
const fallbackLegal = getLegalTexts();
const { documents, loading } = useSystemReadModel({ autoload: true });

const showPrivacy = ref(false);
const showRules = ref(false);
const showAgreement = ref(false);

const containerRef = ref(null);

useEnterSlide(containerRef, {
    y: 40,
    delay: 1.2,
});

function resolveFooterDoc(key, fallbackBlock) {
    const docs = documents.value || [];
    const doc = docs.find((d) => d.key === key);
    const title =
        doc?.title && String(doc.title).trim()
            ? String(doc.title).trim()
            : fallbackBlock.title;
    if (doc && hasDocumentBody(doc.content)) {
        return {
            title,
            useHtml: true,
            html: doc.content,
        };
    }
    return {
        title,
        useHtml: false,
        paragraphs: fallbackBlock.content,
    };
}

const privacyDoc = computed(() =>
    resolveFooterDoc(FOOTER_DOC_KEYS.privacy, fallbackLegal.privacy),
);
const rulesDoc = computed(() =>
    resolveFooterDoc(FOOTER_DOC_KEYS.rules, fallbackLegal.rules),
);
const agreementDoc = computed(() =>
    resolveFooterDoc(FOOTER_DOC_KEYS.agreement, fallbackLegal.agreement),
);

const legalHtmlClass =
    "legal-doc text-sm text-slate-200/90 [&_p]:mb-3 [&_p:last-child]:mb-0 [&_ul]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:mb-3 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-amber-300 [&_a]:underline-offset-2 hover:[&_a]:underline [&_strong]:text-slate-100";
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
                        {{ privacyDoc.title }}
                    </button>
                    <button
                        type="button"
                        class="hover:text-amber-300 transition-colors duration-200"
                        @click="showRules = true"
                    >
                        {{ rulesDoc.title }}
                    </button>
                    <button
                        type="button"
                        class="hover:text-amber-300 transition-colors duration-200"
                        @click="showAgreement = true"
                    >
                        {{ agreementDoc.title }}
                    </button>
                </div>
                <p class="opacity-70 text-slate-300/80 text-xs sm:text-sm">
                    © Gangsters, {{ year }}
                </p>
            </div>
        </div>

        <BaseModal v-model="showPrivacy">
            <template #header>{{ privacyDoc.title }}</template>
            <div
                v-if="privacyDoc.useHtml"
                :class="legalHtmlClass"
                v-html="privacyDoc.html"
            />
            <div
                v-else
                class="space-y-3 text-sm text-slate-200/90"
            >
                <p
                    v-for="(para, i) in privacyDoc.paragraphs"
                    :key="i"
                >
                    {{ para }}
                </p>
            </div>
        </BaseModal>

        <BaseModal v-model="showRules">
            <template #header>{{ rulesDoc.title }}</template>
            <div
                v-if="rulesDoc.useHtml"
                :class="legalHtmlClass"
                v-html="rulesDoc.html"
            />
            <div
                v-else
                class="space-y-3 text-sm text-slate-200/90"
            >
                <p
                    v-for="(para, i) in rulesDoc.paragraphs"
                    :key="i"
                >
                    {{ para }}
                </p>
            </div>
        </BaseModal>

        <BaseModal v-model="showAgreement">
            <template #header>{{ agreementDoc.title }}</template>
            <div
                v-if="agreementDoc.useHtml"
                :class="legalHtmlClass"
                v-html="agreementDoc.html"
            />
            <div
                v-else
                class="space-y-3 text-sm text-slate-200/90"
            >
                <p
                    v-for="(para, i) in agreementDoc.paragraphs"
                    :key="i"
                >
                    {{ para }}
                </p>
            </div>
        </BaseModal>
    </footer>
</template>

<style scoped></style>
