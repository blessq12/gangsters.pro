import { ref } from "vue";
import {
    playIntroScene,
    scheduleDockRevealAfterIntro,
} from "../../animations/animationManager";

/**
 * Старт SPA: интро → снятие overlay → пауза → bottomBarReady + onDockReveal.
 *
 * @param {{ themeStore?: { syncThemeColorFromCanvas: () => void }, onDockReveal?: () => void }} [options]
 */
export function useShellIntroDockTimeline({ themeStore, onDockReveal } = {}) {
    const introOverlayRef = ref(null);
    const introGlowRef = ref(null);
    const introLogoRef = ref(null);
    const mainRef = ref(null);
    const showIntro = ref(true);
    const bottomBarReady = ref(false);

    /** @type {ReturnType<typeof setTimeout> | null} */
    let dockRevealTimer = null;

    function onIntroSceneComplete() {
        showIntro.value = false;
        themeStore?.syncThemeColorFromCanvas?.();
        dockRevealTimer = scheduleDockRevealAfterIntro(() => {
            dockRevealTimer = null;
            bottomBarReady.value = true;
            onDockReveal?.();
        });
    }

    function startIntroScene() {
        playIntroScene({
            introOverlay: introOverlayRef.value,
            introGlow: introGlowRef.value,
            introLogo: introLogoRef.value,
            main: mainRef.value,
            onComplete: onIntroSceneComplete,
        });
    }

    function dispose() {
        if (dockRevealTimer != null) {
            clearTimeout(dockRevealTimer);
            dockRevealTimer = null;
        }
    }

    return {
        introOverlayRef,
        introGlowRef,
        introLogoRef,
        mainRef,
        showIntro,
        bottomBarReady,
        startIntroScene,
        dispose,
    };
}
