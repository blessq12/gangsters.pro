<script setup>
import { computed, onMounted, onUnmounted, watch } from "vue";
import { useUiStore } from "../../stores/uiStore";
import { useSystemStore } from "../../stores/systemStore";
import {
    safeTrim,
    formatTodayWorkScheduleLine,
    formatCompanyAddressLine,
} from "../../utils/system/companyDisplay";
import { formatRuPhone, phoneToTelHref } from "../../utils/phone/formatRuPhone";

const uiStore = useUiStore();
const systemStore = useSystemStore();

const companyTitle = computed(() => {
    const c = systemStore.company;
    if (!c) return "";
    return safeTrim(c.brand_name) || safeTrim(c.name) || "";
});

const companyTagline = computed(() => safeTrim(systemStore.company?.tagline));

const todayScheduleLine = computed(() =>
    formatTodayWorkScheduleLine(systemStore.company, new Date()),
);

const addressLine = computed(() =>
    formatCompanyAddressLine(systemStore.company),
);

const phoneDisplay = computed(() =>
    formatRuPhone(systemStore.company?.phone),
);

const phoneHref = computed(() => phoneToTelHref(systemStore.company?.phone));

function closeMenuOnScroll() {
    if (uiStore.isMobileMenuOpen) {
        uiStore.setMobileMenuOpen(false);
    }
}

watch(
    () => uiStore.isMobileMenuOpen,
    (open) => {
        if (typeof window === "undefined") return;
        if (open) {
            window.addEventListener("scroll", closeMenuOnScroll, {
                passive: true,
                capture: true,
            });
        } else {
            window.removeEventListener("scroll", closeMenuOnScroll, {
                capture: true,
            });
        }
    },
);

onMounted(() => {
    if (!systemStore.company && !systemStore.loadingCompany) {
        void systemStore.fetchCompany();
    }
});

onUnmounted(() => {
    if (typeof window === "undefined") return;
    window.removeEventListener("scroll", closeMenuOnScroll, { capture: true });
});
</script>

<template>
    <Transition name="mobile-menu-fade">
        <div
            v-if="uiStore.isMobileMenuOpen"
            class="pointer-events-none fixed inset-x-0 top-0 z-30 md:hidden pt-24"
        >
            <div
                class="pointer-events-auto mx-auto mt-3 max-w-7xl px-4 sm:px-6 lg:px-8"
            >
                <nav
                    class="overflow-hidden rounded-2xl border border-white/10 bg-[#1f1f23] text-sm font-medium text-slate-50 shadow-[0_20px_50px_rgba(0,0,0,0.75)]"
                >
                    <div
                        v-if="
                            companyTitle ||
                            companyTagline ||
                            todayScheduleLine ||
                            addressLine ||
                            phoneHref
                        "
                        class="border-b border-white/10 px-4 py-3"
                    >
                        <p
                            v-if="companyTitle"
                            class="text-[13px] font-semibold leading-snug text-slate-50"
                        >
                            {{ companyTitle }}
                        </p>
                        <p
                            v-if="companyTagline"
                            class="mt-0.5 line-clamp-2 text-xs leading-snug text-slate-400"
                        >
                            {{ companyTagline }}
                        </p>

                        <p
                            v-if="todayScheduleLine"
                            class="mt-2.5 text-[11px] leading-snug text-slate-400"
                        >
                            {{ todayScheduleLine }}
                        </p>

                        <p
                            v-if="addressLine"
                            class="mt-1.5 text-[11px] leading-snug text-slate-500"
                        >
                            {{ addressLine }}
                        </p>

                        <a
                            v-if="phoneHref && phoneDisplay"
                            :href="phoneHref"
                            class="mt-2.5 inline-flex text-xs font-medium text-amber-400/95 hover:text-amber-300"
                            @click="uiStore.setMobileMenuOpen(false)"
                        >
                            {{ phoneDisplay }}
                        </a>
                    </div>

                    <div class="space-y-0.5 px-2 py-2">
                        <RouterLink
                            :to="{ name: 'home' }"
                            class="block rounded-xl px-3 py-2.5 text-slate-200 hover:bg-white/5"
                            @click="uiStore.setMobileMenuOpen(false)"
                        >
                            Главная
                        </RouterLink>
                        <RouterLink
                            :to="{ name: 'about' }"
                            class="block rounded-xl px-3 py-2.5 text-slate-200 hover:bg-white/5"
                            @click="uiStore.setMobileMenuOpen(false)"
                        >
                            О компании
                        </RouterLink>
                        <RouterLink
                            :to="{ name: 'delivery' }"
                            class="block rounded-xl px-3 py-2.5 text-slate-200 hover:bg-white/5"
                            @click="uiStore.setMobileMenuOpen(false)"
                        >
                            Оплата и доставка
                        </RouterLink>
                        <RouterLink
                            :to="{ name: 'contacts' }"
                            class="block rounded-xl px-3 py-2.5 text-slate-200 hover:bg-white/5"
                            @click="uiStore.setMobileMenuOpen(false)"
                        >
                            Контакты
                        </RouterLink>
                    </div>
                </nav>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.mobile-menu-fade-enter-active,
.mobile-menu-fade-leave-active {
    transition: opacity 0.2s ease;
}
.mobile-menu-fade-enter-from,
.mobile-menu-fade-leave-to {
    opacity: 0;
}
</style>
