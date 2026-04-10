<script setup>
import { computed, onMounted } from "vue";
import { useSystemStore } from "../stores/systemStore";
import { formatRuPhone, phoneToTelHref } from "../utils/phone/formatRuPhone";
import {
    formatWorkScheduleForDisplay,
    safeTrim,
} from "../utils/system/companyDisplay";

const systemStore = useSystemStore();

const company = computed(() => systemStore.company);

onMounted(() => {
    if (!systemStore.company && !systemStore.loadingCompany) {
        void systemStore.fetchCompany();
    }
});

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
            v-if="systemStore.errorCompany"
            class="mb-4 rounded-2xl border border-red-500/30 bg-red-950/30 px-4 py-3 text-sm text-red-200"
        >
            {{ systemStore.errorCompany }}
        </p>

        <div class="grid gap-4 md:grid-cols-3">
            <article class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.04)] p-5 shadow-[0_16px_50px_rgba(0,0,0,0.35)]">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-amber-400 text-black">
                    <i class="mdi mdi-phone-outline text-xl" />
                </div>
                <p class="text-xs uppercase tracking-[0.22em] text-slate-400">
                    Телефон
                </p>
                <p
                    v-if="systemStore.loadingCompany && !phoneDisplay"
                    class="mt-2 text-sm text-slate-500"
                >
                    Загрузка…
                </p>
                <template v-else>
                    <p class="mt-2 text-lg font-semibold text-slate-50">
                        <a
                            v-if="phoneTel"
                            :href="phoneTel"
                            class="transition-colors hover:text-amber-300"
                        >
                            {{ phoneDisplay }}
                        </a>
                        <span v-else-if="phoneDisplay">{{ phoneDisplay }}</span>
                        <span
                            v-else
                            class="text-slate-500"
                        >Уточняется</span>
                    </p>
                    <p
                        v-if="phoneExtra"
                        class="mt-1 text-sm text-slate-400"
                    >
                        Поддержка: {{ phoneExtra }}
                    </p>
                </template>
                <p class="mt-2 text-sm text-slate-300">
                    Для заказов, уточнений по доставке и быстрых вопросов по меню.
                </p>
            </article>

            <article class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.04)] p-5 shadow-[0_16px_50px_rgba(0,0,0,0.35)]">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-amber-400 text-black">
                    <i class="mdi mdi-send-outline text-xl" />
                </div>
                <p class="text-xs uppercase tracking-[0.22em] text-slate-400">
                    Telegram
                </p>
                <p
                    v-if="systemStore.loadingCompany && !telegramLabel"
                    class="mt-2 text-sm text-slate-500"
                >
                    Загрузка…
                </p>
                <p
                    v-else
                    class="mt-2 text-lg font-semibold text-slate-50"
                >
                    <a
                        v-if="telegramHref"
                        :href="telegramHref"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="transition-colors hover:text-amber-300"
                    >
                        {{ telegramLabel || "Написать в Telegram" }}
                    </a>
                    <span
                        v-else
                        class="text-slate-500"
                    >Уточняется</span>
                </p>
                <p class="mt-2 text-sm text-slate-300">
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
                        class="text-amber-300/90 underline-offset-2 hover:underline"
                    >
                        WhatsApp
                    </a>
                </p>
            </article>

            <article class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.04)] p-5 shadow-[0_16px_50px_rgba(0,0,0,0.35)]">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-amber-400 text-black">
                    <i class="mdi mdi-email-outline text-xl" />
                </div>
                <p class="text-xs uppercase tracking-[0.22em] text-slate-400">
                    Эл. почта
                </p>
                <p
                    v-if="systemStore.loadingCompany && !emailDisplay"
                    class="mt-2 text-sm text-slate-500"
                >
                    Загрузка…
                </p>
                <p
                    v-else
                    class="mt-2 text-lg font-semibold text-slate-50"
                >
                    <a
                        v-if="emailHref"
                        :href="emailHref"
                        class="break-all transition-colors hover:text-amber-300"
                    >
                        {{ emailDisplay }}
                    </a>
                    <span
                        v-else
                        class="text-slate-500"
                    >Уточняется</span>
                </p>
                <p class="mt-2 text-sm text-slate-300">
                    Для партнёрств и предложений, где важны детали в переписке.
                </p>
            </article>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(280px,0.85fr)]">
            <SecondaryContentBlock
                title="Где мы находимся"
                subtitle="БАЗА КУХНИ"
            >
                <template v-if="systemStore.loadingCompany && !hasAddress">
                    <p class="text-sm text-slate-500">
                        Загрузка адреса…
                    </p>
                </template>
                <template v-else-if="hasAddress">
                    <p
                        v-for="(line, i) in addressLines"
                        :key="i"
                        :class="{ 'mt-3': i > 0 }"
                    >
                        {{ line }}
                    </p>
                </template>
                <p v-else>
                    Адрес уточняется. Свяжитесь с нами по телефону или в мессенджере.
                </p>

                <div class="mt-4 rounded-2xl border border-white/10 bg-black/20 p-4">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                        Зона покрытия
                    </p>
                    <p class="mt-2 text-sm text-slate-200">
                        {{ coverageText }}
                    </p>
                </div>

                <p
                    v-if="siteUrl"
                    class="mt-4 text-sm"
                >
                    <a
                        :href="siteUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-amber-300/90 underline-offset-2 hover:underline"
                    >
                        Сайт
                    </a>
                </p>
            </SecondaryContentBlock>

            <article class="overflow-hidden rounded-[1.75rem] border border-amber-400/20 bg-[linear-gradient(180deg,rgba(251,191,36,0.1),rgba(255,255,255,0.03))] p-6">
                <p class="text-xs uppercase tracking-[0.26em] text-amber-200">
                    Режим работы
                </p>
                <p class="mt-4 text-3xl font-semibold text-slate-50">
                    {{ workHoursMain }}
                </p>
                <p class="mt-3 text-sm leading-relaxed text-slate-300">
                    {{ workScheduleNote }}
                </p>
                <div
                    v-if="company?.min_order_amount_kopecks != null || company?.delivery_fee_kopecks != null"
                    class="mt-5 space-y-2 text-sm text-slate-200"
                >
                    <div
                        v-if="company.min_order_amount_kopecks != null"
                        class="flex items-center justify-between rounded-2xl border border-white/10 bg-black/25 px-4 py-3"
                    >
                        <span>Мин. заказ</span>
                        <span class="font-medium text-amber-200">
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
                        class="flex items-center justify-between rounded-2xl border border-white/10 bg-black/25 px-4 py-3"
                    >
                        <span>Доставка от</span>
                        <span class="font-medium text-amber-200">
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
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                        01
                    </p>
                    <p class="mt-2 font-medium text-slate-50">
                        По заказу
                    </p>
                    <p class="mt-1 text-sm text-slate-300">
                        Звонок или Telegram — самый короткий путь, если вопрос срочный.
                    </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                        02
                    </p>
                    <p class="mt-2 font-medium text-slate-50">
                        По сотрудничеству
                    </p>
                    <p class="mt-1 text-sm text-slate-300">
                        Лучше писать на email, чтобы не потерялись детали и контакты.
                    </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                        03
                    </p>
                    <p class="mt-2 font-medium text-slate-50">
                        По акциям и новостям
                    </p>
                    <p class="mt-1 text-sm text-slate-300">
                        Удобнее всего следить в Telegram и на сайте.
                    </p>
                </div>
            </div>
        </SecondaryContentBlock>
    </SecondaryPageLayout>
</template>

<style scoped></style>
