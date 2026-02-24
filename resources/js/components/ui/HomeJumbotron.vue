<script setup>
import "swiper/css";
import { Swiper, SwiperSlide } from "swiper/vue";

const slides = [
    {
        title: "Gangsters Sushi",
        description: "Гангстерски щедрые роллы и суши.",
        image: "/images/banners/banner1.jpeg",
    },
    {
        title: "Мексиканская кухня",
        description: "Тако, буррито и другие горячие комбо.",
        image: "/images/banners/banner2.jpeg",
    },
    {
        title: "Ночные заказы",
        description: "Когда город спит — кухня Gangsters не отдыхает.",
        image: "/images/banners/banner3.jpeg",
    },
];

const handleSwiperInit = (swiper) => {
    if (!swiper) return;
    // переключаемся на слайд назад, чтобы сразу видеть соседние
    swiper.slidePrev(0);
};
</script>

<template>
    <section class="mt-12 mb-18 relative">
        <div
            class="pointer-events-none absolute inset-0 opacity-40 mix-blend-screen"
        >
            <div
                class="absolute -top-24 -left-10 h-56 w-56 rounded-full bg-amber-500/15 blur-3xl"
            ></div>
            <div
                class="absolute -bottom-24 right-0 h-64 w-64 rounded-full bg-rose-500/10 blur-3xl"
            ></div>
        </div>

        <div class="relative z-10">
            <Swiper
                :loop="true"
                :looped-slides="slides.length"
                :loop-additional-slides="slides.length"
                :autoplay="{ delay: 4500 }"
                :speed="700"
                :space-between="24"
                :centered-slides="true"
                :breakpoints="{
                    0: { slidesPerView: 1.1 },
                    768: { slidesPerView: 1.3 },
                    1024: { slidesPerView: 1.6 },
                }"
                class="!overflow-visible"
                @swiper="handleSwiperInit"
            >
                <SwiperSlide
                    v-for="(slide, index) in slides"
                    :key="index"
                    v-slot="{ isActive }"
                >
                    <div
                        class="relative rounded-2xl overflow-hidden bg-slate-900/60 border border-white/10 transition-transform transition-opacity duration-500 ease-out"
                        :class="
                            isActive
                                ? 'scale-[1.15] opacity-100'
                                : 'scale-90 opacity-60'
                        "
                    >
                        <div class="aspect-[16/9] w-full">
                            <img
                                :src="slide.image"
                                :alt="slide.title"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div
                            class="absolute inset-x-0 bottom-0 px-4 sm:px-6 py-4 bg-gradient-to-t from-black/70 via-black/20 to-transparent"
                        >
                            <p
                                class="text-xs sm:text-sm uppercase tracking-[0.3em] text-slate-300/80 mb-1"
                            >
                                Gangsters
                            </p>
                            <h1
                                class="text-lg sm:text-2xl font-semibold text-amber-300"
                            >
                                {{ slide.title }}
                            </h1>
                            <p
                                class="mt-1 text-xs sm:text-sm text-slate-200/90"
                            >
                                {{ slide.description }}
                            </p>
                        </div>
                    </div>
                </SwiperSlide>
            </Swiper>
        </div>
    </section>
</template>

<style scoped></style>
