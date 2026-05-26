<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import { playMobileNavbarLogoPulse } from "../../animations/animationManager";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";
import { useAppDesign } from "../../design/useAppDesign";
import { useUiStore } from "../../stores/uiStore";
import { useOrderEntryPoints } from "../../composables/order/useOrderEntryPoints";
import { useCartStore } from "../../stores/cartStore";

const uiStore = useUiStore();
const cartStore = useCartStore();
const navbar = useAppDesign().components.navbar;
const { openCart } = useOrderEntryPoints();

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
                    <button
                        type="button"
                        :class="navbar.mobile.cartBtn"
                        aria-label="Корзина"
                        @click="openCart"
                    >
                        <i :class="navbar.mobile.cartBtnIcon" />
                        <span
                            v-if="cartStore.cartTotalItems > 0"
                            class="absolute -top-1.5 -right-1.5 flex min-h-[1.1rem] min-w-[1.1rem] items-center justify-center rounded-none bg-red-500 px-1 text-[10px] font-semibold text-white"
                        >
                            {{ cartStore.cartTotalItems }}
                        </span>
                    </button>
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
