import { nextTick, ref } from "vue";
import {
    playIntroScene,
    revealShellMainContent,
    scheduleDockRevealAfterIntro,
} from "../../animations/animationManager";
import { useShellStore } from "../../stores/shellStore";

/**
 * Intro + reveal dock: локальные refs (как до decoupling), shellStore — фазы и очередь dock.
 *
 * @param {{ themeStore?: { syncThemeColorFromCanvas: () => void }, onDockReveal?: () => void }} [options]
 */
export function useShellIntroDockTimeline({ themeStore, onDockReveal } = {}) {
    const shellStore = useShellStore();

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
        shellStore.completeIntro();

        dockRevealTimer = scheduleDockRevealAfterIntro(() => {
            dockRevealTimer = null;
            bottomBarReady.value = true;
            shellStore.revealDock();
            onDockReveal?.();
        });
    }

    function tryPlayIntroScene() {
        const introOverlay = introOverlayRef.value;
        const introLogo = introLogoRef.value;
        const main = mainRef.value;

        if (!main) {
            return false;
        }

        if (!introOverlay || !introLogo) {
            return false;
        }

        playIntroScene({
            introOverlay,
            introGlow: introGlowRef.value,
            introLogo,
            main,
            onComplete: onIntroSceneComplete,
        });

        return true;
    }

    async function presentShellContent() {
        shellStore.beginIntro();

        await nextTick();

        if (tryPlayIntroScene()) {
            return;
        }

        await nextTick();

        if (tryPlayIntroScene()) {
            return;
        }

        revealShellMainContent(mainRef.value);
        showIntro.value = false;
        shellStore.completeIntro();
        bottomBarReady.value = true;
        shellStore.revealDock();
        onDockReveal?.();
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
        presentShellContent,
        dispose,
    };
}
