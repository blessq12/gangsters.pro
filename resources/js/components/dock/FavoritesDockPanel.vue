<script setup>
import { useUserStore } from "../../stores/userStore";

const userStore = useUserStore();
</script>

<template>
    <div
        class="rounded-3xl border border-amber-400/30 bg-[rgba(0,0,0,0.88)] px-4 sm:px-6 lg:px-8 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur"
    >
        <div class="flex flex-col gap-3">
            <p class="text-sm sm:text-base font-semibold text-slate-50">
                Избранное
            </p>

            <ul class="space-y-2 text-xs sm:text-sm text-slate-200">
                <li
                    class="flex items-center justify-between gap-3 rounded-2xl bg-[rgba(255,255,255,0.03)] px-3 py-2"
                >
                    <span class="truncate">Филадельфия классика</span>
                    <button
                        type="button"
                        class="shrink-0 text-[11px] text-amber-300 hover:text-amber-200"
                    >
                        В корзину
                    </button>
                </li>
                <li
                    class="flex items-center justify-between gap-3 rounded-2xl bg-[rgba(255,255,255,0.03)] px-3 py-2"
                >
                    <span class="truncate">Сет «Ночной дозор»</span>
                    <button
                        type="button"
                        class="shrink-0 text-[11px] text-amber-300 hover:text-amber-200"
                    >
                        В корзину
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped></style>

