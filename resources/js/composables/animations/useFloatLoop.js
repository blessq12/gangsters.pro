import { onMounted, onBeforeUnmount } from "vue";
import { playFloatLoop } from "../../animations/animationManager";

export function useFloatLoop(targetsRef, options = {}) {
    let controller = null;

    onMounted(() => {
        controller = playFloatLoop({
            elements: targetsRef?.value,
            options,
        });
    });

    onBeforeUnmount(() => {
        if (controller && typeof controller.kill === "function") {
            controller.kill();
            controller = null;
        }
    });
}

