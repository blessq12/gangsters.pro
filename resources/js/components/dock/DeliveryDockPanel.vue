<script setup>
import { useUserStore } from "../../stores/userStore";

const userStore = useUserStore();
</script>

<template>
    <div
        class="rounded-3xl border border-amber-400/30 bg-[rgba(0,0,0,0.88)] px-4 sm:px-6 lg:px-8 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur"
    >
        <div class="flex flex-col gap-3 text-xs sm:text-sm text-slate-200">
            <p class="text-sm sm:text-base font-semibold text-slate-50">
                Оплата и доставка
            </p>

            <div class="rounded-2xl bg-[rgba(255,255,255,0.03)] px-3 py-2">
                <p class="text-slate-300/90">
                    Адрес доставки:
                    <span class="font-semibold">
                        {{
                            userStore.selectedAddress
                                ? userStore.selectedAddress.label
                                : "г. Пример, ул. Ролльная, д. 7"
                        }}
                    </span>
                </p>
                <p class="mt-1 text-slate-400">
                    Доставим за ~45–60 минут, без учёта часа пик.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="text-slate-300/80">Способ оплаты:</span>
                <span
                    class="rounded-full bg-black/70 px-3 py-1 text-[11px] uppercase tracking-wide text-slate-100"
                >
                    Картой курьеру
                </span>
                <span
                    class="rounded-full bg-black/70 px-3 py-1 text-[11px] uppercase tracking-wide text-slate-100"
                >
                    Онлайн
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped></style>

