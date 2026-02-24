<script setup>
const promos = [
    {
        title: 'Комбо "Гангстерский вечер"',
        description:
            "Большой сет роллов + горячее блюдо + напитки по спеццене.",
        tag: "-20%",
    },
    {
        title: "2 по цене 1",
        description: "На выбранные роллы во вторник и четверг весь день.",
        tag: "1+1",
    },
    {
        title: "Бесплатная доставка",
        description:
            "При заказе от 1500 ₽ доставка в пределах города — за нас.",
        tag: "FREE",
    },
];
</script>

<template>
    <section class="my-12">
        <h2 class="text-lg sm:text-xl font-semibold text-slate-100 mb-4">
            Актуальные акции
        </h2>

        <div class="grid gap-4 sm:grid-cols-3">
            <article
                v-for="(promo, index) in promos"
                :key="index"
                class="rounded-2xl border border-white/10 bg-[rgba(10,10,14,0.9)] px-4 py-4 sm:px-5 sm:py-5 shadow-md shadow-black/40 relative overflow-hidden"
            >
                <div
                    class="pointer-events-none absolute inset-0 opacity-30 mix-blend-screen"
                >
                    <div
                        class="absolute -top-10 -right-6 h-24 w-24 rounded-full bg-amber-500/15 blur-2xl"
                    ></div>
                </div>

                <span
                    class="inline-flex items-center rounded-full border border-amber-400/50 bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-300 mb-2"
                >
                    {{ promo.tag }}
                </span>

                <h3
                    class="text-sm sm:text-base font-semibold text-slate-50 mb-1.5"
                >
                    {{ promo.title }}
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    {{ promo.description }}
                </p>
            </article>
        </div>
    </section>
</template>

<style scoped></style>
