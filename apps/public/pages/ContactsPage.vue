<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useContentStore } from "../modules/content/store";
import { formatRuPhone, phoneToTelHref } from "../platform/ruPhone";
import { formatAverageDeliveryLine } from "../modules/content/application/company";
import {
    formatCompanyAddressLine,
    formatTodayWorkScheduleLine,
    getWorkScheduleRows,
    safeTrim,
} from "../modules/content/application/company";
import { getCurrentDayKey } from "../modules/content/application/company";
import { useAppDesign } from "../design/useAppDesign";

const contentStore = useContentStore();
const { profile, deliveryFacts: facts, legal, loading, error } = storeToRefs(contentStore);

const loadingProfile = computed(() => loading.value && !profile.value);
const loadingDelivery = computed(() => loading.value && !facts.value);
const loadingLegal = computed(() => loading.value && !legal.value);
const errors = computed(() => ({
    profile: error.value,
}));

const legalName = computed(() => safeTrim(legal.value?.full_name) || "");
const legalInn = computed(() => safeTrim(legal.value?.inn) || "");
const legalOgrn = computed(() => safeTrim(legal.value?.ogrn) || "");
const hasLegal = computed(
    () => Boolean(legalName.value) || Boolean(legalInn.value) || Boolean(legalOgrn.value),
);

const heroStats = computed(() => {
    const c = profile.value;
    const d = facts.value;
    const today = formatTodayWorkScheduleLine(c, new Date()) || "—";
    return [
        {
            label: "Режим",
            value: today,
        },
        {
            label: "Доставка",
            value: formatAverageDeliveryLine(d),
        },
    ];
});

const phoneDisplay = computed(() => {
    const raw = profile.value?.phone;
    return raw ? formatRuPhone(raw) : "";
});

const phoneTel = computed(() => phoneToTelHref(profile.value?.phone));

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
    return getWorkScheduleRows(c.schedule);
});

const currentDayKey = computed(() => getCurrentDayKey(new Date()));

function isScheduleToday(dayKey) {
    return dayKey != null && dayKey === currentDayKey.value;
}

const siteUrl = computed(() => safeTrim(socials.value.site_url));

const whatsappHref = computed(() => {
    const w = socials.value.whatsapp;
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
        description="Телефон, мессенджеры, адрес кухни и режим работы."
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
                </template>
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
            </article>
        </div>

        <div :class="co.mainGrid">
            <SecondaryContentBlock title="Адрес">
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
                    Адрес уточняется.
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

            <SecondaryContentBlock title="Режим работы">
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
                    Режим уточняется.
                </p>
            </SecondaryContentBlock>
        </div>

        <SecondaryContentBlock title="Как лучше связаться">
            <div :class="co.tipsGrid">
                <div :class="co.tipTile">
                    <p :class="co.tipKicker">
                        01
                    </p>
                    <p :class="co.tipTitle">
                        По заказу
                    </p>
                    <p :class="co.tipBody">
                        Сайт или телефон.
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
                        Электронная почта.
                    </p>
                </div>
                <div :class="co.tipTile">
                    <p :class="co.tipKicker">
                        03
                    </p>
                    <p :class="co.tipTitle">
                        По информации
                    </p>
                    <p :class="co.tipBody">
                        Telegram-канал или Telegram-бот.
                    </p>
                </div>
            </div>
        </SecondaryContentBlock>

        <SecondaryContentBlock title="Юридическая информация">
            <p
                v-if="loadingLegal && !hasLegal"
                :class="co.legalLoading"
            >
                Загрузка…
            </p>
            <div
                v-else-if="hasLegal"
                :class="co.legalList"
            >
                <div
                    v-if="legalName"
                    :class="co.legalRow"
                >
                    <span :class="co.legalLabel">Наименование</span>
                    <span :class="co.legalValue">{{ legalName }}</span>
                </div>
                <div
                    v-if="legalInn"
                    :class="co.legalRow"
                >
                    <span :class="co.legalLabel">ИНН</span>
                    <span :class="co.legalValue">{{ legalInn }}</span>
                </div>
                <div
                    v-if="legalOgrn"
                    :class="co.legalRow"
                >
                    <span :class="co.legalLabel">ОГРН</span>
                    <span :class="co.legalValue">{{ legalOgrn }}</span>
                </div>
            </div>
            <p
                v-else
                :class="co.legalEmpty"
            >
                Юридические реквизиты уточняются.
            </p>
        </SecondaryContentBlock>
    </SecondaryPageLayout>
</template>
