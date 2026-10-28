<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import { playMobileNavbarLogoPulse } from "../../animations/animationManager";
import { useEnterSlide } from "../../animations/animationManager";
import { useAppDesign } from "../../design/useAppDesign";
import { useUiStore } from "../../modules/shell/store/uiStore";
const uiStore = useUiStore();
const navbar = useAppDesign().components.navbar;

const containerRef = ref(null);
const logoPulseRef = ref(null);

/** @type {{ kill: () => void } | null} */
let logoPulseControl = null;

useEnterSlide(containerRef);

onMounted(() => {
    void nextTick().then(() => {
        if (logoPulseRef.value) {
            logoPulseControl?.kill();
            logoPulseControl = playMobileNavbarLogoPulse(logoPulseRef.value);
        }
    });
});

onUnmounted(() => {
    logoPulseControl?.kill();
    logoPulseControl = null;
});

function toggleMobileMenu() {
    uiStore.toggleMobileMenu();
}
</script>

<template>
    <header :class="navbar.shared.header">
        <div :class="navbar.mobile.inner">
            <div
                ref="containerRef"
                :class="navbar.mobile.bar"
            >
                <div :class="navbar.mobile.scheduleZone">
                    <WorkScheduleStrip layout="navbarCompact" />
                </div>

                <NavbarBrand variant="mobile">
                    <span
                        ref="logoPulseRef"
                        :class="navbar.mobile.logoPulseWrap"
                    >
                        <img
                            src="/images/load_logo.svg"
                            alt="Gangsters"
                            :class="navbar.mobile.logoImg"
                        />
                    </span>
                </NavbarBrand>

                <div :class="navbar.mobile.burgerZone">
                    <NavbarBurgerButton
                        variant="mobile"
                        :open="uiStore.isMobileMenuOpen"
                        @click="toggleMobileMenu"
                    />
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped></style>
