import { onMounted } from "vue";
import { gsap } from "gsap";
import {
    NAVBAR_ENTER_DELAY,
    NAVBAR_ENTER_DURATION,
    NAVBAR_ENTER_EASE,
    NAVBAR_ENTER_Y,
} from "../../animations/animationManager";

export function useEnterSlide(targetRef, options = {}) {
    onMounted(() => {
        if (!targetRef.value) return;

        const {
            y = NAVBAR_ENTER_Y,
            opacity = 0,
            duration = NAVBAR_ENTER_DURATION,
            delay = NAVBAR_ENTER_DELAY,
            ease = NAVBAR_ENTER_EASE,
        } = options;

        gsap.from(targetRef.value, {
            y,
            opacity,
            duration,
            delay,
            ease,
        });
    });
}

