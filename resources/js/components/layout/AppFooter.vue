<script setup>
import { computed, ref } from "vue";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";
import { useAppDesign } from "../../design/useAppDesign";
import { storeToRefs } from "pinia";
import { useContentStore } from "../../stores/contentStore";
import { hasDocumentBody } from "../../utils/system/documentBody";

const FOOTER_DOC_KEYS = {
    privacy: "privacy_policy",
    rules: "terms_of_use",
    agreement: "user_agreement",
};

const FOOTER_DOC_TITLES = {
    privacy_policy: "Политика конфиденциальности",
    terms_of_use: "Правила использования",
    user_agreement: "Пользовательское соглашение",
};

const footer = useAppDesign().components.footer;

const year = new Date().getFullYear();
const contentStore = useContentStore();
const { documents } = storeToRefs(contentStore);

const showPrivacy = ref(false);
const showRules = ref(false);
const showAgreement = ref(false);

const containerRef = ref(null);

useEnterSlide(containerRef, {
    y: 40,
    delay: 1.2,
});

function resolveFooterDoc(key) {
    const docs = documents.value || [];
    const doc = docs.find((d) => d.key === key);
    const title =
        doc?.title && String(doc.title).trim()
            ? String(doc.title).trim()
            : FOOTER_DOC_TITLES[key] || key;

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
        empty: true,
    };
}

const privacyDoc = computed(() => resolveFooterDoc(FOOTER_DOC_KEYS.privacy));
const rulesDoc = computed(() => resolveFooterDoc(FOOTER_DOC_KEYS.rules));
const agreementDoc = computed(() =>
    resolveFooterDoc(FOOTER_DOC_KEYS.agreement),
);
</script>

<template>
    <footer :class="footer.footer">
        <div :class="footer.inner">
            <div
                ref="containerRef"
                :class="footer.bar"
            >
                <div :class="footer.legalLinks">
                    <button
                        type="button"
                        :class="footer.legalButton"
                        @click="showPrivacy = true"
                    >
                        {{ privacyDoc.title }}
                    </button>
                    <button
                        type="button"
                        :class="footer.legalButton"
                        @click="showRules = true"
                    >
                        {{ rulesDoc.title }}
                    </button>
                    <button
                        type="button"
                        :class="footer.legalButton"
                        @click="showAgreement = true"
                    >
                        {{ agreementDoc.title }}
                    </button>
                </div>
                <p :class="footer.copyright">
                    © Gangsters, {{ year }}
                </p>
            </div>
        </div>

        <FooterLegalModal
            v-model="showPrivacy"
            :title="privacyDoc.title"
            :doc="privacyDoc"
        />
        <FooterLegalModal
            v-model="showRules"
            :title="rulesDoc.title"
            :doc="rulesDoc"
        />
        <FooterLegalModal
            v-model="showAgreement"
            :title="agreementDoc.title"
            :doc="agreementDoc"
        />
    </footer>
</template>

<style scoped></style>
