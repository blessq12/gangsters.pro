<template>
    <div class="banner position-relative overflow-hidden">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="position-relative">
                        <img
                            ref="bannerImage"
                            :src="'/uploads/' + banner.image"
                            :alt="banner.title ?? 'Баннер'"
                            class="img-fluid w-100 rounded-3 object-cover"
                        />
                        <div
                            class="overlay rounded-3 d-flex align-items-center"
                            v-if="banner.title || banner.description"
                        >
                            <div class="text-content p-4 p-md-5 text-white">
                                <h1
                                    ref="bannerTitle"
                                    class="display-4 fw-bold mb-3 text-uppercase"
                                >
                                    {{ banner.title }}
                                </h1>
                                <p ref="bannerDescription" class="lead mb-0">
                                    {{ banner.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

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
        gsap.from([this.$refs.bannerTitle, this.$refs.bannerDescription], {
            opacity: 0,
            x: -30,
            stagger: 0.4,
            duration: 1,
            ease: "power4.out",
            delay: 0.3,
        });
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

<style scoped>
.banner {
    margin-top: 1rem;
    border-radius: 0.75rem;
    overflow: hidden;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        145deg,
        rgba(0, 0, 0, 0.2) 0%,
        rgba(0, 0, 0, 0.4) 100%
    );
    transition: background 0.4s ease;
}

.text-content {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 0.75rem;
    backdrop-filter: blur(8px);
    transition: transform 0.3s ease;
}

.text-content:hover {
    transform: translateY(-5px);
}

.banner img {
    transition: transform 0.6s ease;
}

@media (max-width: 768px) {
    .text-content {
        padding: 1.5rem;
    }
    h1 {
        font-size: 1.75rem;
    }
    p {
        font-size: 0.95rem;
    }
}
</style>
