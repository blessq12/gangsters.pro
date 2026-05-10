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

const slideUrls = computed(() => {
    const list = props.images;
    if (!Array.isArray(list)) return [];
    return list
        .map((item) => (typeof item === "string" ? item : item?.url))
        .filter(Boolean);
});
</script>

<template>
    <div :class="dg.root">
        <div v-if="!slideUrls.length" :class="dg.noPhoto">
            {{ dg.noPhotoLabel }}
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
                :class="dg.swiper"
            >
                <SwiperSlide
                    v-for="(url, index) in slideUrls"
                    :key="index"
                    :class="dg.slide"
                >
                    <img
                        :src="url"
                        :alt="alt ? `${alt} — фото ${index + 1}` : ''"
                        :class="dg.img"
                    />
                </SwiperSlide>
            </Swiper>
        </template>
        <div v-else :class="dg.wrap">
            <img
                :src="slideUrls[0]"
                :alt="alt || 'Фото товара'"
                :class="dg.img"
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
