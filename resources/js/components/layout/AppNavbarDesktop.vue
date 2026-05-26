<script setup>
import { ref } from "vue";
import { useEnterSlide } from "../../composables/animations/useEnterSlide";
import { useAppDesign } from "../../design/useAppDesign";
import {
    NAV_LINKS_LEFT_PRIMARY,
    NAV_LINKS_RIGHT_PRIMARY,
} from "../../design/layout/navigation.present";
const navbar = useAppDesign().components.navbar;

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
