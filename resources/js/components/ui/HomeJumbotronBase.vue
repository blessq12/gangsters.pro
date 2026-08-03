<script setup>
import "swiper/css";
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { Autoplay } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/vue";
import { storeToRefs } from "pinia";
import { useContentStore } from "../../stores/contentStore";
import { useAppDesign } from "../../design/useAppDesign";

const AUTOPLAY_INTERVAL_MS = 30000;
const ZOOM_SCALE = 1.12;

const props = defineProps({
    variant: {
        type: String,
        default: "desktop",
        validator: (value) => ["desktop", "mobile"].includes(value),
    },
});

const contentStore = useContentStore();
const { banners, loading } = storeToRefs(contentStore);
const isMobile = computed(() => props.variant === "mobile");

const j = useAppDesign().components.home.jumbotron;
const jShared = j.shared;
const jVar = computed(() => (isMobile.value ? j.mobile : j.desktop));

const swiperModules = [Autoplay];
const shuffledBanners = ref([]);
let bannersSignature = "";

function shuffleFisherYates(items) {
    const copy = [...items];
    for (let i = copy.length - 1; i > 0; i -= 1) {
        const randomIndex = Math.floor(Math.random() * (i + 1));
        [copy[i], copy[randomIndex]] = [copy[randomIndex], copy[i]];
    }
    return copy;
}

watch(
    () => banners.value,
    (list) => {
        const source = Array.isArray(list) ? [...list] : [];
        if (!source.length) {
            shuffledBanners.value = [];
            bannersSignature = "";
            return;
        }

        const signature = source.map((banner, index) => banner.id ?? index).join("|");
        if (signature === bannersSignature && shuffledBanners.value.length) {
            return;
        }

        bannersSignature = signature;
        shuffledBanners.value = shuffleFisherYates(source);
    },
    { immediate: true },
);

const slides = computed(() =>
    shuffledBanners.value.map((banner, index) => ({
        id: banner.id ?? index,
        image: isMobile.value
            ? banner.image_mobile || banner.image_desktop || ""
            : banner.image_desktop || banner.image_mobile || "",
    })),
);

const isLoading = computed(() => loading.value && banners.value.length === 0);
const swiperRef = ref(null);
const loopReady = computed(() => slides.value.length >= 3);
const rewindEnabled = computed(() => slides.value.length > 1 && !loopReady.value);
const autoplayOptions = computed(() =>
    slides.value.length > 1
        ? {
              enabled: true,
              delay: AUTOPLAY_INTERVAL_MS,
              disableOnInteraction: false,
              waitForTransition: false,
          }
        : false,
);

const zoomStyle = computed(() => ({
    "--home-jumbotron-naplyv-duration": `${AUTOPLAY_INTERVAL_MS}ms`,
    "--home-jumbotron-naplyv-scale": String(ZOOM_SCALE),
}));

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
    // Do not call slideTo on init: with speed 0, transitionend may never fire
    // and Autoplay stays paused after beforeTransitionStart.
    if (swiper.autoplay && !swiper.autoplay.running) {
        swiper.autoplay.start();
    }
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
    () => `${props.variant}-${slides.value.map((slide) => slide.id).join("-")}`,
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
        :class="jVar.sectionRoot"
        :style="zoomStyle"
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
                :modules="swiperModules"
                :loop="loopReady"
                :rewind="rewindEnabled"
                :looped-slides="loopReady ? slides.length : 0"
                :loop-additional-slides="loopReady ? slides.length : 0"
                :autoplay="autoplayOptions"
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
                <SwiperSlide v-for="(slide, index) in slides" :key="slide.id ?? index">
                    <div :class="[jShared.slideInnerFlex, jVar.slidePadY]">
                        <div :class="jShared.cardFrame">
                            <div
                                :class="
                                    isMobile
                                        ? jShared.mediaSlotMobile
                                        : jShared.mediaSlotDesktop
                                "
                            >
                                <img
                                    :src="slide.image"
                                    :alt="`Баннер ${index + 1}`"
                                    :class="[
                                        isMobile
                                            ? jShared.slideImageMobile
                                            : jShared.slideImageDesktop,
                                        'home-jumbotron-naplyv',
                                    ]"
                                    :width="isMobile ? 900 : 1920"
                                    :height="isMobile ? 1200 : 1080"
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
    --home-jumbotron-naplyv-duration: 30000ms;
    --home-jumbotron-naplyv-scale: 1.12;
}

.home-jumbotron-media {
    overflow: hidden;
}

.home-jumbotron-naplyv {
    transform: scale(1);
    transform-origin: center center;
    will-change: transform;
}

.home-jumbotron :deep(.swiper-slide-active) .home-jumbotron-naplyv {
    animation: home-jumbotron-naplyv var(--home-jumbotron-naplyv-duration) linear forwards;
}

@keyframes home-jumbotron-naplyv {
    from {
        transform: scale(1);
    }

    to {
        transform: scale(var(--home-jumbotron-naplyv-scale));
    }
}

@media (prefers-reduced-motion: reduce) {
    .home-jumbotron :deep(.swiper-slide-active) .home-jumbotron-naplyv {
        animation: none;
    }
}

/*
 * Карточка всегда w-full от слайда — иначе fit-content + min(100%,…) даёт цикл и ширина 0.
 * Мобила: 3:4, object-contain. Десктоп: 16:9 hero, object-cover (до правок 2026-06-26).
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
