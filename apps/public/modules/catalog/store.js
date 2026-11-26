import { defineStore } from "pinia";
import { fetchCatalog } from "../../api/catalog";
import { mapCatalogTreeFromPayload } from "./domain/buildCatalogTree";
import { mapApiError } from "../../platform/mapApiError";
import { useContentStore } from "../content/store";

const CATALOG_STORAGE_KEY = "gangsters_catalog";
const DESKTOP_CARDS_PER_ROW_DEFAULT = 4;
/** Sync with uiStore / Tailwind `md`. */
const DESKTOP_MEDIA_QUERY = "(min-width: 768px)";

/**
 * Дефолт mobile-вида каталога: на узком экране — горизонтальный список,
 * на desktop (поле почти не используется) — grid.
 * @returns {"grid"|"horizontal"}
 */
function resolveDefaultMobileCardViewMode() {
    if (typeof window === "undefined") {
        return "horizontal";
    }

    try {
        return window.matchMedia(DESKTOP_MEDIA_QUERY).matches ? "grid" : "horizontal";
    } catch {
        return "horizontal";
    }
}

function normalizeDesktopCardsPerRow(value) {
    return value === 3 || value === 4 ? value : DESKTOP_CARDS_PER_ROW_DEFAULT;
}

function normalizeMobileCardViewMode(value) {
    return value === "grid" || value === "horizontal" ? value : resolveDefaultMobileCardViewMode();
}

/** Защита от гонки: результат применяется только у последнего запроса (смена филиала). */
let catalogFetchSeq = 0;

export const useCatalogStore = defineStore("catalog", {
    state: () => ({
        categories: [],
        accompanyingCategories: [],
        complementProducts: [],
        loading: false,
        error: null,
        selectedCategoryId: null,
        selectedProduct: null,
        hasLoaded: false,
        selectedTag: null,
        desktopCardsPerRow: DESKTOP_CARDS_PER_ROW_DEFAULT,
        mobileCardViewMode: resolveDefaultMobileCardViewMode(),
    }),
    getters: {
        /**
         * Основное меню = обычные категории + сопутствующие (флаг is_accompanying).
         * Upsell чекаута по-прежнему читает accompanyingCategories отдельно.
         */
        menuCategoryEntries(state) {
            const main = Array.isArray(state.categories) ? state.categories : [];
            const accompanying = Array.isArray(state.accompanyingCategories)
                ? state.accompanyingCategories
                : [];
            return [...main, ...accompanying].sort(
                (a, b) => (a?.category?.sort_order ?? 0) - (b?.category?.sort_order ?? 0),
            );
        },
        flatProducts() {
            return this.menuCategoryEntries.flatMap((entry) => entry.products || []);
        },
        filteredProducts(state) {
            if (state.selectedCategoryId == null || state.selectedCategoryId === "") {
                return this.flatProducts;
            }

            const selected = state.selectedCategoryId;
            const entry = this.menuCategoryEntries.find((c) => {
                const id = c.category.id;
                const slug = c.category.slug;
                return (
                    (id != null && Number(selected) === Number(id)) ||
                    (slug && String(selected) === String(slug))
                );
            });
            if (!entry) {
                return [];
            }

            return entry.products || [];
        },
        /**
         * Лента меню: все категории (включая сопутствующие); фильтр только по тегу.
         * Активная категория бара — scroll-spy, не фильтр ленты.
         */
        menuSections() {
            const selectedTag = this.selectedTag;
            const byTag = (product) => {
                if (!selectedTag) return true;
                const tags = Array.isArray(product.tags) ? product.tags : [];
                return tags.some((tag) => String(tag?.code) === String(selectedTag));
            };

            const entries = this.menuCategoryEntries;
            const orderMap = new Map(
                entries.map((entry, index) => [
                    entry?.category?.id ?? entry?.category?.slug ?? null,
                    index,
                ]),
            );

            return entries
                .map((entry) => {
                    const base = Array.isArray(entry?.products) ? entry.products : [];
                    const products = base.filter((p) => byTag(p));
                    return {
                        id: entry?.category?.id ?? entry?.category?.slug ?? null,
                        name: entry?.category?.name || "Без категории",
                        products,
                    };
                })
                .filter((section) => section.products.length > 0)
                .sort((a, b) => (orderMap.get(a.id) ?? 0) - (orderMap.get(b.id) ?? 0));
        },
        menuProducts() {
            return this.menuSections.flatMap((section) => section.products);
        },
        categoryTabs() {
            return this.menuCategoryEntries.map((entry) => ({
                id: entry.category.id,
                name: entry.category.name,
                uri: entry.category.slug,
            }));
        },
        tagTabs() {
            const map = new Map();
            this.flatProducts.forEach((product) => {
                const tags = Array.isArray(product.tags) ? product.tags : [];
                tags.forEach((tag) => {
                    const code = String(tag?.code || "").trim();
                    if (!code || map.has(code)) return;

                    map.set(code, {
                        id: code,
                        name: String(tag?.label || code),
                    });
                });
            });

            return Array.from(map.values());
        },
    },
    actions: {
        initFromStorage() {
            if (typeof window === "undefined") return;

            try {
                const raw = window.localStorage.getItem(CATALOG_STORAGE_KEY);
                if (!raw) {
                    this.mobileCardViewMode = resolveDefaultMobileCardViewMode();
                    this.persist();
                    return;
                }

                const parsed = JSON.parse(raw);
                if (!parsed || typeof parsed !== "object") {
                    this.mobileCardViewMode = resolveDefaultMobileCardViewMode();
                    this.persist();
                    return;
                }

                if ("selectedCategoryId" in parsed) {
                    this.selectedCategoryId = parsed.selectedCategoryId ?? null;
                }
                if ("selectedTag" in parsed) {
                    this.selectedTag = parsed.selectedTag ?? null;
                }
                if ("desktopCardsPerRow" in parsed) {
                    this.desktopCardsPerRow = normalizeDesktopCardsPerRow(
                        parsed.desktopCardsPerRow,
                    );
                }
                if ("mobileCardViewMode" in parsed) {
                    this.mobileCardViewMode = normalizeMobileCardViewMode(
                        parsed.mobileCardViewMode,
                    );
                } else {
                    this.mobileCardViewMode = resolveDefaultMobileCardViewMode();
                    this.persist();
                }
            } catch (e) {
                console.error("Failed to init catalog store from localStorage", e);
            }
        },
        persist() {
            if (typeof window === "undefined") return;

            window.localStorage.setItem(
                CATALOG_STORAGE_KEY,
                JSON.stringify({
                    selectedCategoryId: this.selectedCategoryId,
                    selectedTag: this.selectedTag,
                    desktopCardsPerRow: this.desktopCardsPerRow,
                    mobileCardViewMode: this.mobileCardViewMode,
                }),
            );
        },
        setSelectedCategoryId(categoryId) {
            this.selectedCategoryId = categoryId ?? null;
            this.persist();
        },
        setSelectedTag(tagCode) {
            this.selectedTag = tagCode ?? null;
            this.persist();
        },
        setSelectedProduct(product) {
            this.selectedProduct = product ?? null;
        },
        setDesktopCardsPerRow(value) {
            this.desktopCardsPerRow = normalizeDesktopCardsPerRow(value);
            this.persist();
        },
        setMobileCardViewMode(value) {
            this.mobileCardViewMode = normalizeMobileCardViewMode(value);
            this.persist();
        },

        /**
         * Сбрасывает category/tag из localStorage, если после загрузки дерева
         * они больше не существуют (иначе menuProducts пустой при живых данных).
         */
        sanitizePersistedFiltersAfterLoad() {
            const categories = this.menuCategoryEntries;
            if (!Array.isArray(categories) || categories.length === 0) {
                return;
            }

            let changed = false;
            const sel = this.selectedCategoryId;
            if (sel != null && sel !== "") {
                const found = categories.some((entry) => {
                    const id = entry.category?.id;
                    const slug = entry.category?.slug;
                    return (
                        (id != null && Number(sel) === Number(id)) ||
                        (slug && String(sel) === String(slug))
                    );
                });
                if (!found) {
                    this.selectedCategoryId = null;
                    changed = true;
                }
            }

            const tag = this.selectedTag;
            if (tag) {
                const flat = categories.flatMap((e) => e.products || []);
                const tagOk = flat.some((product) => {
                    const tags = Array.isArray(product.tags) ? product.tags : [];
                    return tags.some((t) => String(t?.code) === String(tag));
                });
                if (!tagOk) {
                    this.selectedTag = null;
                    changed = true;
                }
            }

            if (changed) {
                this.persist();
            }
        },

        async fetchAll() {
            const seq = ++catalogFetchSeq;
            this.loading = true;
            this.error = null;

            try {
                const branchId = await useContentStore().ensureSelectedBranchId();
                if (branchId == null) {
                    throw new Error("Нет доступных филиалов.");
                }

                const response = { data: await fetchCatalog(branchId) };
                if (seq !== catalogFetchSeq) {
                    return;
                }
                const tree = mapCatalogTreeFromPayload(response?.data || {});
                this.categories = Array.isArray(tree?.categories) ? tree.categories : [];
                this.accompanyingCategories = Array.isArray(tree?.accompanyingCategories)
                    ? tree.accompanyingCategories
                    : [];
                this.complementProducts = Array.isArray(tree?.complementProducts)
                    ? tree.complementProducts
                    : [];
                this.sanitizePersistedFiltersAfterLoad();

                this.hasLoaded = true;
            } catch (e) {
                if (seq !== catalogFetchSeq) {
                    return;
                }
                console.error("Failed to fetch catalog", e);
                this.error = mapApiError(
                    e,
                    "Не удалось загрузить каталог. Попробуйте обновить страницу.",
                );
            } finally {
                if (seq === catalogFetchSeq) {
                    this.loading = false;
                }
            }
        },
    },
});
