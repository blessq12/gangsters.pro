<script>
import gsap from "gsap";
import { mapStores } from "pinia";
import { localStore } from "../../stores/localStore";

export default {
    mounted() {
        this.initializeAnimations();
    },
    computed: {
        ...mapStores(localStore),
    },
    methods: {
        initializeAnimations() {
            if (this.localStore.fav.length) {
                gsap.from(this.$refs.favList, {
                    y: 20,
                    opacity: 0,
                    duration: 0.5,
                    stagger: 0.1,
                    ease: "power3.out",
                });
            }
        },
    },
};
</script>

<template>
    <div class="fav-wrapper py-6 rounded-2xl">
        <transition
            enter-active-class="animate__animated animate__fadeIn"
            leave-active-class="animate__animated animate__fadeOut"
            mode="out-in"
        >
            <div v-if="localStore.fav.length" class="space-y-4">
                <transition-group
                    tag="ul"
                    class="fav-list space-y-3"
                    enter-active-class="animate__animated animate__fadeInUp"
                    leave-active-class="animate__animated animate__fadeOutDown"
                    move-class="transition-all duration-300"
                >
                    <li
                        v-for="item in localStore.fav"
                        :key="item.id"
                        class="bg-white rounded-xl shadow-sm p-4"
                        ref="favList"
                    >
                        <ProductComponentSmall
                            :product="item"
                            :is-favorite="true"
                        />
                    </li>
                </transition-group>

                <div
                    class="fav-footer bg-white rounded-xl shadow-sm p-4 space-y-2"
                >
                    <div
                        class="flex justify-between items-center text-gray-700"
                    >
                        <span class="font-medium">Стоимость:</span>
                        <span class="font-semibold text-primary-600">
                            {{ localStore.favTotal }} ₽
                        </span>
                    </div>
                    <div
                        class="flex justify-between items-center text-gray-700"
                    >
                        <span class="font-medium">Наборов:</span>
                        <span class="font-semibold">
                            {{ localStore.fav.length }} шт
                        </span>
                    </div>
                </div>

                <button
                    class="w-full btn bg-red-500 hover:bg-red-600 text-white rounded-xl py-3 transition-colors"
                    @click="localStore.clearStore('fav')"
                >
                    <i class="mdi mdi-trash-can mr-2"></i>
                    Очистить избранное
                </button>
            </div>
            <div v-else class="text-center">
                <img
                    src="/images/placeholder/empty-fav.png"
                    alt="Пустой список избранного"
                    class="mx-auto max-h-[280px]"
                />
                <div class="space-y-2">
                    <h5 class="text-3xl font-semibold text-gray-800">
                        В избранном ничего нет
                    </h5>
                    <p class="text-gray-500 text-sm max-w-[300px] mx-auto">
                        В избранном можно хранить понравившиеся позиции и
                        добавлять их в корзину
                    </p>
                </div>
            </div>
        </transition>
    </div>
</template>

<style lang="sass" scoped>
.fav-list
    li
        transform-origin: center
        will-change: transform, opacity
</style>
