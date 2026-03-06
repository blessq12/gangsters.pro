<script setup>
import { ref } from "vue";
import { useFloatLoop } from "../../composables/animations/useFloatLoop";

const promos = [
    {
        title: 'Комбо "Гангстерский вечер"',
        description:
            "Большой сет роллов, горячее блюдо и напитки по спеццене для шумной компании.",
        tag: "-20%",
        image: "/images/promos/promo1.png",
    },
    {
        title: "2 по цене 1",
        description:
            "Выбранные роллы во вторник и четверг: второй сет в подарок.",
        tag: "1+1",
        image: "/images/promos/promo2.png",
    },
    {
        title: "Бесплатная доставка",
        description:
            "При заказе от 1500 ₽ доставка в пределах города за наш счёт.",
        tag: "FREE",
        image: "/images/promos/promo3.png",
    },
];

const showModal = ref(false);
const activePromo = ref(null);

const promoRefs = ref([]);

const registerPromo = (el, index) => {
    if (el) {
        promoRefs.value[index] = el;
    }
};

useFloatLoop(promoRefs, {
    y: 7,
    x: 5,
    duration: 3.2,
    stagger: 0.25,
});

const openPromo = (promo) => {
    activePromo.value = promo;
    showModal.value = true;
};
</script>

<template>
    <section class="my-12">
        <h2 class="text-lg sm:text-xl font-semibold text-slate-100 mb-4">
            Актуальные акции
        </h2>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
            <article
                v-for="(promo, index) in promos"
                :key="index"
                :ref="(el) => registerPromo(el, index)"
                class="group rounded-2xl relative cursor-pointer"
                @click="openPromo(promo)"
            >
                <div class="aspect-[16/9] w-full overflow-hidden">
                    <img
                        :src="promo.image"
                        :alt="promo.title"
                        class="h-full w-full object-cover grayscale group-hover:grayscale-0 transition-transform duration-500 ease-out group-hover:scale-105"
                    />
                </div>
            </article>
        </div>

        <BaseModal v-model="showModal" v-if="activePromo">
            <template #header>{{ activePromo.title }}</template>
            <div class="space-y-3">
                <div class="aspect-[16/9] w-full overflow-hidden rounded-xl">
                    <img
                        :src="activePromo.image"
                        :alt="activePromo.title"
                        class="h-full w-full object-cover"
                    />
                </div>
                <p class="text-sm text-slate-100 leading-relaxed">
                    {{ activePromo.description }}
                </p>
            </div>
        </BaseModal>
    </section>
</template>

<style scoped></style>
