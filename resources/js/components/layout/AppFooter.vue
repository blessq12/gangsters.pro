<script setup>
import { computed, ref } from "vue";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";
import { useAppDesign } from "../../design/useAppDesign";
import { getLegalTexts } from "../../content/legalTexts";
import { useSystemReadModel } from "../../features/system/useSystemReadModel";
import { hasDocumentBody } from "../../utils/system/documentBody";

const FOOTER_DOC_KEYS = {
    privacy: "privacy_policy",
    rules: "terms_of_use",
    agreement: "user_agreement",
};

const footer = useAppDesign().components.footer;

const year = new Date().getFullYear();
const fallbackLegal = getLegalTexts();
const { documents } = useSystemReadModel({ autoload: true });

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
</script>

<template>
    <footer :class="footer.footer">
        <div :class="footer.inner">
            <div
                ref="containerRef"
                :class="footer.bar"
            >
                <FooterPrimaryNav />

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
