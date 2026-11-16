import {
    nextTick,
    onBeforeUnmount,
    ref,
    unref,
    watch,
} from "vue";

/** Селектор секций ленты ({@link CatalogProductsBase}). */
export const CATALOG_SECTION_ATTR = "data-catalog-category-section";

/** Селектор пилюль бара ({@link CatalogCategoriesBase}). */
export const CATALOG_PILL_ATTR = "data-catalog-category-pill";

const STICKY_TOP_PX = 16;
const CLICK_LOCK_MS = 900;

function sameCategoryId(a, b) {
    if (a == null && b == null) return true;
    if (a == null || b == null) return false;
    return String(a) === String(b);
}

function readSectionId(el) {
    if (!el?.hasAttribute?.(CATALOG_SECTION_ATTR)) return null;
    const raw = el.getAttribute(CATALOG_SECTION_ATTR);
    return raw === "" ? null : raw;
}

/**
 * Бар категорий: IntersectionObserver → active + scroll к секции.
 * Offset под fixed-копию бара (pinOnScroll). На mobile — scroll пилюли в ряду.
 *
 * @param {object} options
 * @param {import('vue').Ref<HTMLElement|null>|HTMLElement|null} options.productsRef
 * @param {import('vue').Ref<HTMLElement|null>|HTMLElement|null} options.barRef — активный бар (in-flow или fixed)
 * @param {import('vue').WritableComputedRef<string|number|null>|import('vue').Ref<string|number|null>} options.activeId
 * @param {import('vue').Ref<boolean>|boolean} [options.enabled]
 * @param {boolean} [options.syncPillScroll] — horizontal scroll пилюли (только mobile)
 * @param {number} [options.stickyTopPx] — top fixed-острова + зазор
 */
export function useCatalogCategoryScrollSpy({
    productsRef,
    barRef,
    activeId,
    enabled = true,
    syncPillScroll = false,
    stickyTopPx = STICKY_TOP_PX,
} = {}) {
    const barHeightPx = ref(0);

    let observer = null;
    let resizeObserver = null;
    let visibleIds = new Set();
    let clickLockUntil = 0;
    let unlockTimer = null;
    let scrollRaf = 0;

    function isEnabled() {
        return Boolean(unref(enabled));
    }

    function productsEl() {
        return unref(productsRef) ?? null;
    }

    function barEl() {
        return unref(barRef) ?? null;
    }

    function measureBar() {
        const el = barEl();
        barHeightPx.value = el ? Math.ceil(el.getBoundingClientRect().height) : 0;
    }

    function spyOffsetPx() {
        return stickyTopPx + barHeightPx.value;
    }

    function sectionElements() {
        const root = productsEl();
        if (!root) return [];
        return Array.from(root.querySelectorAll(`[${CATALOG_SECTION_ATTR}]`));
    }

    function findSection(id) {
        const sections = sectionElements();
        if (id == null || id === "") {
            return sections[0] ?? null;
        }
        const key = String(id);
        return (
            sections.find((el) => String(readSectionId(el)) === key) ?? null
        );
    }

    function findPill(id) {
        const root = barEl();
        if (!root) return null;
        const key = id == null || id === "" ? "__all__" : String(id);
        return root.querySelector(`[${CATALOG_PILL_ATTR}="${CSS.escape(key)}"]`);
    }

    function setActive(id, { fromSpy = false } = {}) {
        if (fromSpy && Date.now() < clickLockUntil) return;
        if (sameCategoryId(unref(activeId), id)) return;
        activeId.value = id;
    }

    function scrollActivePillIntoView() {
        if (!syncPillScroll || !isEnabled()) return;
        const pill = findPill(unref(activeId));
        if (!pill) return;
        pill.scrollIntoView({
            behavior: "smooth",
            inline: "center",
            block: "nearest",
        });
    }

    function lockSpyFromClick() {
        clickLockUntil = Date.now() + CLICK_LOCK_MS;
        if (unlockTimer) clearTimeout(unlockTimer);
        unlockTimer = setTimeout(() => {
            clickLockUntil = 0;
            unlockTimer = null;
        }, CLICK_LOCK_MS);
    }

    /**
     * Клик по пилюле: active + скролл к секции («Все» → первая секция / верх ленты).
     * @param {string|number|null} id
     */
    function scrollToCategory(id) {
        if (!isEnabled()) {
            activeId.value = id ?? null;
            return;
        }

        lockSpyFromClick();
        activeId.value = id ?? null;

        const target = findSection(id);
        const root = productsEl();
        const el = target ?? root;
        if (!el) return;

        const top =
            el.getBoundingClientRect().top +
            window.scrollY -
            spyOffsetPx() -
            8;

        window.scrollTo({
            top: Math.max(0, top),
            behavior: "smooth",
        });

        nextTick(() => scrollActivePillIntoView());
    }

    function pickActiveFromVisible() {
        if (!isEnabled()) return;
        if (Date.now() < clickLockUntil) return;

        const sections = sectionElements();
        if (!sections.length) return;

        for (const el of sections) {
            const id = readSectionId(el);
            const key = id == null ? "" : String(id);
            if (visibleIds.has(key)) {
                setActive(id, { fromSpy: true });
                return;
            }
        }
    }

    function disconnectObserver() {
        if (observer) {
            observer.disconnect();
            observer = null;
        }
        visibleIds = new Set();
    }

    function connectObserver() {
        disconnectObserver();
        if (!isEnabled() || typeof IntersectionObserver === "undefined") return;

        measureBar();
        const sections = sectionElements();
        if (!sections.length) return;

        const offset = spyOffsetPx();
        observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    const id = readSectionId(entry.target);
                    const key = id == null ? "" : String(id);
                    if (entry.isIntersecting) {
                        visibleIds.add(key);
                    } else {
                        visibleIds.delete(key);
                    }
                }
                pickActiveFromVisible();
                if (syncPillScroll) {
                    nextTick(() => scrollActivePillIntoView());
                }
            },
            {
                root: null,
                // Зона «под» sticky-баром: верх режется offset, низ — чтобы активна верхняя секция.
                rootMargin: `-${Math.max(0, offset)}px 0px -45% 0px`,
                threshold: [0, 0.05, 0.1, 0.25],
            },
        );

        sections.forEach((el) => observer.observe(el));
    }

    function reconnectSoon() {
        if (scrollRaf) cancelAnimationFrame(scrollRaf);
        scrollRaf = requestAnimationFrame(() => {
            scrollRaf = 0;
            connectObserver();
            pickActiveFromVisible();
        });
    }

    function bindBarResize() {
        if (resizeObserver) {
            resizeObserver.disconnect();
            resizeObserver = null;
        }
        const el = barEl();
        if (!el || typeof ResizeObserver === "undefined") {
            measureBar();
            return;
        }
        resizeObserver = new ResizeObserver(() => {
            const prev = barHeightPx.value;
            measureBar();
            if (Math.abs(prev - barHeightPx.value) >= 1) {
                reconnectSoon();
            }
        });
        resizeObserver.observe(el);
        measureBar();
    }

    watch(
        () => [unref(productsRef), unref(barRef), unref(enabled)],
        async () => {
            await nextTick();
            bindBarResize();
            reconnectSoon();
        },
        { immediate: true, flush: "post" },
    );

    watch(
        () => unref(activeId),
        () => {
            if (!syncPillScroll) return;
            nextTick(() => scrollActivePillIntoView());
        },
    );

    onBeforeUnmount(() => {
        disconnectObserver();
        if (resizeObserver) {
            resizeObserver.disconnect();
            resizeObserver = null;
        }
        if (unlockTimer) clearTimeout(unlockTimer);
        if (scrollRaf) cancelAnimationFrame(scrollRaf);
    });

    return {
        barHeightPx,
        scrollToCategory,
        reconnect: reconnectSoon,
    };
}
