<script setup>
import { onMounted, ref } from "vue";
import { playBannerSticks } from "../../animations/animationManager";

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
    <section class="my-6 md:my-8 lg:my-12 relative">
        <!-- анимированные палочки в правом верхнем углу -->
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

        <div
            class="rounded-2xl border border-white/10 bg-[rgba(255,255,255,0.03)] px-4 sm:px-6 lg:px-8 py-8 sm:py-10 flex flex-col sm:flex-row gap-6 items-start overflow-hidden relative"
        >
            <div
                class="pointer-events-none absolute inset-0 opacity-40 mix-blend-screen"
            >
                <div
                    class="absolute -top-16 -left-10 h-40 w-40 rounded-full bg-amber-500/20 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-20 right-0 h-48 w-48 rounded-full bg-rose-500/10 blur-3xl"
                ></div>
            </div>

            <div class="flex-1 min-w-0">
                <div
                    v-if="breadcrumbs.length"
                    class="mb-2 text-xs text-slate-400"
                >
                    <nav class="flex flex-wrap gap-1 items-center">
                        <span
                            v-for="(crumb, index) in breadcrumbs"
                            :key="index"
                            class="flex items-center gap-1"
                        >
                            <span class="truncate">{{ crumb }}</span>
                            <span
                                v-if="index < breadcrumbs.length - 1"
                                class="opacity-60"
                                >/</span
                            >
                        </span>
                    </nav>
                </div>

                <h1
                    class="text-xl sm:text-2xl font-semibold text-amber-300 mb-2"
                >
                    {{ props.title }}
                </h1>

                <p v-if="props.description" class="text-sm text-slate-200/90">
                    {{ props.description }}
                </p>
            </div>

            <div
                v-if="props.image"
                class="shrink-0 w-28 h-28 sm:w-32 sm:h-32 rounded-xl overflow-hidden border border-white/10 bg-slate-900/40"
            >
                <img
                    :src="props.image"
                    :alt="props.title"
                    class="w-full h-full object-cover"
                />
            </div>
        </div>
    </section>
</template>

<style scoped></style>
