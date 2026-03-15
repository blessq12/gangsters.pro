<script setup>
import { computed, nextTick, onMounted, ref } from "vue";
import "swiper/css";
import "swiper/css/navigation";
import { Autoplay, Navigation } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/vue";

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

const ready = ref(false);

onMounted(() => {
    nextTick(() => {
        ready.value = true;
    });
});

const slideUrls = computed(() => {
    const list = props.images;
    if (!Array.isArray(list)) return [];
    return list
        .map((item) => (typeof item === "string" ? item : item?.url))
        .filter(Boolean);
});
</script>

<template>
    <div class="product-gallery">
        <div v-if="!slideUrls.length" class="product-gallery__no-photo">
            Нет фото
        </div>
        <!-- Слайдер монтируем после nextTick, чтобы контейнер уже был в DOM (модалка не падает) -->
        <template v-else-if="ready">
            <Swiper
                :modules="swiperModules"
                :slides-per-view="1"
                :space-between="0"
                :loop="slideUrls.length > 1"
                :navigation="slideUrls.length > 1"
                :autoplay="{ delay: 3500, disableOnInteraction: false }"
                class="product-gallery__swiper"
            >
                <SwiperSlide
                    v-for="(url, index) in slideUrls"
                    :key="index"
                    class="product-gallery__slide"
                >
                    <img
                        :src="url"
                        :alt="alt ? `${alt} — фото ${index + 1}` : ''"
                        class="product-gallery__img"
                    />
                </SwiperSlide>
            </Swiper>
        </template>
        <div v-else class="product-gallery__wrap">
            <img
                :src="slideUrls[0]"
                :alt="alt || 'Фото товара'"
                class="product-gallery__img"
            />
        </div>
    </div>
</template>

<style scoped>
.product-gallery {
    position: relative;
    width: 100%;
    height: 16rem;
    background: rgba(30, 41, 59, 0.5);
    overflow: hidden;
}

@media (min-width: 640px) {
    .product-gallery {
        height: 20rem;
    }
}

.product-gallery__no-photo {
    display: flex;
    height: 100%;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    color: #64748b;
}

.product-gallery__wrap {
    width: 100%;
    height: 100%;
}

.product-gallery__swiper {
    width: 100%;
    height: 100%;
}

:deep(.swiper-wrapper) {
    height: 100%;
}

.product-gallery__slide {
    width: 100%;
    height: 100%;
}

.product-gallery__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
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
