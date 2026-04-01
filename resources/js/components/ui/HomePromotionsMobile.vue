<script setup>
import { computed, ref } from "vue";
import "swiper/css";
import { Autoplay } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/vue";
import { useSystemStore } from "../../stores/systemStore";

const systemStore = useSystemStore();
const swiperModules = [Autoplay];

const promos = computed(() =>
    (systemStore.promotions || []).map((promo) => ({
        title: promo.title || "",
        description: promo.description || "",
        image: promo.image || "",
    })),
);

const isLoading = computed(() => systemStore.loadingPromotions);

const showModal = ref(false);
const activePromo = ref(null);

const openPromo = (promo) => {
    activePromo.value = promo;
    showModal.value = true;
};
</script>

<template>
    <section class="my-12">
        <h2 class="mb-4 text-lg font-semibold text-slate-100">Актуальные акции</h2>

        <template v-if="isLoading">
            <div class="flex gap-3 overflow-x-auto px-1 pb-2">
                <div
                    v-for="index in 3"
                    :key="index"
                    class="flex-none w-[18rem] rounded-2xl border border-white/10 bg-slate-800/60 animate-pulse"
                >
                    <div class="aspect-[16/9] w-full rounded-2xl" />
                </div>
            </div>
        </template>

        <template v-else-if="promos.length">
            <Swiper
                :modules="swiperModules"
                :slides-per-view="1.15"
                :space-between="12"
                :loop="promos.length > 1"
                :autoplay="
                    promos.length > 1
                        ? { delay: 3200, disableOnInteraction: false, pauseOnMouseEnter: true }
                        : false
                "
                class="promos-swiper"
            >
                <SwiperSlide v-for="(promo, index) in promos" :key="index">
                    <article
                        class="group relative cursor-pointer overflow-hidden rounded-2xl"
                        @click="openPromo(promo)"
                    >
                        <div class="aspect-[16/9] w-full overflow-hidden rounded-2xl">
                            <img
                                :src="promo.image"
                                :alt="promo.title"
                                class="h-full w-full object-cover grayscale transition-transform duration-500 ease-out group-hover:scale-105 group-hover:grayscale-0"
                            />
                        </div>
                    </article>
                </SwiperSlide>
            </Swiper>
        </template>

        <template v-else>
            <div class="py-4 text-center text-xs text-slate-500">
                Акции скоро появятся.
            </div>
        </template>

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
                <p class="text-sm leading-relaxed text-slate-100">
                    {{ activePromo.description }}
                </p>
            </div>
        </BaseModal>
    </section>
</template>

<style scoped>
.promos-swiper :deep(.swiper-slide) {
    height: auto;
}
</style>
