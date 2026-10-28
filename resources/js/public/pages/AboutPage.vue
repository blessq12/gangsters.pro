<script setup>
import { useAppDesign } from "../design/useAppDesign";

const a = useAppDesign().components.pages.about;

const offer = [
    {
        index: "01",
        title: "Полный комплект",
        body: "Васаби, имбирь и палочки в заказе — без доплат.",
    },
    {
        index: "02",
        title: "Большие порции",
        body: "Адекватный вес, крупные куски, без воздуха в коробке.",
    },
    {
        index: "03",
        title: "Лосось без экономии",
        body: "Больше филе — больше вкуса.",
    },
];

const quality = [
    {
        title: "Не экономим",
        body: "На продуктах не режем — вкус держится на нормальном сырье.",
    },
    {
        title: "Поставщики",
        body: "Работаем с проверенными поставщиками.",
    },
    {
        title: "Свежесть",
        body: "Только свежие ингредиенты — стабильное качество из заказа в заказ.",
    },
];

const orderSteps = [
    {
        icon: "mdi mdi-cart-outline",
        title: "1. Оформление",
        body: "Собираете заказ, указываете адрес и сразу видите условия доставки.",
    },
    {
        icon: "mdi mdi-check-decagram-outline",
        title: "2. Подтверждение",
        body: "Заказ принят после подтверждения оператором или системой.",
    },
    {
        icon: "mdi mdi-fire-circle",
        title: "3. Приготовление",
        body: "Кухня готовит и собирает заказ, чтобы не терять качество.",
    },
    {
        icon: "mdi mdi-moped-outline",
        title: "4. Доставка",
        body: "Курьер везёт заказ в согласованное время.",
    },
];
</script>

<template>
    <SecondaryPageLayout
        title="О компании"
        eyebrow="7+ лет на рынке"
        description="Готовим и доставляем роллы, горячее и комбо уже больше семи лет."
        :breadcrumbs="['Главная', 'О компании']"
        hero-image="/images/about_us_banner.jpg"
        :stats="[
            { label: 'На рынке', value: '7+ лет' },
            { label: 'Клиентов', value: '2000+' },
            { label: 'Комплект', value: 'Без доплат' },
        ]"
    >
        <div :class="a.stack">
            <section :class="a.brandPanel">
                <div :class="a.brandLogoWrap">
                    <img
                        src="/images/load_logo.svg"
                        alt="Gangsters"
                        :class="a.brandLogo"
                    />
                </div>
                <div :class="a.brandCopy">
                    <p :class="a.brandOverline">Gangsters</p>
                    <h2 :class="a.brandTitle">
                        Своя кухня. Свои правила.
                    </h2>
                    <p :class="a.brandBody">
                        Предлагаем свежие и вкусные блюда японской кухни —
                        с любовью к делу и вниманием к деталям. Готовим из
                        продуктов проверенных поставщиков, следим за качеством
                        и весом порций: в коробке еда, а не воздух, плюс полный
                        комплект к заказу. Быстрая доставка — не выходя из дома.
                    </p>
                </div>
            </section>

            <section>
                <header :class="a.sectionHead">
                    <h2 :class="a.sectionTitle">Что получаете</h2>
                </header>

                <ul :class="a.offerList">
                    <li
                        v-for="item in offer"
                        :key="item.index"
                        :class="a.offerCard"
                    >
                        <span :class="a.offerIndex">{{ item.index }}</span>
                        <h3 :class="a.offerTitle">
                            {{ item.title }}
                        </h3>
                        <p :class="a.offerBody">
                            {{ item.body }}
                        </p>
                    </li>
                </ul>
            </section>

            <section :class="a.qualityPanel">
                <header :class="a.sectionHead">
                    <p :class="a.sectionOverline">Качество</p>
                    <h2 :class="a.sectionTitle">Как держим уровень</h2>
                </header>

                <div :class="a.qualityGrid">
                    <article
                        v-for="item in quality"
                        :key="item.title"
                        :class="a.qualityCol"
                    >
                        <h3 :class="a.qualityTitle">
                            {{ item.title }}
                        </h3>
                        <p :class="a.qualityBody">
                            {{ item.body }}
                        </p>
                    </article>
                </div>
            </section>

            <section>
                <header :class="a.sectionHead">
                    <h2 :class="a.sectionTitle">Как проходит заказ</h2>
                </header>

                <div :class="a.stepsGrid">
                    <article
                        v-for="step in orderSteps"
                        :key="step.title"
                        :class="a.stepCard"
                    >
                        <div :class="a.stepIconWrap">
                            <i
                                class="text-xl"
                                :class="step.icon"
                            ></i>
                        </div>
                        <p :class="a.stepTitle">
                            {{ step.title }}
                        </p>
                        <p :class="a.stepBody">
                            {{ step.body }}
                        </p>
                    </article>
                </div>
            </section>
        </div>
    </SecondaryPageLayout>
</template>
