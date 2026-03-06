<script setup>
defineProps({
    title: {
        type: String,
        default: "",
    },
    subtitle: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <section class="relative overflow-hidden rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.03)] px-4 py-6 shadow-[0_16px_50px_rgba(0,0,0,0.35)] sm:px-6 sm:py-8 lg:px-8">
        <div
            class="pointer-events-none absolute inset-0 opacity-40 mix-blend-screen"
        >
            <div
                class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-amber-500/10 blur-3xl"
            ></div>
            <div
                class="absolute bottom-0 left-0 h-24 w-24 rounded-full bg-rose-500/10 blur-3xl"
            ></div>
        </div>

        <div class="relative">
            <div
                v-if="title || subtitle"
                class="mb-5 flex flex-col gap-3 border-b border-white/10 pb-4 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p class="mb-2 h-px w-14 bg-gradient-to-r from-amber-400 to-transparent"></p>
                    <h2
                        v-if="title"
                        class="text-lg font-semibold text-amber-300 sm:text-xl"
                    >
                        {{ title }}
                    </h2>
                </div>
                <p
                    v-if="subtitle"
                    class="max-w-xl text-xs uppercase tracking-[0.22em] text-slate-400"
                >
                    {{ subtitle }}
                </p>
            </div>
            <div class="text-sm text-slate-200/90 leading-relaxed space-y-3">
                <slot />
            </div>
        </div>
    </section>
</template>
