<script setup>
import { onMounted, ref } from "vue";
import { playBannerSticks } from "../../animations/animationManager";
import { useAppDesign } from "../../design/useAppDesign";

const b = useAppDesign().components.home.secondary.banner;

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: "",
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
    image: {
        type: String,
        default: "",
    },
});

const leftStickRef = ref(null);
const rightStickRef = ref(null);

onMounted(() => {
    playBannerSticks({
        left: leftStickRef.value,
        right: rightStickRef.value,
    });
});
</script>

<template>
    <section :class="b.section">
        <!-- анимированные палочки в правом верхнем углу -->
        <div :class="b.sticksWrap">
            <img
                ref="leftStickRef"
                src="/images/stick.png"
                alt=""
                :class="b.stickLeft"
            />
            <img
                ref="rightStickRef"
                src="/images/stick.png"
                alt=""
                :class="b.stickRight"
            />
        </div>

        <div :class="b.card">
            <div :class="b.glowLayer">
                <div :class="b.glowAmber"></div>
                <div :class="b.glowRose"></div>
            </div>

            <div :class="b.mainCol">
                <div
                    v-if="breadcrumbs.length"
                    :class="b.breadcrumbsWrap"
                >
                    <nav :class="b.breadcrumbsNav">
                        <span
                            v-for="(crumb, index) in breadcrumbs"
                            :key="index"
                            :class="b.crumbRow"
                        >
                            <span :class="b.crumbText">{{ crumb }}</span>
                            <span
                                v-if="index < breadcrumbs.length - 1"
                                :class="b.crumbSep"
                                >/</span
                            >
                        </span>
                    </nav>
                </div>

                <h1 :class="b.title">
                    {{ props.title }}
                </h1>

                <p
                    v-if="props.description"
                    :class="b.description"
                >
                    {{ props.description }}
                </p>
            </div>

            <div
                v-if="props.image"
                :class="b.imageWrap"
            >
                <img
                    :src="props.image"
                    :alt="props.title"
                    :class="b.image"
                />
            </div>
        </div>
    </section>
</template>

<style scoped></style>
