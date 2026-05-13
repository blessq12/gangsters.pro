<script setup>
import "swiper/css";
import { computed, ref } from "vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import { useSystemStore } from "../../stores/systemStore";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    variant: {
        type: String,
        default: "desktop",
        validator: (value) => ["desktop", "mobile"].includes(value),
    },
});

const systemStore = useSystemStore();
const isMobile = computed(() => props.variant === "mobile");

const j = useAppDesign().components.home.jumbotron;
const jShared = j.shared;
const jVar = computed(() => (isMobile.value ? j.mobile : j.desktop));

const slides = computed(() =>
    (systemStore.banners || []).map((banner) => ({
        title: banner.title || "",
        description: banner.description || "",
        image: isMobile.value
            ? banner.image_mobile || banner.image || banner.image_desktop || ""
            : banner.image_desktop || banner.image || banner.image_mobile || "",
    })),
);

const isLoading = computed(() => systemStore.loadingBanners);
const swiperRef = ref(null);
const loopReady = computed(() => slides.value.length >= 3);
const rewindEnabled = computed(() => slides.value.length > 1 && !loopReady.value);

const swiperBreakpoints = computed(() =>
    isMobile.value
        ? {
              0: { slidesPerView: 1.18, spaceBetween: 10 },
              480: { slidesPerView: 1.24 },
          }
        : {
              0: { slidesPerView: 1.08, spaceBetween: 8 },
              768: { slidesPerView: 1.12, spaceBetween: 8 },
              1024: { slidesPerView: 1.18, spaceBetween: 10 },
              1280: { slidesPerView: 1.24, spaceBetween: 10 },
          },
);

const handleSwiperInit = (swiper) => {
    if (!swiper) return;
    swiperRef.value = swiper;
    swiper.slidePrev(0);
};

const goPrev = () => {
    const swiper = swiperRef.value;
    const total = slides.value.length;
    if (!swiper || total < 2) return;

    const targetIndex = (swiper.realIndex - 1 + total) % total;
    if (loopReady.value) {
        swiper.slideToLoop(targetIndex);
        return;
    }

    swiper.slideTo(targetIndex);
};

const goNext = () => {
    const swiper = swiperRef.value;
    const total = slides.value.length;
    if (!swiper || total < 2) return;

    const targetIndex = (swiper.realIndex + 1) % total;
    if (loopReady.value) {
        swiper.slideToLoop(targetIndex);
        return;
    }

    swiper.slideTo(targetIndex);
};
</script>

<template>
    <section :class="jVar.sectionRoot">
        <div :class="jShared.backdropLayer">
            <div :class="jVar.glowLeft"></div>
            <div :class="jVar.glowRight"></div>
        </div>

        <div :class="jVar.innerWrap">
            <div
                v-if="isLoading"
                :class="jVar.loadingRow"
            >
                <div :class="jShared.loadingSlot"></div>
            </div>

            <Swiper
                v-else-if="slides.length"
                :loop="loopReady"
                :rewind="rewindEnabled"
                :looped-slides="loopReady ? slides.length : 0"
                :loop-additional-slides="loopReady ? slides.length : 0"
                :autoplay="slides.length > 1 ? { delay: 4500 } : false"
                :speed="700"
                :space-between="isMobile ? 10 : 8"
                :centered-slides="true"
                :watch-slides-progress="false"
                :slide-to-clicked-slide="true"
                :breakpoints="swiperBreakpoints"
                :observer="false"
                :observe-parents="false"
                :resize-observer="false"
                :class="jShared.swiperOverflow"
                @swiper="handleSwiperInit"
            >
                <SwiperSlide v-for="(slide, index) in slides" :key="index">
                    <div :class="[jShared.slideInnerFlex, jVar.slidePadY]">
                        <div :class="jShared.cardFrame">
                            <div :class="jShared.mediaSlot">
                                <img
                                    :src="slide.image"
                                    :alt="slide.title"
                                    :class="jShared.slideImage"
                                    :width="isMobile ? 900 : 1920"
                                    :height="isMobile ? 1200 : 1080"
                                    :loading="index === 0 ? 'eager' : 'lazy'"
                                    decoding="async"
                                />
                            </div>
                            <div :class="jShared.gradientScrim"></div>
                            <div :class="jVar.badgeBrand">
                                Gangsters
                            </div>
                            <div :class="jVar.badgeCounter">
                                {{ String(index + 1).padStart(2, "0") }}/{{ String(slides.length).padStart(2, "0") }}
                            </div>
                            <div :class="jVar.captionPanel">
                                <h1 :class="jVar.title">
                                    {{ slide.title }}
                                </h1>
                                <p :class="jVar.description">
                                    {{ slide.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </SwiperSlide>
            </Swiper>

            <div
                v-if="!isMobile && slides.length > 1"
                :class="jShared.navRail"
            >
                <button
                    type="button"
                    :class="jShared.navBtn"
                    aria-label="Предыдущий слайд"
                    @click="goPrev"
                >
                    <i class="mdi mdi-chevron-left text-2xl" aria-hidden="true"></i>
                </button>
                <button
                    type="button"
                    :class="jShared.navBtn"
                    aria-label="Следующий слайд"
                    @click="goNext"
                >
                    <i class="mdi mdi-chevron-right text-2xl" aria-hidden="true"></i>
                </button>
            </div>

            <div
                v-if="!isLoading && !slides.length"
                :class="jShared.emptyState"
            >
                Баннеры скоро появятся.
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Переменные для анимации border-color слайдов (иначе var() пустой → мерцание острова). */
.home-jumbotron {
    --app-slide-border-dim: color-mix(in srgb, var(--app-canvas-fg, #ececec) 12%, transparent);
    --app-slide-border-accent: color-mix(in srgb, var(--app-accent, #c62424) 50%, transparent);
}

/*
 * Карточка всегда w-full от слайда — иначе fit-content + min(100%,…) даёт цикл и ширина 0.
 * Медиа: min(100%, …) от карточки; десктоп = витрина под 1920×1080 (16:9), мобила = 3:4.
 */
.home-jumbotron--mobile .home-jumbotron-card {
    width: 100%;
    max-width: 100%;
}

/* Мобила: арт 3:4 (выше слот — запас под scale активного слайда) */
.home-jumbotron--mobile .home-jumbotron-aspect-slot {
    --jh-m-h: min(88dvh, 960px);
    width: min(100%, calc(var(--jh-m-h) * 3 / 4));
    max-height: var(--jh-m-h);
    aspect-ratio: 3 / 4;
    max-width: 100%;
    margin-inline: auto;
}

@media (orientation: landscape) {
    .home-jumbotron--mobile .home-jumbotron-aspect-slot {
        --jh-ml-h: min(88dvh, 520px);
        width: min(100%, calc(var(--jh-ml-h) * 16 / 9));
        max-height: var(--jh-ml-h);
        aspect-ratio: 16 / 9;
    }
}

.home-jumbotron--desktop .home-jumbotron-card {
    width: 100%;
    max-width: 100%;
}

/* Десктоп: витрина под 1920×1080 (16:9) */
.home-jumbotron--desktop .home-jumbotron-aspect-slot {
    --jh-d-h: min(75dvh, 1080px);
    width: min(100%, 1920px, calc(var(--jh-d-h) * 16 / 9));
    max-height: var(--jh-d-h);
    aspect-ratio: 16 / 9;
    max-width: 100%;
    margin-inline: auto;
}

.home-jumbotron--desktop :deep(.swiper-slide) {
    min-width: 0;
}

.home-jumbotron--mobile :deep(.swiper-slide) {
    min-width: 0;
}

.home-jumbotron--desktop :deep(.swiper-slide .home-jumbotron-card) {
    transform: scale(0.86);
    opacity: 0;
    border-color: transparent;
    transition:
        transform 500ms ease,
        opacity 500ms ease,
        border-color 500ms ease,
        box-shadow 500ms ease;
}

.home-jumbotron--desktop :deep(.swiper-slide-prev .home-jumbotron-card),
.home-jumbotron--desktop :deep(.swiper-slide-next .home-jumbotron-card) {
    transform: scale(0.92);
    opacity: 0.55;
    cursor: pointer;
    border-color: var(--app-slide-border-dim);
}

.home-jumbotron--desktop :deep(.swiper-slide-prev .home-jumbotron-card:hover),
.home-jumbotron--desktop :deep(.swiper-slide-next .home-jumbotron-card:hover) {
    opacity: 0.7;
}

.home-jumbotron--desktop :deep(.swiper-slide-active .home-jumbotron-card) {
    transform: scale(1.02);
    opacity: 1;
    border-color: var(--app-slide-border-accent);
    box-shadow: 0 24px 50px rgba(0, 0, 0, 0.45);
    z-index: 10;
}

@media (max-width: 767px) {
    .home-jumbotron--desktop :deep(.swiper-slide .home-jumbotron-card) {
        transform: scale(0.82);
    }

    .home-jumbotron--desktop :deep(.swiper-slide-prev .home-jumbotron-card),
    .home-jumbotron--desktop :deep(.swiper-slide-next .home-jumbotron-card) {
        transform: scale(0.9);
        opacity: 0.58;
    }

    .home-jumbotron--desktop :deep(.swiper-slide-active .home-jumbotron-card) {
        transform: scale(1.02);
        box-shadow: 0 20px 42px rgba(0, 0, 0, 0.42);
    }
}

.home-jumbotron--mobile :deep(.swiper),
.home-jumbotron--mobile :deep(.swiper-wrapper),
.home-jumbotron--mobile :deep(.swiper-slide),
.home-jumbotron--mobile :deep(.swiper-3d) {
    overflow-x: visible !important;
    overflow-y: visible !important;
    overflow: visible !important;
}

.home-jumbotron--mobile :deep(.swiper .swiper-wrapper),
.home-jumbotron--mobile :deep(.swiper .swiper-slide),
.home-jumbotron--mobile :deep(.swiper .swiper-3d),
.home-jumbotron--mobile :deep(.swiper-slide-active) {
    overflow: visible !important;
}

.home-jumbotron--mobile :deep(.swiper),
.home-jumbotron--mobile :deep(.swiper-wrapper) {
    height: auto !important;
    min-height: 0 !important;
}

.home-jumbotron--mobile :deep(.swiper-wrapper) {
    align-items: stretch !important;
}

.home-jumbotron--mobile :deep(.swiper-slide) {
    padding-top: clamp(18px, 5dvh, 36px) !important;
    padding-bottom: clamp(18px, 5dvh, 36px) !important;
    box-sizing: border-box;
}

.home-jumbotron--mobile :deep(.swiper-slide .home-jumbotron-card) {
    transform: scale(0.78);
    opacity: 0;
    border-color: transparent;
    transition:
        transform 500ms ease,
        opacity 500ms ease,
        border-color 500ms ease,
        box-shadow 500ms ease;
}

.home-jumbotron--mobile :deep(.swiper-slide-prev .home-jumbotron-card),
.home-jumbotron--mobile :deep(.swiper-slide-next .home-jumbotron-card) {
    transform: scale(0.86);
    opacity: 0.58;
    cursor: pointer;
    border-color: var(--app-slide-border-dim);
}

.home-jumbotron--mobile :deep(.swiper-slide-prev .home-jumbotron-card:hover),
.home-jumbotron--mobile :deep(.swiper-slide-next .home-jumbotron-card:hover) {
    opacity: 0.7;
}

.home-jumbotron--mobile :deep(.swiper-slide-active .home-jumbotron-card) {
    transform: scale(1.14);
    opacity: 1;
    border-color: var(--app-slide-border-accent);
    box-shadow: 0 24px 50px rgba(0, 0, 0, 0.45);
    z-index: 10;
}
</style>
