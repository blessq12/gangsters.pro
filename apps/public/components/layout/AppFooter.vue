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

const footer = useAppDesign().components.footer;

const year = new Date().getFullYear();
const contentStore = useContentStore();
const { documents, profile, deliveryFacts, legal } = storeToRefs(contentStore);

/** @type {import("vue").Ref<object|null>} */
const activeDocument = ref(null);

const containerRef = ref(null);

useEnterSlide(containerRef, {
    y: 40,
    delay: 1.2,
});

const legalDocuments = computed(() => documents.value || []);

const legalModalOpen = computed({
    get: () => activeDocument.value != null,
    set: (open) => {
        if (!open) {
            activeDocument.value = null;
        }
    },
});

const activeLegalModalTitle = computed(
    () => safeTrim(activeDocument.value?.name) || "",
);

const activeLegalModalDoc = computed(() => {
    const doc = activeDocument.value;
    if (doc && hasDocumentBody(doc.content)) {
        return {
            useHtml: true,
            html: doc.content,
        };
    }

    return {
        useHtml: false,
        empty: true,
    };
});

/**
 * @param {object} doc
 */
function openLegalDocument(doc) {
    activeDocument.value = doc;
}

const companyTitle = computed(() => {
    const c = profile.value;
    if (!c) return "";
    return safeTrim(c.name) || "";
});

const todayScheduleLine = computed(() =>
    formatTodayWorkScheduleLine(profile.value, new Date()),
);

const phoneRaw = computed(() => profile.value?.phone || "");
const phoneDisplay = computed(() => formatRuPhone(phoneRaw.value));
const phoneHref = computed(() => phoneToTelHref(phoneRaw.value));

const socials = computed(() => profile.value?.socials || {});

const telegramLabel = computed(() => {
    const t = socials.value.telegram;
    if (!t) return "";
    const s = String(t).trim();
    if (s.startsWith("http")) return s.replace(/^https?:\/\/t\.me\//i, "@");
    return s.startsWith("@") ? s : `@${s.replace(/^@/, "")}`;
});

const telegramHref = computed(() => {
    const t = socials.value.telegram;
    if (!t) return null;
    const s = String(t).trim();
    if (/^https?:\/\//i.test(s)) return s;
    const u = s.replace(/^@/, "").replace(/^(https?:\/\/)?t\.me\//i, "");
    return u ? `https://t.me/${u}` : null;
});

const emailDisplay = computed(() => safeTrim(profile.value?.email) || "");

const emailHref = computed(() => {
    const e = emailDisplay.value;
    return e ? `mailto:${e}` : null;
});

const whatsappHref = computed(() => {
    const w = socials.value.whatsapp;
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

const vkHref = computed(() =>
    socialProfileHref(socials.value.vk, "vk.com"),
);

const instHref = computed(() =>
    socialProfileHref(socials.value.inst, "instagram.com"),
);

const addressLine = computed(() =>
    formatCompanyAddressLine(deliveryFacts.value),
);

const deliveryStats = computed(() =>
    buildDefinedDeliveryStats(deliveryFacts.value),
);

const legalName = computed(() => safeTrim(legal.value?.full_name) || "");

const legalInn = computed(() => safeTrim(legal.value?.inn) || "");

const legalOgrnValue = computed(() => safeTrim(legal.value?.ogrn) || "");

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
                    <div
                        v-if="legalDocuments.length"
                        :class="footer.column"
                    >
                        <p :class="footer.columnTitle">
                            Юридическая информация
                        </p>
                        <div :class="footer.linkStack">
                            <button
                                v-for="doc in legalDocuments"
                                :key="doc.id ?? doc.slug"
                                type="button"
                                :class="footer.linkItem"
                                @click="openLegalDocument(doc)"
                            >
                                {{ doc.name }}
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
                                ОГРН {{ legalOgrnValue }}
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
            v-model="legalModalOpen"
            :title="activeLegalModalTitle"
            :doc="activeLegalModalDoc"
        />
    </footer>
</template>
