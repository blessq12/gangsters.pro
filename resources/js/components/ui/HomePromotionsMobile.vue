<script setup>
import { computed, ref } from "vue";
import "swiper/css";
import { Autoplay } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/vue";
import { useMarketingReadModel } from "../../features/marketing/useMarketingReadModel";
import { hasDocumentBody } from "../../utils/system/documentBody";
import { useAppDesign } from "../../design/useAppDesign";

const { promotions, loading } = useMarketingReadModel({ autoload: true });

const hp = useAppDesign().components.home.promotions;
const hpShared = hp.shared;
const hpMob = hp.mobileSplit;
const swiperModules = [Autoplay];

const promos = computed(() =>
    (promotions.value || []).map((promo) => ({
        id: promo.id,
        title: promo.title || "",
        description: promo.description || "",
        body: promo.body || "",
        image: promo.image || "",
        hasHtmlBody: hasDocumentBody(promo.body),
    })),
);
const loopReady = computed(() => promos.value.length >= 3);
const rewindEnabled = computed(() => promos.value.length > 1 && !loopReady.value);

const isLoading = computed(() => loading.value.promotions);

const showModal = ref(false);
const activePromo = ref(null);

const openPromo = (promo) => {
    activePromo.value = promo;
    showModal.value = true;
};
</script>

<template>
    <section :class="hpShared.section">
        <h2 :class="hpMob.heading">
            Актуальные акции
        </h2>

        <template v-if="isLoading">
            <div :class="hpMob.loadingRow">
                <div
                    v-for="index in 3"
                    :key="index"
                    :class="hpMob.loadingCard"
                >
                    <div :class="hpShared.pulseInner" />
                </div>
            </div>
        </template>

        <template v-else-if="promos.length">
            <Swiper
                :modules="swiperModules"
                :slides-per-view="1.15"
                :space-between="12"
                :loop="loopReady"
                :rewind="rewindEnabled"
                :autoplay="
                    promos.length > 1
                        ? { delay: 3200, disableOnInteraction: false, pauseOnMouseEnter: true }
                        : false
                "
                :class="hpShared.swiperHook"
            >
                <SwiperSlide v-for="(promo, index) in promos" :key="promo.id ?? index">
                    <article
                        :class="hpMob.article"
                        @click="openPromo(promo)"
                    >
                        <div :class="hpShared.thumbWrap">
                            <img
                                :src="promo.image"
                                :alt="promo.title"
                                :class="hpShared.thumbImg"
                            />
                        </div>
                    </article>
                </SwiperSlide>
            </Swiper>
        </template>

        <template v-else>
            <div :class="hpShared.emptyText">
                Акции скоро появятся.
            </div>
        </template>

        <BaseModal v-model="showModal" v-if="activePromo">
            <template #header>{{ activePromo.title }}</template>
            <div :class="hpShared.modalStack">
                <div :class="hpShared.modalMedia">
                    <img
                        :src="activePromo.image"
                        :alt="activePromo.title"
                        :class="hpShared.modalImg"
                    />
                </div>
                <div
                    v-if="activePromo.hasHtmlBody"
                    :class="hpShared.modalText"
                    v-html="activePromo.body"
                />
                <p
                    v-else-if="activePromo.description"
                    :class="hpShared.modalText"
                >
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
