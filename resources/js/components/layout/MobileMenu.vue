<script setup>
import { computed, onUnmounted, watch } from "vue";
import { useUiStore } from "../../modules/shell/store/uiStore";
import { useContentStore } from "../../modules/content/store";
import { useAppDesign } from "../../design/useAppDesign";
import { NAV_LINKS_MOBILE_SHEET } from "../../design/layout/navigation.present";
import {
    safeTrim,
    formatTodayWorkScheduleLine,
    formatCompanyAddressLine,
} from "../../modules/content/application/company";
import { formatRuPhone, phoneToTelHref } from "../../platform/ruPhone";

const uiStore = useUiStore();
const contentStore = useContentStore();
const mm = useAppDesign().components.navbar.mobileMenu;

const profile = computed(() => contentStore.profile);
const deliveryFacts = computed(() => contentStore.deliveryFacts);

const companyTitle = computed(() => {
    const c = profile.value;
    if (!c) return "";
    return safeTrim(c.brand_name) || safeTrim(c.name) || "";
});

const companyTagline = computed(() => safeTrim(profile.value?.tagline));

const todayScheduleLine = computed(() =>
    formatTodayWorkScheduleLine(profile.value, new Date()),
);

const addressLine = computed(() =>
    formatCompanyAddressLine(deliveryFacts.value),
);

const phoneDisplay = computed(() =>
    formatRuPhone(profile.value?.phone),
);

const phoneHref = computed(() => phoneToTelHref(profile.value?.phone));

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
