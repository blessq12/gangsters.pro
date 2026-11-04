<script setup>
import { computed, nextTick, onMounted, ref } from "vue";
import "swiper/css";
import "swiper/css/navigation";
import { Autoplay, Navigation } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/vue";
import { useAppDesign } from "../../design/useAppDesign";

const swiperModules = [Autoplay, Navigation];

const props = defineProps({
    images: {
        type: Array,
        default: () => [],
    },
    alt: {
        type: String,
        default: "",
    },
});

const dg = useAppDesign().components.catalog.modal.gallery;

const ready = ref(false);

onMounted(() => {
    nextTick(() => {
        ready.value = true;
    });
});

const slides = computed(() => {
    const list = props.images;
    if (!Array.isArray(list)) return [];

    return list
        .map((item) => {
            if (typeof item === "string") {
                return { url: item, objectPosition: "50% 50%" };
            }
            const url = item?.url;
            if (!url) return null;
            const objectPosition =
                typeof item.objectPosition === "string" && item.objectPosition.trim()
                    ? item.objectPosition
                    : "50% 50%";
            return { url, objectPosition };
        })
        .filter(Boolean);
});
</script>

<template>
    <div :class="dg.root">
        <div v-if="!slides.length" :class="dg.noPhoto">
            {{ dg.noPhotoLabel }}
        </div>
        <!-- Слайдер монтируем после nextTick, чтобы контейнер уже был в DOM (модалка не падает) -->
        <template v-else-if="ready">
            <Swiper
                :modules="swiperModules"
                :slides-per-view="1"
                :space-between="0"
                :loop="slides.length > 1"
                :navigation="slides.length > 1"
                :autoplay="{ delay: 3500, disableOnInteraction: false }"
                :class="dg.swiper"
            >
                <SwiperSlide
                    v-for="(slide, index) in slides"
                    :key="`${slide.url}-${index}`"
                    :class="dg.slide"
                >
                    <img
                        :src="slide.url"
                        :alt="alt ? `${alt} — фото ${index + 1}` : ''"
                        :class="dg.img"
                        :style="{ objectPosition: slide.objectPosition }"
                    />
                </SwiperSlide>
            </Swiper>
        </template>
        <div v-else :class="dg.wrap">
            <img
                :src="slides[0].url"
                :alt="alt || 'Фото товара'"
                :class="dg.img"
                :style="{ objectPosition: slides[0].objectPosition }"
            />
        </div>
    </div>
</template>

<style scoped>
:deep(.swiper-wrapper) {
    height: 100%;
}

:deep(.swiper-button-prev),
:deep(.swiper-button-next) {
    color: #fcd34d;
}

:deep(.swiper-button-prev::after),
:deep(.swiper-button-next::after) {
    font-size: 1.25rem;
}
</style>
