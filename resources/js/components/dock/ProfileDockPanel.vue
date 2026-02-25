<script setup>
import { useUserStore } from "../../stores/userStore";

const userStore = useUserStore();
</script>

<template>
    <div
        class="rounded-3xl border border-amber-400/30 bg-[rgba(0,0,0,0.88)] px-4 sm:px-6 lg:px-8 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur"
    >
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <div
                class="flex h-14 w-14 items-center justify-center rounded-full border border-amber-400/40 bg-black/70 text-lg font-semibold text-amber-200 shadow-[0_0_20px_rgba(251,191,36,0.6)]"
            >
                G
            </div>
            <div class="min-w-0 flex-1 space-y-1">
                <p class="text-sm sm:text-base font-semibold text-slate-50">
                    {{ userStore.profile.name || "Гость Gangsters" }}
                </p>
                <p class="text-xs sm:text-sm text-slate-300/85">
                    {{ userStore.profile.phone || "+7 (___) ___‑__‑__" }}
                </p>
                <p class="text-xs text-slate-400">
                    {{ userStore.profile.email || "email не указан" }}
                </p>
            </div>
            <div
                class="mt-3 sm:mt-0 flex flex-col items-end gap-1 text-right text-xs sm:text-sm"
            >
                <span class="text-slate-300/80">Бонусы</span>
                <span class="text-lg font-semibold text-amber-300">
                    {{ userStore.bonuses || 120 }}
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped></style>

