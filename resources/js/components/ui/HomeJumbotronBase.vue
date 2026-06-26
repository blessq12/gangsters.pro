<script setup>
import "swiper/css";
import { computed, onBeforeUnmount, ref } from "vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import { useMarketingReadModel } from "../../features/marketing/useMarketingReadModel";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    variant: {
        type: String,
        default: "desktop",
        validator: (value) => ["desktop", "mobile"].includes(value),
    },
});

const { banners, loading } = useMarketingReadModel({ autoload: true });
const isMobile = computed(() => props.variant === "mobile");

const j = useAppDesign().components.home.jumbotron;
const jShared = j.shared;
const jVar = computed(() => (isMobile.value ? j.mobile : j.desktop));

const slides = computed(() =>
    (banners.value || []).map((banner, index) => ({
        id: banner.id ?? index,
        image: isMobile.value
            ? banner.image_mobile || banner.image_desktop || ""
            : banner.image_desktop || banner.image_mobile || "",
    })),
);

const isLoading = computed(() => loading.value.banners);

const desktopSlidesPerView = computed(() => {
    const total = slides.value.length;
    if (total >= 5) {
        return 5;
    }
    if (total === 4) {
        return 4;
    }
    if (total === 3) {
        return 3;
    }
    if (total === 2) {
        return 1.24;
    }
    return 1;
});

const desktopQuintet = computed(
    () => !isMobile.value && slides.value.length >= 5,
);

const loopReady = computed(() => {
    const total = slides.value.length;
    if (total < 3) {
        return false;
    }
    if (isMobile.value) {
        return true;
    }
    return total >= desktopSlidesPerView.value;
});

const rewindEnabled = computed(() => slides.value.length > 1 && !loopReady.value);

const swiperBreakpoints = computed(() => {
    if (isMobile.value) {
        return {
            0: { slidesPerView: 1.18, spaceBetween: 10 },
            480: { slidesPerView: 1.24 },
        };
    }

    const slidesPerView = desktopSlidesPerView.value;
    const spaceBetween = slidesPerView >= 5 ? 10 : 8;

    return {
        0: { slidesPerView, spaceBetween },
        768: { slidesPerView, spaceBetween },
        1024: { slidesPerView, spaceBetween },
        1280: { slidesPerView, spaceBetween },
    };
});

const watchSlidesProgress = computed(() => desktopQuintet.value);

const swiperRef = ref(null);

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

const swiperRemountKey = computed(
    () => `${props.variant}-${slides.value.length}`,
);

onBeforeUnmount(() => {
    const swiper = swiperRef.value;
    if (!swiper?.destroyed) {
        swiper.destroy(true, true);
    }
    swiperRef.value = null;
});
</script>

<template>
    <section
        :class="[
            jVar.sectionRoot,
            { 'home-jumbotron--desktop-quintet': desktopQuintet },
        ]"
    >
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
                :key="swiperRemountKey"
                :loop="loopReady"
                :rewind="rewindEnabled"
                :looped-slides="loopReady ? slides.length : 0"
                :loop-additional-slides="loopReady ? slides.length : 0"
                :autoplay="slides.length > 1 ? { delay: 4500 } : false"
                :speed="700"
                :space-between="isMobile ? 10 : 8"
                :centered-slides="true"
                :watch-slides-progress="watchSlidesProgress"
                :slide-to-clicked-slide="true"
                :breakpoints="swiperBreakpoints"
                :observer="false"
                :observe-parents="false"
                :resize-observer="false"
                :class="jShared.swiperOverflow"
                @swiper="handleSwiperInit"
            >
                <SwiperSlide v-for="(slide, index) in slides" :key="slide.id ?? index">
                    <div :class="[jShared.slideInnerFlex, jVar.slidePadY]">
                        <div :class="jShared.cardFrame">
                            <div :class="jShared.mediaSlot">
                                <img
                                    :src="slide.image"
                                    :alt="`Баннер ${index + 1}`"
                                    :class="jShared.slideImage"
                                    :width="isMobile ? 900 : 1600"
                                    :height="isMobile ? 1200 : 1200"
                                    :loading="index === 0 ? 'eager' : 'lazy'"
                                    decoding="async"
                                />
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
 * Медиа: десктоп 4:3 с max-height (dvh), мобила 3:4; object-contain, без обрезки.
 */
.home-jumbotron--mobile .home-jumbotron-card {
    width: 100%;
    max-width: 100%;
}

/* Мобила: рекламный арт 3:4 */
.home-jumbotron--mobile .home-jumbotron-aspect-slot {
    width: min(100%, 960px);
    max-width: 100%;
    aspect-ratio: 3 / 4;
    margin-inline: auto;
}

.home-jumbotron--desktop .home-jumbotron-card {
    width: 100%;
    max-width: 100%;
}

/* Десктоп: рекламный арт 4:3 — height-first, блок контента, не hero на весь экран */
.home-jumbotron--desktop .home-jumbotron-aspect-slot {
    --jh-d-h: min(40dvh, 520px);
    width: min(100%, 1600px, calc(var(--jh-d-h) * 4 / 3));
    max-height: var(--jh-d-h);
    max-width: 100%;
    aspect-ratio: 4 / 3;
    margin-inline: auto;
}

@media (min-width: 1024px) {
    .home-jumbotron--desktop .home-jumbotron-aspect-slot {
        --jh-d-h: min(42dvh, 560px);
    }
}

@media (min-width: 1280px) {
    .home-jumbotron--desktop .home-jumbotron-aspect-slot {
        --jh-d-h: min(44dvh, 600px);
    }
}

@media (min-width: 1536px) {
    .home-jumbotron--desktop .home-jumbotron-aspect-slot {
        --jh-d-h: min(46dvh, 640px);
    }
}

/* Пятёрка: размер карточки от height-cap, не от ширины колонки слайда */
.home-jumbotron--desktop-quintet .home-jumbotron-aspect-slot {
    width: min(calc(var(--jh-d-h) * 4 / 3), 1600px);
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
    transform: scale(1);
    opacity: 1;
    border-color: var(--app-slide-border-accent);
    box-shadow: 0 24px 50px rgba(0, 0, 0, 0.45);
    z-index: 10;
}

/* Пятёрка: внешняя пара (±2 от active) — видимые, но не prev/next */
.home-jumbotron--desktop-quintet :deep(.swiper-slide-visible:not(.swiper-slide-active):not(.swiper-slide-prev):not(.swiper-slide-next) .home-jumbotron-card) {
    transform: scale(0.78);
    opacity: 0.38;
    cursor: pointer;
    border-color: var(--app-slide-border-dim);
}

.home-jumbotron--desktop-quintet :deep(.swiper-slide-visible:not(.swiper-slide-active):not(.swiper-slide-prev):not(.swiper-slide-next) .home-jumbotron-card:hover) {
    opacity: 0.52;
}

.home-jumbotron--desktop-quintet {
    overflow-x: clip;
}

.home-jumbotron--desktop-quintet :deep(.swiper),
.home-jumbotron--desktop-quintet :deep(.swiper-wrapper),
.home-jumbotron--desktop-quintet :deep(.swiper-slide) {
    overflow: visible !important;
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
        transform: scale(1);
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
