<script setup>
import { computed, onMounted, ref } from "vue";
import { RouterLink } from "vue-router";
import { playBannerSticks } from "../animations/animationManager";
import { useAppDesign } from "../design/useAppDesign";

const sp = useAppDesign().components.layoutShell.secondaryPage;

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
    /** Фоновое изображение hero (как в Jumbotron) */
    heroImage: {
        type: String,
        default: "",
    },
    eyebrow: {
        type: String,
        default: "",
    },
    stats: {
        type: Array,
        default: () => [],
    },
});

const leftStickRef = ref(null);
const rightStickRef = ref(null);
const heroStats = computed(() => props.stats.slice(0, 3));

onMounted(() => {
    playBannerSticks({
        left: leftStickRef.value,
        right: rightStickRef.value,
    });
});
</script>

<template>
    <section :class="sp.section">
        <div :class="sp.outerGlowWrap">
            <div :class="sp.outerGlowTL"></div>
            <div :class="sp.outerGlowBR"></div>
        </div>

        <div :class="sp.heroCard">
            <div
                v-if="heroImage"
                :class="sp.heroImageLayer"
            >
                <img
                    :src="heroImage"
                    :alt="title"
                    :class="sp.heroImage"
                />
                <div :class="sp.heroScrim"></div>
            </div>

            <div :class="sp.innerAmbient">
                <div :class="sp.innerGlowA"></div>
                <div :class="sp.innerGlowB"></div>
            </div>

            <div :class="sp.contentPad">
                <div :class="sp.sticksWrap">
                    <img
                        ref="leftStickRef"
                        src="/images/stick.png"
                        alt=""
                        :class="sp.stickLeft"
                    />
                    <img
                        ref="rightStickRef"
                        src="/images/stick.png"
                        alt=""
                        :class="sp.stickRight"
                    />
                </div>

                <div :class="sp.textCol">
                    <nav
                        v-if="breadcrumbs.length"
                        :class="sp.breadcrumbsNav"
                    >
                        <template
                            v-for="(crumb, index) in breadcrumbs"
                            :key="index"
                        >
                            <RouterLink
                                v-if="index === 0"
                                :to="{ name: 'home' }"
                                :class="sp.breadcrumbHomeLink"
                            >
                                {{ crumb }}
                            </RouterLink>
                            <span
                                v-else
                                :class="sp.breadcrumbText"
                            >{{ crumb }}</span>
                            <span
                                v-if="index < breadcrumbs.length - 1"
                                :class="sp.breadcrumbSep"
                            >
                                /
                            </span>
                        </template>
                    </nav>

                    <div
                        v-if="eyebrow"
                        :class="sp.eyebrow"
                    >
                        {{ eyebrow }}
                    </div>

                    <h1 :class="sp.title">
                        {{ title }}
                    </h1>

                    <p
                        v-if="description"
                        :class="sp.description"
                    >
                        {{ description }}
                    </p>

                    <div
                        v-if="heroStats.length"
                        :class="sp.statsGrid"
                    >
                        <div
                            v-for="(stat, index) in heroStats"
                            :key="index"
                            :class="sp.statCard"
                        >
                            <p :class="sp.statLabel">
                                {{ stat.label }}
                            </p>
                            <p :class="sp.statValue">
                                {{ stat.value }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div :class="sp.slotWrap">
        <slot />
    </div>
</template>
