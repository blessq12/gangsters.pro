<script setup>
import { useUserStore } from "../../stores/userStore";

const userStore = useUserStore();
</script>

<template>
    <div
        class="rounded-3xl border border-amber-400/30 bg-[rgba(0,0,0,0.88)] px-4 sm:px-6 lg:px-8 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur"
    >
        <div class="flex flex-col gap-3">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm sm:text-base font-semibold text-slate-50">
                    Корзина
                </p>
                <div
                    class="flex h-8 items-center rounded-full bg-black/70 px-3 text-xs text-slate-200"
                >
                    Пример корзины
                </div>
            </div>

            <ul class="space-y-2 text-xs sm:text-sm text-slate-200">
                <li
                    class="flex items-center justify-between gap-3 rounded-2xl bg-[rgba(255,255,255,0.03)] px-3 py-2"
                >
                    <span class="truncate">Филадельфия классика</span>
                    <span class="shrink-0 text-slate-300">1 × 520 ₽</span>
                </li>
                <li
                    class="flex items-center justify-between gap-3 rounded-2xl bg-[rgba(255,255,255,0.03)] px-3 py-2"
                >
                    <span class="truncate">Комбо «Гангстерский сет»</span>
                    <span class="shrink-0 text-slate-300">1 × 1 890 ₽</span>
                </li>
            </ul>

            <div class="mt-1 flex items-center justify-between text-xs sm:text-sm">
                <span class="text-slate-300/85">Итого</span>
                <span class="font-semibold text-amber-300">2 410 ₽</span>
            </div>
        </div>
    </div>
</template>

<style scoped></style>

