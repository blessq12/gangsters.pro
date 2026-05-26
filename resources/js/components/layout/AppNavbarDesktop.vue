<script setup>
import { ref } from "vue";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";
import { useAppDesign } from "../../design/useAppDesign";
import {
    NAV_LINKS_LEFT_PRIMARY,
    NAV_LINKS_RIGHT_PRIMARY,
} from "../../design/layout/navigation.present";
import { useOrderEntryPoints } from "../../composables/order/useOrderEntryPoints";
import { useCartStore } from "../../stores/cartStore";

const navbar = useAppDesign().components.navbar;
const cartStore = useCartStore();
const { openCart } = useOrderEntryPoints();

const containerRef = ref(null);

useEnterSlide(containerRef);
</script>

<template>
    <header :class="navbar.shared.header">
        <div :class="navbar.desktop.inner">
            <div
                ref="containerRef"
                :class="navbar.desktop.bar"
            >
                <div :class="navbar.desktop.leftZone">
                    <NavbarLinkGroup
                        :links="NAV_LINKS_LEFT_PRIMARY"
                        :nav-class="navbar.desktop.navLeft"
                    />
                </div>

                <NavbarBrand variant="desktop" />

                <div :class="navbar.desktop.rightZone">
                    <button
                        type="button"
                        :class="navbar.desktop.cartBtn"
                        aria-label="Корзина"
                        @click="openCart"
                    >
                        <i :class="navbar.desktop.cartBtnIcon" />
                        <span
                            v-if="cartStore.cartTotalItems > 0"
                            class="absolute -top-1.5 -right-1.5 flex min-h-[1.1rem] min-w-[1.1rem] items-center justify-center rounded-none bg-red-500 px-1 text-[10px] font-semibold text-white"
                        >
                            {{ cartStore.cartTotalItems }}
                        </span>
                    </button>
                    <NavbarLinkGroup
                        :links="NAV_LINKS_RIGHT_PRIMARY"
                        :nav-class="navbar.desktop.navRight"
                    />
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped></style>
