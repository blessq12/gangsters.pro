<script setup>
import { computed, onMounted, ref } from "vue";
import { RouterLink } from "vue-router";
import { playBannerSticks } from "../animations/animationManager";

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
    <section class="mt-12 mb-12 relative">
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

        <div
            class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900/60 shadow-[0_20px_80px_rgba(0,0,0,0.55)]"
        >
            <div
                v-if="heroImage"
                class="absolute inset-0"
            >
                <img
                    :src="heroImage"
                    :alt="title"
                    class="h-full w-full object-cover opacity-55"
                />
                <div
                    class="absolute inset-0 bg-[linear-gradient(135deg,rgba(0,0,0,0.92)_0%,rgba(0,0,0,0.66)_45%,rgba(0,0,0,0.4)_100%)]"
                ></div>
            </div>

            <div class="absolute inset-0">
                <div
                    class="absolute -left-20 top-10 h-52 w-52 rounded-full bg-amber-500/10 blur-3xl"
                ></div>
                <div
                    class="absolute right-0 top-0 h-60 w-60 rounded-full bg-rose-500/10 blur-3xl"
                ></div>
            </div>

            <div class="relative px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">
                <div class="rotate-15">
                    <img
                        ref="leftStickRef"
                        src="/images/stick.png"
                        alt=""
                        class="pointer-events-none absolute -bottom-2 right-6 h-3 w-auto"
                    />
                    <img
                        ref="rightStickRef"
                        src="/images/stick.png"
                        alt=""
                        class="pointer-events-none absolute bottom-1 right-3 h-3 w-auto"
                    />
                </div>

                <div class="min-w-0">
                        <nav
                            v-if="breadcrumbs.length"
                            class="mb-4 flex flex-wrap items-center gap-1 text-xs text-slate-400"
                        >
                            <template
                                v-for="(crumb, index) in breadcrumbs"
                                :key="index"
                            >
                                <RouterLink
                                    v-if="index === 0"
                                    :to="{ name: 'home' }"
                                    class="hover:text-amber-300 transition-colors"
                                >
                                    {{ crumb }}
                                </RouterLink>
                                <span v-else class="text-slate-300">{{ crumb }}</span>
                                <span
                                    v-if="index < breadcrumbs.length - 1"
                                    class="opacity-60"
                                >
                                    /
                                </span>
                            </template>
                        </nav>

                        <div
                            v-if="eyebrow"
                            class="mb-3 inline-flex rounded-full border border-amber-400/30 bg-[rgba(255,255,255,0.05)] px-3 py-1 text-[11px] uppercase tracking-[0.28em] text-amber-200 backdrop-blur"
                        >
                            {{ eyebrow }}
                        </div>

                        <h1
                            class="mb-3 max-w-3xl text-2xl font-semibold leading-tight text-amber-300 sm:text-3xl lg:text-4xl"
                        >
                            {{ title }}
                        </h1>

                        <p
                            v-if="description"
                            class="max-w-2xl text-sm leading-relaxed text-slate-200/90 sm:text-base"
                        >
                            {{ description }}
                        </p>

                        <div
                            v-if="heroStats.length"
                            class="mt-6 grid gap-3 sm:grid-cols-3"
                        >
                            <div
                                v-for="(stat, index) in heroStats"
                                :key="index"
                                class="rounded-2xl border border-white/10 bg-[rgba(255,255,255,0.05)] px-4 py-3 backdrop-blur"
                            >
                                <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                                    {{ stat.label }}
                                </p>
                                <p class="mt-1 text-base font-semibold text-slate-50 sm:text-lg">
                                    {{ stat.value }}
                                </p>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </section>

    <div class="space-y-8 sm:space-y-10">
        <slot />
    </div>
</template>
