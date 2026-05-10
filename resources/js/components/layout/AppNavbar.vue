<script setup>
import { ref } from "vue";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";
import { useAppDesign } from "../../design/useAppDesign";
import {
    NAV_LINKS_LEFT_PRIMARY,
    NAV_LINKS_RIGHT_PRIMARY,
} from "../../design/layout/navigation.present";
import { useUiStore } from "../../stores/uiStore";

const uiStore = useUiStore();
const navbar = useAppDesign().components.navbar;

const containerRef = ref(null);

useEnterSlide(containerRef);

function toggleMobileMenu() {
    uiStore.toggleMobileMenu();
}
</script>

<template>
    <header :class="navbar.shared.header">
        <div :class="navbar.responsive.inner">
            <div
                ref="containerRef"
                :class="navbar.responsive.bar"
            >
                <div :class="navbar.responsive.leftZone">
                    <div
                        :class="navbar.responsive.balanceSpacer"
                        aria-hidden="true"
                    />

                    <div :class="navbar.responsive.desktopNavGate">
                        <NavbarLinkGroup
                            :links="NAV_LINKS_LEFT_PRIMARY"
                            :nav-class="navbar.responsive.navLeft"
                        />
                    </div>
                </div>

                <NavbarBrand variant="responsive" />

                <div :class="navbar.responsive.rightZone">
                    <NavbarBurgerButton
                        variant="responsive"
                        :open="uiStore.isMobileMenuOpen"
                        @click="toggleMobileMenu"
                    />

                    <NavbarLinkGroup
                        :links="NAV_LINKS_RIGHT_PRIMARY"
                        :nav-class="navbar.responsive.navRight"
                    />
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped></style>
