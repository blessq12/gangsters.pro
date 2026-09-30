<script setup>
import { storeToRefs } from "pinia";
import { computed, ref } from "vue";
import { useEnterSlide } from "../../animations/animationManager";
import { NAV_LINKS_MOBILE_SHEET } from "../../design/layout/navigation.present";
import { useAppDesign } from "../../design/useAppDesign";
import {
    buildDefinedDeliveryStats,
    formatCompanyAddressLine,
    formatTodayWorkScheduleLine,
    safeTrim,
} from "../../modules/content/application/company";
import { useContentStore } from "../../modules/content/store";
import { hasDocumentBody } from "../../platform/document";
import { formatRuPhone, phoneToTelHref } from "../../platform/ruPhone";
import FooterLegalModal from "./FooterLegalModal.vue";

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
const { documents, profile, deliveryFacts, legal } = storeToRefs(contentStore);

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

const companyTitle = computed(() => {
    const c = profile.value;
    if (!c) return "";
    return safeTrim(c.brand_name) || safeTrim(c.name) || "";
});

const todayScheduleLine = computed(() =>
    formatTodayWorkScheduleLine(profile.value, new Date()),
);

const phoneRaw = computed(
    () => profile.value?.phone || profile.value?.support_phone || "",
);
const phoneDisplay = computed(() => formatRuPhone(phoneRaw.value));
const phoneHref = computed(() => phoneToTelHref(phoneRaw.value));

const telegramLabel = computed(() => {
    const t = profile.value?.telegram;
    if (!t) return "";
    const s = String(t).trim();
    if (s.startsWith("http")) return s.replace(/^https?:\/\/t\.me\//i, "@");
    return s.startsWith("@") ? s : `@${s.replace(/^@/, "")}`;
});

const telegramHref = computed(() => {
    const t = profile.value?.telegram;
    if (!t) return null;
    const s = String(t).trim();
    if (/^https?:\/\//i.test(s)) return s;
    const u = s.replace(/^@/, "").replace(/^(https?:\/\/)?t\.me\//i, "");
    return u ? `https://t.me/${u}` : null;
});

const emailDisplay = computed(() => {
    const c = profile.value;
    return safeTrim(c?.public_email) || safeTrim(c?.email_address) || "";
});

const emailHref = computed(() => {
    const e = emailDisplay.value;
    return e ? `mailto:${e}` : null;
});

const whatsappHref = computed(() => {
    const w = profile.value?.whatsapp_phone;
    if (!w) return null;
    const digits = String(w).replace(/\D/g, "");
    if (digits.length < 10) return null;
    const tail = digits.slice(-10);
    return `https://wa.me/7${tail}`;
});

/**
 * @param {unknown} raw
 * @param {string} host без схемы, например "vk.com"
 */
function socialProfileHref(raw, host) {
    if (raw == null) return null;
    const s = String(raw).trim();
    if (!s) return null;
    if (/^https?:\/\//i.test(s)) return s;
    const path = s
        .replace(new RegExp(`^(https?:\\/\\/)?(www\\.)?${host}\\/`, "i"), "")
        .replace(/^@/, "")
        .replace(/^\//, "");
    return path ? `https://${host}/${path}` : null;
}

const vkHref = computed(() => socialProfileHref(profile.value?.vk, "vk.com"));

const instHref = computed(() =>
    socialProfileHref(profile.value?.inst, "instagram.com"),
);

const addressLine = computed(() =>
    formatCompanyAddressLine(deliveryFacts.value),
);

const deliveryStats = computed(() =>
    buildDefinedDeliveryStats(deliveryFacts.value),
);

const legalName = computed(() => {
    const l = legal.value;
    if (!l) return "";
    return safeTrim(l.full_name) || safeTrim(l.short_name) || "";
});

const legalInn = computed(() => safeTrim(legal.value?.inn) || "");

const legalOgrnLabel = computed(() => {
    const l = legal.value;
    if (!l) return "";
    if (safeTrim(l.ogrn)) return "ОГРН";
    if (safeTrim(l.ogrnip)) return "ОГРНИП";
    return "";
});

const legalOgrnValue = computed(() => {
    const l = legal.value;
    if (!l) return "";
    return safeTrim(l.ogrn) || safeTrim(l.ogrnip) || "";
});

const hasContactsCol = computed(
    () =>
        Boolean(phoneHref.value && phoneDisplay.value) ||
        Boolean(telegramHref.value) ||
        Boolean(whatsappHref.value) ||
        Boolean(emailHref.value) ||
        Boolean(vkHref.value) ||
        Boolean(instHref.value) ||
        Boolean(todayScheduleLine.value),
);

const hasServiceCol = computed(
    () => Boolean(addressLine.value) || deliveryStats.value.length > 0,
);

const hasLegalIds = computed(
    () => Boolean(legalInn.value) || Boolean(legalOgrnValue.value),
);

const hasSocials = computed(
    () => Boolean(vkHref.value) || Boolean(instHref.value),
);

const copyrightName = computed(
    () => companyTitle.value || legalName.value || "Gangsters",
);
</script>

<template>
    <footer data-app-footer :class="footer.footer">
        <div :class="footer.inner">
            <div ref="containerRef" :class="footer.bar">
                <div :class="footer.columns">
                    <div :class="footer.column">
                        <p :class="footer.columnTitle">
                            Юридическая информация
                        </p>
                        <div :class="footer.linkStack">
                            <button
                                type="button"
                                :class="footer.linkItem"
                                @click="showPrivacy = true"
                            >
                                {{ privacyDoc.title }}
                            </button>
                            <button
                                type="button"
                                :class="footer.linkItem"
                                @click="showRules = true"
                            >
                                {{ rulesDoc.title }}
                            </button>
                            <button
                                type="button"
                                :class="footer.linkItem"
                                @click="showAgreement = true"
                            >
                                {{ agreementDoc.title }}
                            </button>
                        </div>
                    </div>

                    <div v-if="hasContactsCol" :class="footer.column">
                        <p :class="footer.columnTitle">Контакты</p>
                        <div :class="footer.contactStack">
                            <a
                                v-if="phoneHref && phoneDisplay"
                                :href="phoneHref"
                                :class="footer.contactPrimary"
                            >
                                {{ phoneDisplay }}
                            </a>
                            <a
                                v-if="telegramHref"
                                :href="telegramHref"
                                target="_blank"
                                rel="noopener noreferrer"
                                :class="footer.contactLink"
                            >
                                Telegram {{ telegramLabel }}
                            </a>
                            <a
                                v-if="whatsappHref"
                                :href="whatsappHref"
                                target="_blank"
                                rel="noopener noreferrer"
                                :class="footer.contactLink"
                            >
                                WhatsApp
                            </a>
                            <a
                                v-if="emailHref"
                                :href="emailHref"
                                :class="footer.contactLink"
                            >
                                {{ emailDisplay }}
                            </a>
                            <template v-if="hasSocials">
                                <a
                                    v-if="vkHref"
                                    :href="vkHref"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :class="footer.contactSocial"
                                >
                                    ВКонтакте
                                </a>
                                <a
                                    v-if="instHref"
                                    :href="instHref"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :class="footer.contactSocial"
                                >
                                    Instagram*
                                </a>
                            </template>
                            <p
                                v-if="todayScheduleLine"
                                :class="footer.contactMeta"
                            >
                                {{ todayScheduleLine }}
                            </p>
                        </div>
                    </div>

                    <div v-if="hasServiceCol" :class="footer.column">
                        <p :class="footer.columnTitle">Доставка</p>
                        <div :class="footer.metaStack">
                            <p v-if="addressLine" :class="footer.metaText">
                                {{ addressLine }}
                            </p>
                            <p
                                v-for="stat in deliveryStats"
                                :key="stat.label"
                                :class="footer.metaText"
                            >
                                {{ stat.label }}: {{ stat.value }}
                            </p>
                        </div>
                    </div>

                    <div :class="footer.column">
                        <p :class="footer.columnTitle">Разделы</p>
                        <nav :class="footer.linkStack">
                            <RouterLink
                                v-for="item in NAV_LINKS_MOBILE_SHEET"
                                :key="item.routeName"
                                :to="{ name: item.routeName }"
                                :class="footer.linkItem"
                            >
                                {{ item.label }}
                            </RouterLink>
                        </nav>
                    </div>
                </div>

                <div :class="footer.legalFooter">
                    <div
                        v-if="hasLegalIds || legalName"
                        :class="footer.legalBar"
                    >
                        <p v-if="hasLegalIds" :class="footer.legalIds">
                            <span v-if="legalInn">ИНН {{ legalInn }}</span>
                            <span v-if="legalOgrnValue">
                                {{ legalOgrnLabel }} {{ legalOgrnValue }}
                            </span>
                        </p>
                        <p :class="footer.copyright">
                            © {{ copyrightName }}, {{ year }}
                        </p>
                    </div>

                    <p v-if="instHref" :class="footer.footnote">
                        * Meta Platforms Inc. (социальные сети Facebook и
                        Instagram) — организация, деятельность которой признана
                        экстремистской и запрещена на территории Российской
                        Федерации.
                    </p>
                </div>
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
