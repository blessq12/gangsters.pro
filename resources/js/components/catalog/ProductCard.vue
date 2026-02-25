<script setup>
import { computed } from "vue";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const primaryThumb = computed(() => {
    const p = props.product || {};

    // thumbs: [{ small, medium, large }]
    if (Array.isArray(p.thumbs) && p.thumbs.length) {
        const t = p.thumbs[0];
        if (t && typeof t === "object") {
            return t.medium || t.large || t.small || null;
        }
    }

    // images: ["/uploads/..."]
    if (Array.isArray(p.images) && p.images.length) {
        return p.images[0];
    }

    return null;
});

const backgroundStyle = computed(() => {
    if (!primaryThumb.value) {
        return {};
    }

    return {
        backgroundImage: `url(${primaryThumb.value})`,
    };
});
</script>

<template>
    <article
        class="group flex h-full flex-col overflow-hidden rounded-3xl bg-[rgba(255,255,255,0.02)] shadow-[0_18px_45px_rgba(0,0,0,0.85)] transition-colors transition-transform duration-300 hover:-translate-y-1 hover:bg-[rgba(255,255,255,0.03)]"
    >
        <div
            class="relative w-full overflow-hidden aspect-[4/3] sm:aspect-[5/4] lg:h-full lg:aspect-auto"
        >
            <div
                class="absolute inset-0 bg-cover bg-center transition-transform duration-500 ease-out group-hover:scale-105"
                :style="backgroundStyle"
                :class="!primaryThumb ? 'bg-slate-900/70' : ''"
            ></div>

            <div
                v-if="!primaryThumb"
                class="absolute inset-0 flex items-center justify-center text-xs text-slate-400"
            >
                Нет фото
            </div>

            <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/80 via-black/45 to-black/10"
            ></div>

            <div
                v-if="product.weight"
                class="absolute left-3 top-3 inline-flex items-center rounded-full border border-white/10 bg-[rgba(0,0,0,0.75)] px-2.5 py-1 text-[11px] font-medium text-slate-100 backdrop-blur"
            >
                {{ product.weight }} г
            </div>

            <div
                v-if="product.price"
                class="absolute right-3 top-3 inline-flex items-center rounded-full bg-amber-400 px-3 py-1.5 text-xs font-semibold text-black shadow-[0_0_20px_rgba(251,191,36,0.7)]"
            >
                {{ product.price }} ₽
            </div>

            <div
                class="absolute inset-x-3 bottom-3 rounded-2xl border border-amber-400/30 bg-[rgba(255,255,255,0.04)] px-3.5 py-2.5 backdrop-blur shadow-[0_0_20px_rgba(0,0,0,0.9)]"
            >
                <h3
                    class="text-sm sm:text-base font-semibold text-slate-50 line-clamp-2 sm:line-clamp-3"
                >
                    {{ product.name }}
                </h3>
                <p
                    v-if="product.consist"
                    class="mt-1 text-[11px] sm:text-xs text-slate-300/85 leading-snug line-clamp-3"
                >
                    {{ product.consist }}
                </p>
            </div>
        </div>
    </article>
</template>

<style scoped></style>

