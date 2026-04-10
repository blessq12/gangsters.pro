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

export function playIntroScene({ introOverlay, introLogo, main, onComplete }) {
    if (!introOverlay || !introLogo || !main) return;

    const tl = gsap.timeline();

    tl.fromTo(
        introLogo,
        { scale: 0.6, opacity: 0 },
        { scale: 1.2, opacity: 1, duration: 0.5, ease: "back.out(1.7)" },
    )
        .to(introLogo, { duration: 0.2 })
        .to(introLogo, {
            scale: 0,
            duration: 0.5,
            ease: "power2.out",
        })
        .to(
            main,
            {
                opacity: 1,
                duration: 0.4,
                ease: "power2.out",
            },
            "-=0.2",
        )
        .to(
            introOverlay,
            {
                opacity: 0,
                duration: 0.4,
                ease: "power2.out",
                onComplete,
            },
            "-=0.3",
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

export function playBannerSticks({ left, right }) {
    if (!left && !right) return;

    if (left) {
        gsap.fromTo(
            left,
            { y: -10, rotate: -10 },
            {
                y: 5,
                rotate: -2,
                duration: 2.2,
                ease: "sine.inOut",
                yoyo: true,
                repeat: -1,
            },
        );
    }

    if (right) {
        gsap.fromTo(
            right,
            { y: 10, rotate: 8 },
            {
                y: -4,
                rotate: 2,
                duration: 2.4,
                ease: "sine.inOut",
                yoyo: true,
                repeat: -1,
            },
        );
    }
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
 * @param {DockAnimVariant} [variant]
 */
export function playBottomBarShow(bar, variant = "mobile") {
    if (!bar) return;

    if (variant === "desktop") {
        gsap.fromTo(
            bar,
            { x: -40, opacity: 0 },
            {
                x: 0,
                opacity: 1,
                duration: 0.32,
                ease: "power2.out",
            },
        );
        return;
    }

    gsap.fromTo(
        bar,
        { y: 40, opacity: 0 },
        {
            y: 0,
            opacity: 1,
            duration: 0.35,
            ease: "power2.out",
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

    if (variant === "desktop") {
        gsap.to(bar, {
            x: -36,
            opacity: 0,
            duration: 0.22,
            ease: "power2.in",
            onComplete,
        });
        return;
    }

    gsap.to(bar, {
        y: 40,
        opacity: 0,
        duration: 0.25,
        ease: "power2.in",
        onComplete,
    });
}


export function playCatalogItemsEnter(container) {
    if (!container) return;

    const items = container.querySelectorAll(".catalog-item");
    if (!items.length) return;

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

    if (variant === "desktop") {
        gsap.fromTo(
            panel,
            { opacity: 0, x: -16 },
            {
                opacity: 1,
                x: 0,
                duration: 0.24,
                ease: "power2.out",
                onComplete,
            },
        );
        return;
    }

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

    if (variant === "desktop") {
        gsap.to(panel, {
            opacity: 0,
            x: -12,
            duration: 0.18,
            ease: "power2.in",
            onComplete,
        });
        return;
    }

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


