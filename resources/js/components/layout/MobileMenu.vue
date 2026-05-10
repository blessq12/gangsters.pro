<script setup>
import { computed, onMounted, onUnmounted, watch } from "vue";
import { useUiStore } from "../../stores/uiStore";
import { useSystemStore } from "../../stores/systemStore";
import { useAppDesign } from "../../design/useAppDesign";
import { NAV_LINKS_MOBILE_SHEET } from "../../design/layout/navigation.present";
import {
    safeTrim,
    formatTodayWorkScheduleLine,
    formatCompanyAddressLine,
} from "../../utils/system/companyDisplay";
import { formatRuPhone, phoneToTelHref } from "../../utils/phone/formatRuPhone";

const uiStore = useUiStore();
const systemStore = useSystemStore();
const mm = useAppDesign().components.navbar.mobileMenu;

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

function handleNavClick() {
    uiStore.setMobileMenuOpen(false);
}
</script>

<template>
    <Transition name="mobile-menu-fade">
        <div
            v-if="uiStore.isMobileMenuOpen"
            :class="mm.overlayRoot"
        >
            <div :class="mm.innerContainer">
                <nav :class="mm.sheetNav">
                    <div
                        v-if="
                            companyTitle ||
                            companyTagline ||
                            todayScheduleLine ||
                            addressLine ||
                            phoneHref
                        "
                        :class="mm.companySection"
                    >
                        <p
                            v-if="companyTitle"
                            :class="mm.companyTitle"
                        >
                            {{ companyTitle }}
                        </p>
                        <p
                            v-if="companyTagline"
                            :class="mm.companyTagline"
                        >
                            {{ companyTagline }}
                        </p>

                        <p
                            v-if="todayScheduleLine"
                            :class="mm.companySchedule"
                        >
                            {{ todayScheduleLine }}
                        </p>

                        <p
                            v-if="addressLine"
                            :class="mm.companyAddress"
                        >
                            {{ addressLine }}
                        </p>

                        <a
                            v-if="phoneHref && phoneDisplay"
                            :href="phoneHref"
                            :class="mm.phoneLink"
                            @click="handleNavClick"
                        >
                            {{ phoneDisplay }}
                        </a>
                    </div>

                    <div :class="mm.linksRegion">
                        <RouterLink
                            v-for="item in NAV_LINKS_MOBILE_SHEET"
                            :key="item.routeName"
                            :to="{ name: item.routeName }"
                            :class="mm.routerLinkItem"
                            @click="handleNavClick"
                        >
                            {{ item.label }}
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
