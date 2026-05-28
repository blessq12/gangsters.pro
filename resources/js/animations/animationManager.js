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

/** @deprecated Используйте INTRO_DOCK_REVEAL_GAP_MS */
export const INTRO_BOTTOM_BAR_DELAY_MS = INTRO_DOCK_REVEAL_GAP_MS;

/**
 * Длительность playIntroScene до onComplete (сек), по константам overlap.
 */
export function getIntroSceneDurationSec() {
    const logoEnd =
        INTRO_LOGO_IN_DURATION +
        INTRO_LOGO_HOLD_DURATION +
        INTRO_LOGO_OUT_DURATION;
    const mainEnd = logoEnd - INTRO_MAIN_FADE_OVERLAP + INTRO_MAIN_FADE_DURATION;
    return mainEnd - INTRO_OVERLAY_FADE_OVERLAP + INTRO_OVERLAY_FADE_DURATION;
}

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

export function playIntroScene({
    introOverlay,
    introLogo,
    main,
    introGlow,
    onComplete,
}) {
    if (!introOverlay || !introLogo || !main) return;

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
 * @param {() => void} [onComplete]
 * @param {DockAnimVariant} [variant]
 */
export function playBottomBarShow(bar, onComplete, variant = "mobile") {
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
                onComplete,
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

/**
 * @param {HTMLElement|null|undefined} panel
 * @param {() => void} [onComplete]
 * @param {DockAnimVariant} [variant]
 */
export function playTopBenefitsShow(panel, onComplete, variant = "mobile") {
    if (!panel) return;
    gsap.fromTo(
        panel,
        { y: -28, opacity: 0 },
        {
            y: 0,
            opacity: 1,
            duration: variant === "desktop" ? 0.32 : 0.35,
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
export function playTopBenefitsHide(panel, onComplete, variant = "mobile") {
    if (!panel) {
        if (onComplete) onComplete();
        return;
    }
    gsap.to(panel, {
        y: -24,
        opacity: 0,
        duration: variant === "desktop" ? 0.22 : 0.25,
        ease: "power2.in",
        onComplete,
    });
}


let flyProductToCartTween = null;

export function prefersReducedMotion() {
    if (typeof window === "undefined") return false;
    return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
}

function getVisibleRect(el) {
    if (!el?.isConnected) return null;
    const rect = el.getBoundingClientRect();
    if (rect.width <= 0 || rect.height <= 0) return null;
    return rect;
}

/**
 * Цель fly: dock cart tab, иначе navbar cart.
 * @returns {HTMLElement|null}
 */
export function resolveCartFlyTargetEl() {
    if (typeof document === "undefined") return null;

    const dockTab = document.querySelector('[data-dock-target="cart"]');
    if (getVisibleRect(dockTab)) return dockTab;

    return dockTab;
}

let skipNextCartBadgeBump = false;

export function markCartBadgeBumpFromFly() {
    skipNextCartBadgeBump = true;
}

/**
 * Заметный bump вкладки dock: пилл tabIconWrap + бейдж (не .mdi).
 * @param {"cart"|"favorites"|string} dockId
 */
export function playDockTabBump(dockId) {
    if (typeof document === "undefined" || prefersReducedMotion()) return;

    const bumpRoot = document.querySelector(`[data-dock-bump-root="${dockId}"]`);
    const badge = document.querySelector(`[data-dock-badge="${dockId}"]`);
    const tabBtn = document.querySelector(`[data-dock-target="${dockId}"]`);

    const tabEl =
        bumpRoot?.isConnected ? bumpRoot : tabBtn?.isConnected ? tabBtn : null;

    if (tabEl) {
        gsap.killTweensOf(tabEl);
        gsap.fromTo(
            tabEl,
            { scale: 1, y: 0, transformOrigin: "50% 50%", force3D: true },
            {
                scale: 1.2,
                y: -6,
                duration: 0.18,
                ease: "power2.out",
                yoyo: true,
                repeat: 1,
            },
        );
    }

    if (badge?.isConnected) {
        gsap.killTweensOf(badge);
        gsap.fromTo(
            badge,
            {
                scale: 1,
                transformOrigin: "50% 50%",
                force3D: true,
                boxShadow: "0 0 8px rgba(239,68,68,0.65)",
            },
            {
                scale: 1.45,
                boxShadow: "0 0 16px rgba(239,68,68,0.95)",
                duration: 0.2,
                ease: "back.out(1.6)",
                yoyo: true,
                repeat: 1,
            },
        );
    }
}

/** @deprecated Используйте playDockTabBump */
export function playDockBadgeBump(dockId) {
    playDockTabBump(dockId);
}

/**
 * @param {HTMLElement|null|undefined} targetEl
 */
export function playDockCartTabBump(targetEl) {
    if (!targetEl?.isConnected || prefersReducedMotion()) return;
    const dockId = targetEl.getAttribute("data-dock-target") || "cart";
    playDockTabBump(dockId);
}

export function consumeSkipNextCartBadgeBump() {
    if (!skipNextCartBadgeBump) return false;
    skipNextCartBadgeBump = false;
    return true;
}

/**
 * @param {{
 *   sourceEl?: HTMLElement|null,
 *   imageUrl?: string,
 *   targetEl?: HTMLElement|null,
 *   onComplete?: () => void,
 * }} params
 */
export function playFlyProductToCart({
    sourceEl,
    imageUrl,
    targetEl,
    onComplete,
} = {}) {
    if (typeof document === "undefined") {
        onComplete?.();
        return;
    }

    const target = targetEl || resolveCartFlyTargetEl();
    const sourceRect = getVisibleRect(sourceEl);
    const targetRect = getVisibleRect(target);

    if (prefersReducedMotion() || !sourceRect || !targetRect) {
        if (target) {
            markCartBadgeBumpFromFly();
            playDockTabBump("cart");
        }
        onComplete?.();
        return;
    }

    if (flyProductToCartTween) {
        flyProductToCartTween.kill();
        flyProductToCartTween = null;
    }

    const ghost = document.createElement("div");
    ghost.setAttribute("aria-hidden", "true");
    ghost.style.cssText =
        "position:fixed;z-index:10001;pointer-events:none;overflow:hidden;border-radius:2px;box-shadow:0 8px 24px rgba(0,0,0,0.45);";

    const size = Math.min(Math.max(sourceRect.width * 0.35, 48), 88);
    ghost.style.width = `${size}px`;
    ghost.style.height = `${size}px`;
    ghost.style.left = `${sourceRect.left + sourceRect.width / 2 - size / 2}px`;
    ghost.style.top = `${sourceRect.top + sourceRect.height / 2 - size / 2}px`;

    if (imageUrl) {
        const img = document.createElement("img");
        img.src = imageUrl;
        img.alt = "";
        img.style.cssText =
            "width:100%;height:100%;object-fit:cover;display:block;";
        ghost.appendChild(img);
    } else {
        ghost.style.background = "rgba(198,36,36,0.85)";
    }

    document.body.appendChild(ghost);

    const endX = targetRect.left + targetRect.width / 2 - size / 2;
    const endY = targetRect.top + targetRect.height / 2 - size / 2;

    flyProductToCartTween = gsap.to(ghost, {
        left: endX,
        top: endY,
        scale: 0.35,
        opacity: 0.15,
        duration: 0.68,
        ease: "power2.inOut",
        onComplete: () => {
            ghost.remove();
            flyProductToCartTween = null;
            if (target) {
                markCartBadgeBumpFromFly();
                playDockTabBump("cart");
            }
            onComplete?.();
        },
    });
}

export function playCatalogItemsEnter(container) {
    if (!container?.isConnected) return;

    const items = Array.from(container.querySelectorAll(".catalog-item")).filter(
        (el) => el.isConnected,
    );
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


