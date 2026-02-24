import { onMounted } from "vue";
import { gsap } from "gsap";

export function useEnterSlide(targetRef, options = {}) {
    onMounted(() => {
        if (!targetRef.value) return;

        const {
            y = 40,
            opacity = 0,
            duration = 0.6,
            delay = 0,
            ease = "power3.out",
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

