<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useContentStore } from "../stores/contentStore";
import { formatRuPhone, phoneToTelHref } from "../utils/phone/formatRuPhone";
import { formatAverageDeliveryLine } from "../utils/system/companyDeliveryFacts";
import {
    formatCompanyAddressLine,
    getWorkScheduleRows,
    safeTrim,
} from "../utils/system/companyDisplay";
import { getCurrentDayKey } from "../utils/system/companyOpenStatus";
import { useAppDesign } from "../design/useAppDesign";

const contentStore = useContentStore();
const { profile, deliveryFacts: facts, loading, error } = storeToRefs(contentStore);

const loadingProfile = computed(() => loading.value && !profile.value);
const loadingDelivery = computed(() => loading.value && !facts.value);
const errors = computed(() => ({
    profile: error.value,
    legal: error.value,
    documents: error.value,
}));

const heroDescription = computed(() => {
    const c = profile.value;
    const tag = safeTrim(c?.tagline);
    if (tag) return tag;
    const desc = safeTrim(c?.description);
    if (desc) return desc;
    return "Нужен заказ, уточнение по доставке или партнёрский вопрос — каналы связи и режим работы с актуальными данными.";
});

const heroStats = computed(() => {
    const c = profile.value;
    const d = facts.value;
    return [
        {
            label: "Режим",
            value: safeTrim(c?.work_hours) || "—",
        },
        {
            label: "Доставка",
            value: formatAverageDeliveryLine(d),
        },
    ];
});

const phoneDisplay = computed(() => {
    const c = profile.value;
    const raw = c?.phone || c?.support_phone;
    return raw ? formatRuPhone(raw) : "";
});

const phoneTel = computed(() => {
    const c = profile.value;
    return phoneToTelHref(c?.phone || c?.support_phone);
});

const phoneExtra = computed(() => {
    const c = profile.value;
    if (!c?.phone || !c?.support_phone) return "";
    if (String(c.phone) === String(c.support_phone)) return "";
    return formatRuPhone(c.support_phone);
});

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

const addressLines = computed(() => {
    const d = facts.value;
    if (!d) return [];
    const line = formatCompanyAddressLine(d);
    if (!line) return [];
    return [line];
});

const hasAddress = computed(() => addressLines.value.length > 0);

const scheduleRows = computed(() => {
    const c = profile.value;
    if (!c) return [];
    const rows = getWorkScheduleRows(c.work_schedule);
    if (rows.length) return rows;
    const wh = safeTrim(c.work_hours);
    if (wh) {
        return [
            {
                dayKey: null,
                dayLabel: "",
                isDayOff: false,
                work: wh,
                isFallbackString: true,
            },
        ];
    }
    return [];
});

const currentDayKey = computed(() => getCurrentDayKey(new Date()));

function isScheduleToday(dayKey) {
    return dayKey != null && dayKey === currentDayKey.value;
}

const siteUrl = computed(() => safeTrim(profile.value?.site_url));

const whatsappHref = computed(() => {
    const w = profile.value?.whatsapp_phone;
    if (!w) return null;
    const digits = String(w).replace(/\D/g, "");
    if (digits.length < 10) return null;
    const tail = digits.slice(-10);
    return `https://wa.me/7${tail}`;
});

const co = useAppDesign().components.pages.contacts;
</script>

<template>
    <SecondaryPageLayout
        title="Контакты"
        eyebrow="Связаться с нами"
        :description="heroDescription"
        :breadcrumbs="['Главная', 'Контакты']"
        hero-image="/images/contact_banner.jpg"
        :stats="heroStats"
    >
        <p
            v-if="errors.profile"
            :class="co.apiError"
        >
            {{ errors.profile }}
        </p>

        <div :class="co.channelsGrid">
            <article :class="co.channelArticle">
                <div :class="co.channelIconWrap">
                    <i class="mdi mdi-phone-outline text-xl" />
                </div>
                <p :class="co.channelLabel">
                    Телефон
                </p>
                <p
                    v-if="loadingProfile && !phoneDisplay"
                    :class="co.channelLoading"
                >
                    Загрузка…
                </p>
                <template v-else>
                    <p :class="co.channelValueRow">
                        <a
                            v-if="phoneTel"
                            :href="phoneTel"
                            :class="co.channelLinkHover"
                        >
                            {{ phoneDisplay }}
                        </a>
                        <span v-else-if="phoneDisplay">{{ phoneDisplay }}</span>
                        <span
                            v-else
                            :class="co.channelMutedValue"
                        >Уточняется</span>
                    </p>
                    <p
                        v-if="phoneExtra"
                        :class="co.channelSubMuted"
                    >
                        Поддержка: {{ phoneExtra }}
                    </p>
                </template>
                <p :class="co.channelLead">
                    Для заказов, уточнений по доставке и быстрых вопросов по меню.
                </p>
            </article>

            <article :class="co.channelArticle">
                <div :class="co.channelIconWrap">
                    <i class="mdi mdi-send-outline text-xl" />
                </div>
                <p :class="co.channelLabel">
                    Telegram
                </p>
                <p
                    v-if="loadingProfile && !telegramLabel"
                    :class="co.channelLoading"
                >
                    Загрузка…
                </p>
                <p
                    v-else
                    :class="co.channelValueRow"
                >
                    <a
                        v-if="telegramHref"
                        :href="telegramHref"
                        target="_blank"
                        rel="noopener noreferrer"
                        :class="co.channelLinkHover"
                    >
                        {{ telegramLabel || "Написать в Telegram" }}
                    </a>
                    <span
                        v-else
                        :class="co.channelMutedValue"
                    >Уточняется</span>
                </p>
                <p :class="co.channelLead">
                    Самый быстрый канал для связи и актуальных акций.
                </p>
                <p
                    v-if="whatsappHref"
                    class="mt-3 text-sm"
                >
                    <a
                        :href="whatsappHref"
                        target="_blank"
                        rel="noopener noreferrer"
                        :class="co.waLink"
                    >
                        WhatsApp
                    </a>
                </p>
            </article>

            <article :class="co.channelArticle">
                <div :class="co.channelIconWrap">
                    <i class="mdi mdi-email-outline text-xl" />
                </div>
                <p :class="co.channelLabel">
                    Эл. почта
                </p>
                <p
                    v-if="loadingProfile && !emailDisplay"
                    :class="co.channelLoading"
                >
                    Загрузка…
                </p>
                <p
                    v-else
                    :class="co.channelValueRow"
                >
                    <a
                        v-if="emailHref"
                        :href="emailHref"
                        :class="co.emailLink"
                    >
                        {{ emailDisplay }}
                    </a>
                    <span
                        v-else
                        :class="co.channelMutedValue"
                    >Уточняется</span>
                </p>
                <p :class="co.channelLead">
                    Для партнёрств и предложений, где важны детали в переписке.
                </p>
            </article>
        </div>

        <div :class="co.mainGrid">
            <SecondaryContentBlock
                title="Где мы находимся"
                subtitle="БАЗА КУХНИ"
            >
                <template v-if="loadingDelivery && !hasAddress">
                    <p :class="co.addressLoading">
                        Загрузка адреса…
                    </p>
                </template>
                <template v-else-if="hasAddress">
                    <p
                        v-for="(line, i) in addressLines"
                        :key="i"
                        :class="{ [co.addressLineSpaced]: i > 0 }"
                    >
                        {{ line }}
                    </p>
                </template>
                <p v-else>
                    Адрес уточняется. Свяжитесь с нами по телефону или в мессенджере.
                </p>

                <ContactsKitchenMap />

                <p
                    v-if="siteUrl"
                    :class="co.siteLinkPara"
                >
                    <a
                        :href="siteUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        :class="co.waLink"
                    >
                        Сайт
                    </a>
                </p>
            </SecondaryContentBlock>

            <SecondaryContentBlock
                title="Режим работы"
                subtitle="РЕЖИМ РАБОТЫ"
            >
                <p
                    v-if="loadingProfile && !scheduleRows.length"
                    :class="co.scheduleLoading"
                >
                    Загрузка…
                </p>
                <p
                    v-else-if="scheduleRows.length && scheduleRows[0].isFallbackString"
                    :class="co.scheduleFallback"
                >
                    {{ scheduleRows[0].work }}
                </p>
                <ul
                    v-else-if="scheduleRows.length"
                    :class="co.scheduleList"
                >
                    <li
                        v-for="(row, idx) in scheduleRows"
                        :key="row.dayKey || `row-${idx}`"
                        :class="co.scheduleRow"
                    >
                        <span
                            :class="
                                isScheduleToday(row.dayKey)
                                    ? co.scheduleDayToday
                                    : co.scheduleDay
                            "
                        >
                            {{ row.dayLabel }}
                        </span>
                        <span
                            :class="
                                isScheduleToday(row.dayKey)
                                    ? co.scheduleWorkToday
                                    : co.scheduleWork
                            "
                        >
                            <template v-if="row.isDayOff">Выходной</template>
                            <template v-else>{{ row.work || "—" }}</template>
                        </span>
                    </li>
                </ul>
                <p
                    v-else
                    :class="co.scheduleEmpty"
                >
                    Заказы принимаем в заявленном режиме. Уточнения — по телефону или в мессенджере.
                </p>
            </SecondaryContentBlock>
        </div>

        <SecondaryContentBlock
            title="Как лучше связаться"
            subtitle="КАК С НАМИ СВЯЗАТЬСЯ"
        >
            <div :class="co.tipsGrid">
                <div :class="co.tipTile">
                    <p :class="co.tipKicker">
                        01
                    </p>
                    <p :class="co.tipTitle">
                        По заказу
                    </p>
                    <p :class="co.tipBody">
                        Звонок или Telegram — самый короткий путь, если вопрос срочный.
                    </p>
                </div>
                <div :class="co.tipTile">
                    <p :class="co.tipKicker">
                        02
                    </p>
                    <p :class="co.tipTitle">
                        По сотрудничеству
                    </p>
                    <p :class="co.tipBody">
                        Лучше писать на email, чтобы не потерялись детали и контакты.
                    </p>
                </div>
                <div :class="co.tipTile">
                    <p :class="co.tipKicker">
                        03
                    </p>
                    <p :class="co.tipTitle">
                        По акциям и новостям
                    </p>
                    <p :class="co.tipBody">
                        Удобнее всего следить в Telegram и на сайте.
                    </p>
                </div>
            </div>
        </SecondaryContentBlock>
    </SecondaryPageLayout>
</template>

<style scoped></style>
