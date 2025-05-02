<script>
import gsap from "gsap";

export default {
    name: "Banner",
    props: {
        banner: {
            type: Object,
            required: true,
        },
    },
    mounted() {
        gsap.fromTo(
            this.$refs.bannerImage,
            { opacity: 0, y: 20, scale: 1.05 },
            { opacity: 1, y: 0, scale: 1, duration: 1.5, ease: "expo.out" }
        );
        if (this.banner.title || this.banner.description) {
            gsap.from([this.$refs.bannerTitle, this.$refs.bannerDescription], {
                opacity: 0,
                x: -30,
                stagger: 0.4,
                duration: 1,
                ease: "power4.out",
                delay: 0.3,
            });
        }
        this.$refs.bannerImage.addEventListener("mouseenter", () => {
            gsap.to(this.$refs.bannerImage, {
                scale: 1.02,
                duration: 0.5,
                ease: "power2.out",
            });
        });
        this.$refs.bannerImage.addEventListener("mouseleave", () => {
            gsap.to(this.$refs.bannerImage, {
                scale: 1,
                duration: 0.5,
                ease: "power2.out",
            });
        });
    },
};
</script>

<template>
    <div class="relative overflow-hidden mt-4 rounded-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
            <div class="w-full">
                <div
                    ref="bannerImage"
                    class="relative bg-cover bg-center bg-no-repeat w-full rounded-xl overflow-hidden transition-transform duration-500 min-h-[300px]"
                    :style="{
                        backgroundImage: `url('/uploads/${banner.image}')`,
                    }"
                >
                    <div
                        v-if="banner.title || banner.description"
                        class="absolute inset-0 bg-gradient-to-br from-black/40 to-black/60 flex items-center rounded-xl backdrop-blur-xs"
                    >
                        <div
                            class="p-6 md:p-10 text-white backdrop-blur-md bg-white/10 rounded-xl transform transition-transform duration-300 hover:-translate-y-1 mx-4 md:mx-8"
                        >
                            <h1
                                ref="bannerTitle"
                                class="text-2xl sm:text-3xl md:text-4xl lg:text-6xl font-bold mb-4 uppercase tracking-wide text-white/95"
                            >
                                {{ banner.title }}
                            </h1>
                            <p
                                ref="bannerDescription"
                                class="text-base sm:text-lg md:text-xl font-medium text-white/90"
                            >
                                {{ banner.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
