<script setup>
import { computed } from "vue";
import { useSystemReadModel } from "../features/system/useSystemReadModel";
import { formatRuPhone, phoneToTelHref } from "../utils/phone/formatRuPhone";
import {
    formatWorkScheduleForDisplay,
    safeTrim,
} from "../utils/system/companyDisplay";
import { useAppDesign } from "../design/useAppDesign";

const { company: companyRef, loading, errors } = useSystemReadModel({
    autoload: true,
});

const loadingCompany = computed(() => loading.value.company);

const company = computed(() => companyRef.value);

const heroDescription = computed(() => {
    const c = company.value;
    const tag = safeTrim(c?.tagline);
    if (tag) return tag;
    const desc = safeTrim(c?.description);
    if (desc) return desc;
    return "Нужен заказ, уточнение по доставке или партнёрский вопрос — каналы связи и режим работы с актуальными данными.";
});

const heroStats = computed(() => {
    const c = company.value;
    return [
        {
            label: "Режим",
            value: safeTrim(c?.work_hours) || "—",
        },
        {
            label: "Доставка",
            value:
                c?.average_delivery_time_minutes != null
                    ? `около ${c.average_delivery_time_minutes} мин`
                    : "—",
        },
        {
            label: "Покрытие",
            value: safeTrim(c?.city_coverage) || "Доставка",
        },
    ];
});

const phoneDisplay = computed(() => {
    const c = company.value;
    const raw = c?.phone || c?.support_phone;
    return raw ? formatRuPhone(raw) : "";
});

const phoneTel = computed(() => {
    const c = company.value;
    return phoneToTelHref(c?.phone || c?.support_phone);
});

const phoneExtra = computed(() => {
    const c = company.value;
    if (!c?.phone || !c?.support_phone) return "";
    if (String(c.phone) === String(c.support_phone)) return "";
    return formatRuPhone(c.support_phone);
});

const telegramLabel = computed(() => {
    const t = company.value?.telegram;
    if (!t) return "";
    const s = String(t).trim();
    if (s.startsWith("http")) return s.replace(/^https?:\/\/t\.me\//i, "@");
    return s.startsWith("@") ? s : `@${s.replace(/^@/, "")}`;
});

const telegramHref = computed(() => {
    const t = company.value?.telegram;
    if (!t) return null;
    const s = String(t).trim();
    if (/^https?:\/\//i.test(s)) return s;
    const u = s.replace(/^@/, "").replace(/^(https?:\/\/)?t\.me\//i, "");
    return u ? `https://t.me/${u}` : null;
});

const emailDisplay = computed(() => {
    const c = company.value;
    return safeTrim(c?.public_email) || safeTrim(c?.email_address) || "";
});

const emailHref = computed(() => {
    const e = emailDisplay.value;
    return e ? `mailto:${e}` : null;
});

const addressLines = computed(() => {
    const c = company.value;
    if (!c) return [];
    const parts = [
        [c.city, c.street, c.house && `д. ${c.house}`]
            .filter(Boolean)
            .join(", "),
    ].filter(Boolean);
    const comment = safeTrim(c.address_comment);
    if (comment) {
        parts.push(comment);
    }
    return parts;
});

const hasAddress = computed(() => addressLines.value.length > 0);

const coverageText = computed(() => {
    const t = safeTrim(company.value?.city_coverage);
    return (
        t ||
        "Точную доступность по адресу можно проверить при оформлении заказа."
    );
});

const workHoursMain = computed(() => {
    const c = company.value;
    return safeTrim(c?.work_hours) || "—";
});

const workScheduleNote = computed(() => {
    const c = company.value;
    const fromSchedule = formatWorkScheduleForDisplay(c?.work_schedule);
    if (fromSchedule) return fromSchedule;
    return "Заказы принимаем в заявленном режиме. Уточнения — по телефону или в мессенджере.";
});

const siteUrl = computed(() => safeTrim(company.value?.site_url));

const whatsappHref = computed(() => {
    const w = company.value?.whatsapp_phone;
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
        hero-image="/images/banners/banner3.jpeg"
        :stats="heroStats"
    >
        <p
            v-if="errors.company"
            :class="co.apiError"
        >
            {{ errors.company }}
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
                    v-if="loadingCompany && !phoneDisplay"
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
                    v-if="loadingCompany && !telegramLabel"
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
                    v-if="loadingCompany && !emailDisplay"
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
                <template v-if="loadingCompany && !hasAddress">
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

                <div :class="co.coverageBox">
                    <p :class="co.coverageKicker">
                        Зона покрытия
                    </p>
                    <p :class="co.coverageBody">
                        {{ coverageText }}
                    </p>
                </div>

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

            <article :class="co.scheduleArticle">
                <p :class="co.scheduleEyebrow">
                    Режим работы
                </p>
                <p :class="co.scheduleTime">
                    {{ workHoursMain }}
                </p>
                <p :class="co.scheduleNote">
                    {{ workScheduleNote }}
                </p>
                <div
                    v-if="company?.min_order_amount_kopecks != null || company?.delivery_fee_kopecks != null"
                    :class="co.feeStack"
                >
                    <div
                        v-if="company.min_order_amount_kopecks != null"
                        :class="co.feeRow"
                    >
                        <span>Мин. заказ</span>
                        <span :class="co.feeValue">
                            {{
                                new Intl.NumberFormat("ru-RU").format(
                                    Math.round(
                                        Number(company.min_order_amount_kopecks) /
                                            100,
                                    ),
                                )
                            }}
                            ₽
                        </span>
                    </div>
                    <div
                        v-if="company.delivery_fee_kopecks != null"
                        :class="co.feeRow"
                    >
                        <span>Доставка от</span>
                        <span :class="co.feeValue">
                            {{
                                new Intl.NumberFormat("ru-RU").format(
                                    Math.round(
                                        Number(company.delivery_fee_kopecks) / 100,
                                    ),
                                )
                            }}
                            ₽
                        </span>
                    </div>
                </div>
            </article>
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
