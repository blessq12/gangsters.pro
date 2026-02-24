import { gsap } from "gsap";

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



