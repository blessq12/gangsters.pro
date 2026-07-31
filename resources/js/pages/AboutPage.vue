<script setup>
import { useAppDesign } from "../design/useAppDesign";

const a = useAppDesign().components.pages.about;
</script>

<template>
    <SecondaryPageLayout
        title="О компании"
        eyebrow="История бренда"
        description="Gangsters — это доставка с характером: тёмная эстетика, сочные позиции в меню и сервис, который не рассыпается на мелочах."
        :breadcrumbs="['Главная', 'О компании']"
        hero-image="/images/about_us_banner.jpg"
        :stats="[
            { label: 'Кухни', value: '3 направления' },
            { label: 'Фокус', value: 'Скорость и вкус' },
            { label: 'Тон', value: 'Гангстерский вайб' },
        ]"
    >
        <div :class="a.gridTop">
            <SecondaryContentBlock
                title="Кто мы такие"
                subtitle="ГАСТРО-АТМОСФЕРА"
            >
                <p>
                    Gangsters собрали под одним брендом несколько гастро-настроений:
                    роллы, горячие позиции, комбо и ночные заказы. Нам важен не
                    только вкус, но и то, как человек чувствует себя, когда
                    открывает заказ.
                </p>
                <p>
                    Поэтому у нас всё работает в одной системе: графитовый визуал,
                    тёплые янтарные акценты, читаемое меню, живая подача и сервис
                    без хаоса. Короче, не пластиковая витрина, а еда с характером.
                </p>
            </SecondaryContentBlock>

            <div :class="a.asideStack">
                <article :class="a.spotlightArticle">
                    <div :class="a.spotlightHeader">
                        <span :class="a.eyebrowTag">
                            Характер
                        </span>
                        <i class="mdi mdi-crown-outline text-2xl text-app-accent"></i>
                    </div>
                    <p :class="a.spotlightTitle">
                        Вкус, скорость и атмосфера.
                    </p>
                    <p :class="a.spotlightBody">
                        Мы строим продукт так, чтобы он был заметным визуально и
                        понятным в использовании: без лишней мишуры, но с жирным
                        акцентом на качестве.
                    </p>
                </article>

                <article :class="a.tagsArticle">
                    <p :class="a.tagsKicker">
                        Что отличает
                    </p>
                    <div :class="a.tagsRow">
                        <span :class="a.tagPill">
                            Яркие комбо
                        </span>
                        <span :class="a.tagPill">
                            Ночной режим
                        </span>
                        <span :class="a.tagPill">
                            Живой брендинг
                        </span>
                        <span :class="a.tagPill">
                            Быстрая сборка
                        </span>
                    </div>
                </article>
            </div>
        </div>

        <SecondaryContentBlock
            title="Наши принципы"
            subtitle="НАШИ ПРИНЦИПЫ"
        >
            <div :class="a.principlesGrid">
                <article :class="a.pillarCard">
                    <div :class="a.pillarIconWrap">
                        <i class="mdi mdi-silverware-fork-knife text-xl"></i>
                    </div>
                    <h3 :class="a.pillarTitle">Качество</h3>
                    <p :class="a.pillarText">
                        Ингредиенты и сборка заказа не должны проваливаться даже в
                        час пик. Если блюдо не выглядит аппетитно, оно не должно ехать к гостю.
                    </p>
                </article>

                <article :class="a.pillarCard">
                    <div :class="a.pillarIconWrap">
                        <i class="mdi mdi-lightning-bolt-outline text-xl"></i>
                    </div>
                    <h3 :class="a.pillarTitle">Скорость</h3>
                    <p :class="a.pillarText">
                        Мы проектируем процессы так, чтобы путь от оформления до двери
                        был коротким и понятным. Без лишней ебанины внутри системы.
                    </p>
                </article>

                <article :class="a.pillarCard">
                    <div :class="a.pillarIconWrap">
                        <i class="mdi mdi-movie-open-outline text-xl"></i>
                    </div>
                    <h3 :class="a.pillarTitle">Атмосфера</h3>
                    <p :class="a.pillarText">
                        У Gangsters есть настроение: от главной страницы до упаковки.
                        Заказ должен ощущаться как часть вечера, а не просто чек в пакете.
                    </p>
                </article>
            </div>
        </SecondaryContentBlock>

        <div :class="a.gridBottom">
            <article :class="a.moodArticle">
                <p :class="a.moodEyebrow">
                    Атмосфера Gangsters
                </p>
                <p :class="a.moodTitle">
                    Мы не пытаемся быть «как все доставки». И в этом весь прикол.
                </p>
                <p :class="a.moodBody">
                    Бренд держится на контрасте: тёмный фон, золото, аппетитные
                    фото, мягкое стекло и понятные сценарии взаимодействия.
                </p>
            </article>

            <SecondaryContentBlock
                title="Как строим сервис"
                subtitle="ОТ ЗАКАЗА ДО ДОСТАВКИ"
            >
                <div :class="a.stepsGridInner">
                    <div :class="a.stepMiniCard">
                        <p :class="a.stepMiniKicker">01</p>
                        <p :class="a.stepMiniTitle">Понятный выбор</p>
                        <p :class="a.stepMiniBody">
                            Каталог и акции помогают быстро собрать заказ без лишних кликов.
                        </p>
                    </div>
                    <div :class="a.stepMiniCard">
                        <p :class="a.stepMiniKicker">02</p>
                        <p :class="a.stepMiniTitle">Чёткая сборка</p>
                        <p :class="a.stepMiniBody">
                            Кухня и логистика синхронизированы, чтобы не терять темп.
                        </p>
                    </div>
                    <div :class="a.stepMiniCard">
                        <p :class="a.stepMiniKicker">03</p>
                        <p :class="a.stepMiniTitle">Контроль качества</p>
                        <p :class="a.stepMiniBody">
                            Следим за внешним видом, температурой и полнотой заказа.
                        </p>
                    </div>
                    <div :class="a.stepMiniCard">
                        <p :class="a.stepMiniKicker">04</p>
                        <p :class="a.stepMiniTitle">Нормальный финиш</p>
                        <p :class="a.stepMiniBody">
                            У гостя должно остаться ощущение, что всё отработало красиво.
                        </p>
                    </div>
                </div>
            </SecondaryContentBlock>
        </div>
    </SecondaryPageLayout>
</template>
