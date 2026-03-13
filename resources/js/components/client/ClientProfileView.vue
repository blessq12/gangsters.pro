<script setup>
import { computed } from "vue";
import { useUserStore } from "../../stores/userStore";
import ClientAddressesManager from "./ClientAddressesManager.vue";

const userStore = useUserStore();

const fullName = computed(() => userStore.profile.name || "Гость Gangsters");
const phone = computed(() => userStore.profile.phone || "+7 (___) ___‑__‑__");
const email = computed(() => userStore.profile.email || "email не указан");
</script>

<template>
    <div class="space-y-4 text-slate-50">
        <div class="flex items-center gap-3">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-full border border-amber-400/40 bg-black/70 text-base font-semibold text-amber-200 shadow-[0_0_20px_rgba(251,191,36,0.6)]"
            >
                {{ fullName[0] ?? "G" }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-50">
                    {{ fullName }}
                </p>
                <p class="text-xs text-slate-300/85">
                    {{ phone }}
                </p>
                <p class="text-xs text-slate-400">
                    {{ email }}
                </p>
            </div>
        </div>

        <div class="border-t border-white/5 pt-3">
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">
                Адреса доставки
            </p>

            <ClientAddressesManager />
        </div>
    </div>
</template>

