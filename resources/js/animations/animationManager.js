import { gsap } from "gsap";

/** Тайминг входа основного блока навбара (см. useEnterSlide в AppNavbar*). */
export const NAVBAR_ENTER_DELAY = 0.8;
export const NAVBAR_ENTER_DURATION = 0.6;
export const NAVBAR_ENTER_Y = -40;
export const NAVBAR_ENTER_EASE = "power3.out";

/** Задержка до момента, когда анимация навбара завершилась (delay + duration). */
export function navbarEnterCompleteDelay() {
    return NAVBAR_ENTER_DELAY + NAVBAR_ENTER_DURATION;
}

/** Сцена интро: логотип + оверлей + опциональный радиальный глоу снизу. */
export const INTRO_LOGO_IN_DURATION = 0.7;
export const INTRO_LOGO_HOLD_DURATION = 0.6;
export const INTRO_LOGO_OUT_DURATION = 0.65;
export const INTRO_MAIN_FADE_DURATION = 0.55;
export const INTRO_OVERLAY_FADE_DURATION = 0.55;
/** Секунды: fade main начинает до конца предыдущего блока. */
export const INTRO_MAIN_FADE_OVERLAP = 0.38;
/** Секунды: fade оверлея — overlap к предыдущему tween. */
export const INTRO_OVERLAY_FADE_OVERLAP = 0.42;

/**
 * Пауза после onComplete fade оверлея интро (оверлей полностью скрыт) до reveal dock.
 * playIntroScene onComplete = конец tween opacity оверлея, не середина сцены.
 */
export const INTRO_DOCK_REVEAL_GAP_MS = 450;

/**
 * @param {() => void} onReveal
 * @returns {ReturnType<typeof setTimeout>|undefined}
 */
export function scheduleDockRevealAfterIntro(onReveal) {
    if (typeof onReveal !== "function") return undefined;
    return setTimeout(onReveal, INTRO_DOCK_REVEAL_GAP_MS);
}

/**
 * Появление полосы расписания под шапкой — после завершения анимации навбара.
 */
export function playWorkScheduleStripEnter(el) {
    if (!el) return;
    gsap.fromTo(
        el,
        { y: -24, opacity: 0 },
        {
            y: 0,
            opacity: 1,
            duration: NAVBAR_ENTER_DURATION,
            delay: navbarEnterCompleteDelay(),
            ease: NAVBAR_ENTER_EASE,
        },
    );
}

/**
 * Показать основной контент shell (router-view). Без вызова main остаётся opacity-0 из layout design.
 *
 * @param {HTMLElement | null | undefined} mainEl
 * @param {{ animate?: boolean }} [options]
 */
export function revealShellMainContent(mainEl, { animate = false } = {}) {
    if (!mainEl) {
        return;
    }

    if (animate) {
        gsap.to(mainEl, {
            opacity: 1,
            duration: INTRO_MAIN_FADE_DURATION,
            ease: "power2.out",
        });
        return;
    }

    gsap.set(mainEl, { opacity: 1 });
}

export function playIntroScene({
    introOverlay,
    introLogo,
    main,
    introGlow,
    onComplete,
}) {
    if (!main) {
        onComplete?.();
        return;
    }

    if (!introOverlay || !introLogo) {
        revealShellMainContent(main);
        onComplete?.();
        return;
    }

    const tl = gsap.timeline();

    if (introGlow) {
        tl.fromTo(
            introGlow,
            {
                opacity: 0,
                "--intro-radial-x": "65%",
                "--intro-radial-y": "47.5%",
            },
            {
                opacity: 1,
                "--intro-radial-x": "130%",
                "--intro-radial-y": "95%",
                duration: INTRO_LOGO_IN_DURATION,
                ease: "back.out(1.7)",
            },
            0,
        );
    }

    tl.fromTo(
        introLogo,
        { scale: 0.6, opacity: 0 },
        {
            scale: 1.2,
            opacity: 1,
            duration: INTRO_LOGO_IN_DURATION,
            ease: "back.out(1.7)",
        },
        0,
    )
        .to(introLogo, { duration: INTRO_LOGO_HOLD_DURATION })
        .to(introLogo, {
            scale: 0,
            duration: INTRO_LOGO_OUT_DURATION,
            ease: "power2.out",
        });

    if (introGlow) {
        tl.to(
            introGlow,
            {
                "--intro-radial-x": "65%",
                "--intro-radial-y": "47.5%",
                duration: INTRO_LOGO_OUT_DURATION,
                ease: "power2.out",
            },
            "<",
        );
    }

    tl.to(
        main,
        {
            opacity: 1,
            duration: INTRO_MAIN_FADE_DURATION,
            ease: "power2.out",
        },
        `-=${INTRO_MAIN_FADE_OVERLAP}`,
    ).to(
        introOverlay,
        {
            opacity: 0,
            duration: INTRO_OVERLAY_FADE_DURATION,
            ease: "power2.out",
            onComplete,
        },
        `-=${INTRO_OVERLAY_FADE_OVERLAP}`,
    );
}

export function playModalOpen({ backdrop, card }) {
    if (!backdrop || !card) return;

    const tl = gsap.timeline();

    tl.fromTo(
        backdrop,
        { opacity: 0 },
        { opacity: 1, duration: 0.2, ease: "power2.out" },
    ).fromTo(
        card,
        { y: 20, opacity: 0, scale: 0.96 },
        {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 0.35,
            ease: "power2.out",
        },
        "-=0.05",
    );
}

export function playModalClose({ backdrop, card, onComplete }) {
    if (!backdrop || !card) {
        if (onComplete) onComplete();
        return;
    }

    const tl = gsap.timeline({
        onComplete,
    });

    tl.to(card, {
        y: 10,
        opacity: 0,
        scale: 0.96,
        duration: 0.25,
        ease: "power2.in",
    }).to(
        backdrop,
        {
            opacity: 0,
            duration: 0.2,
            ease: "power2.in",
        },
        "-=0.1",
    );
}

export function playTooltipOpen(el) {
    if (!el) return;
    gsap.fromTo(
        el,
        { opacity: 0, y: -6, scale: 0.96 },
        {
            opacity: 1,
            y: 0,
            scale: 1,
            duration: 0.2,
            ease: "power2.out",
        },
    );
}

export function playTooltipClose(el, onComplete) {
    if (!el) {
        if (onComplete) onComplete();
        return;
    }
    gsap.to(el, {
        opacity: 0,
        y: -4,
        scale: 0.98,
        duration: 0.15,
        ease: "power2.in",
        onComplete,
    });
}

export function playPageEnter(el, done) {
    gsap.fromTo(
        el,
        { opacity: 0 },
        {
            opacity: 1,
            duration: 0.25,
            ease: "power2.out",
            onComplete: done,
        },
    );
}

export function playPageLeave(el, done) {
    gsap.to(el, {
        opacity: 0,
        duration: 0.2,
        ease: "power2.in",
        onComplete: done,
    });
}

export function playFloatLoop({ elements, options = {} }) {
    const targets = (elements || []).filter(Boolean);
    if (!targets.length) {
        return {
            kill() {},
        };
    }

    const {
        y = 12,
        x = 0,
        duration = 3,
        delay = 0,
        stagger = 0.18,
        ease = "sine.inOut",
    } = options;

    const tweens = targets.map((el, index) => {
        const directionX = index % 2 === 0 ? 1 : -1;
        const randomFactor = 0.7 + Math.random() * 0.6;

        const localY = y * randomFactor;
        const localX = x * randomFactor * directionX;
        const localDuration = duration * (0.8 + Math.random() * 0.6);
        const localDelay = delay + index * stagger + Math.random() * 0.4;

        return gsap.to(el, {
            y: localY,
            x: localX,
            duration: localDuration,
            delay: localDelay,
            ease,
            yoyo: true,
            repeat: -1,
        });
    });

    return {
        kill() {
            tweens.forEach((tween) => tween.kill());
        },
    };
}

/**
 * Циклический лёгкий пульс масштаба логотипа в мобильной шапке.
 * @param {HTMLElement|null|undefined} logoEl
 * @returns {{ kill: () => void }}
 */
export function playMobileNavbarLogoPulse(logoEl) {
    if (!logoEl) {
        return { kill() {} };
    }

    const tween = gsap.fromTo(
        logoEl,
        { scale: 1 },
        {
            scale: 1.06,
            duration: 2.1,
            ease: "power2.inOut",
            yoyo: true,
            repeat: -1,
        },
    );

    return {
        kill() {
            tween.kill();
            gsap.set(logoEl, { clearProps: "transform" });
        },
    };
}

/** @typedef {'mobile' | 'desktop'} DockAnimVariant */

/**
 * @param {HTMLElement|null|undefined} bar
 * @param {() => void} [onComplete]
 * @param {DockAnimVariant} [variant]
 */
export function playBottomBarShow(bar, onComplete, variant = "mobile") {
    if (!bar) return;

    // Bottom dock for both mobile and desktop (Y motion).
    void variant;

    gsap.fromTo(
        bar,
        { y: 40, opacity: 0 },
        {
            y: 0,
            opacity: 1,
            duration: 0.35,
            ease: "power2.out",
            onComplete,
        },
    );
}

/**
 * @param {HTMLElement|null|undefined} bar
 * @param {() => void} [onComplete]
 * @param {DockAnimVariant} [variant]
 */
export function playBottomBarHide(bar, onComplete, variant = "mobile") {
    if (!bar) {
        if (onComplete) onComplete();
        return;
    }

    void variant;

    gsap.to(bar, {
        y: 40,
        opacity: 0,
        duration: 0.25,
        ease: "power2.in",
        onComplete,
    });
}

export function prefersReducedMotion() {
    if (typeof window === "undefined") return false;
    return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
}

/**
 * @param {HTMLElement|null|undefined} container
 * @param {{ onlyItems?: HTMLElement[] }} [options]
 */
export function playCatalogItemsEnter(container, options = {}) {
    const items = (options.onlyItems ?? Array.from(
        container?.querySelectorAll(".catalog-item") ?? [],
    )).filter((el) => el?.isConnected);

    if (!items.length) return;

    gsap.killTweensOf(items);

    gsap.fromTo(
        items,
        { opacity: 0, y: 16 },
        {
            opacity: 1,
            y: 0,
            duration: 0.4,
            ease: "power2.out",
            stagger: 0.035,
        },
    );
}

/**
 * @param {HTMLElement|null|undefined} panel
 * @param {() => void} [onComplete]
 * @param {DockAnimVariant} [variant]
 */
export function playDockContentShow(panel, onComplete, variant = "mobile") {
    if (!panel) {
        if (onComplete) onComplete();
        return;
    }

    void variant;

    gsap.fromTo(
        panel,
        { opacity: 0, y: 10 },
        {
            opacity: 1,
            y: 0,
            duration: 0.25,
            ease: "power2.out",
            onComplete,
        },
    );
}

/**
 * @param {HTMLElement|null|undefined} panel
 * @param {() => void} [onComplete]
 * @param {DockAnimVariant} [variant]
 */
export function playDockContentHide(panel, onComplete, variant = "mobile") {
    if (!panel) {
        if (onComplete) onComplete();
        return;
    }

    void variant;

    gsap.to(panel, {
        opacity: 0,
        y: 10,
        duration: 0.2,
        ease: "power2.in",
        onComplete,
    });
}


export function playProductDetailInfoEnter(panel, options = {}) {
    if (!panel) return;

    const { delay = 0.4 } = options;

    gsap.fromTo(
        panel,
        { opacity: 0, y: 30 },
        {
            opacity: 1,
            y: 0,
            duration: 0.35,
            delay,
            ease: "power2.out",
        },
    );
}

/**
 * @param {{ shell: HTMLElement|null|undefined, variant?: 'mobile'|'desktop', onComplete?: () => void }} params
 */
export function playCatalogSearchOpen({ shell, variant = "mobile", onComplete }) {
    if (!shell) {
        onComplete?.();
        return;
    }

    gsap.killTweensOf(shell);

    if (variant === "mobile") {
        gsap.fromTo(
            shell,
            { yPercent: 100, opacity: 0.94 },
            {
                yPercent: 0,
                opacity: 1,
                duration: 0.38,
                ease: "power3.out",
                onComplete,
            },
        );
        return;
    }

    gsap.fromTo(
        shell,
        { opacity: 0, y: -16 },
        {
            opacity: 1,
            y: 0,
            duration: 0.3,
            ease: "power2.out",
            onComplete,
        },
    );
}

/**
 * @param {{ shell: HTMLElement|null|undefined, variant?: 'mobile'|'desktop', onComplete?: () => void }} params
 */
export function playCatalogSearchClose({ shell, variant = "mobile", onComplete }) {
    if (!shell) {
        onComplete?.();
        return;
    }

    gsap.killTweensOf(shell);

    if (variant === "mobile") {
        gsap.to(shell, {
            yPercent: 100,
            opacity: 0.94,
            duration: 0.28,
            ease: "power2.in",
            onComplete,
        });
        return;
    }

    gsap.to(shell, {
        opacity: 0,
        y: -12,
        duration: 0.22,
        ease: "power2.in",
        onComplete,
    });
}

/**
 * @param {HTMLElement|null|undefined} panel
 * @param {() => void} [onComplete]
 */
export function playSearchPanelCrossfade(panel, onComplete) {
    if (!panel) {
        onComplete?.();
        return;
    }

    gsap.killTweensOf(panel);
    gsap.fromTo(
        panel,
        { opacity: 0, y: 8 },
        {
            opacity: 1,
            y: 0,
            duration: 0.22,
            ease: "power2.out",
            onComplete,
        },
    );
}

