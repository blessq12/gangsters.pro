<script setup>
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    provide,
    ref,
    watch,
} from "vue";
import {
    playCatalogSearchClose,
    playCatalogSearchOpen,
    playSearchPanelCrossfade,
} from "../../animations/animationManager";
import { useAppDesign } from "../../design/useAppDesign";
import { CatalogSearchActionSourceKey } from "../../modules/catalog/application/search";
import { useCatalogSearch } from "../../modules/catalog/application/search";
import { useUiStore } from "../../modules/shell/store/uiStore";
import {
    pushBodyScrollLock,
    popBodyScrollLock,
} from "../../platform/document";
import CatalogSearchDiscoverFeed from "./CatalogSearchDiscoverFeed.vue";
import CatalogSearchEmptyPanel from "./CatalogSearchEmptyPanel.vue";
import CatalogSearchIdlePanel from "./CatalogSearchIdlePanel.vue";
import CatalogSearchResults from "./CatalogSearchResults.vue";
import ProductDetailModalBase from "./ProductDetailModalBase.vue";

provide(CatalogSearchActionSourceKey, "catalog-search");

const cs = useAppDesign().components.catalog.search;
const uiStore = useUiStore();

const {
    query,
    isOpen,
    bodyState,
    results,
    loading,
    idleHint,
    emptyHint,
    searchHistory,
    discoverItems,
    hasDiscoverFeed,
    loadMoreDiscover,
    openSearch: _openSearch,
    requestCloseSearch,
    resetSession,
    clearQuery,
    applyQuery,
    openProductDetail,
    markLayerReady,
    showProductDetailModal,
    selectedProduct,
    desktopCardsPerRow,
    mobileCardViewMode,
} = useCatalogSearch();

const isVisible = ref(false);
const shellRef = ref(null);
const bodyScrollRef = ref(null);
const bodyPanelRef = ref(null);
const searchInputRef = ref(null);
const headerFocused = ref(false);
let bodyScrollLocksHeld = 0;
let closing = false;

const isDesktop = computed(() => uiStore.deviceMode === "desktop");
const animVariant = computed(() => (isDesktop.value ? "desktop" : "mobile"));

const resultsCountLabel = computed(() => {
    const count = results.value.length;
    if (bodyState.value !== "results") {
        return "";
    }

    const mod10 = count % 10;
    const mod100 = count % 100;
    let word = "позиций";
    if (mod10 === 1 && mod100 !== 11) {
        word = "позиция";
    } else if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) {
        word = "позиции";
    }

    return `Найдено ${count} ${word}`;
});

function lockBodyScroll() {
    pushBodyScrollLock();
    bodyScrollLocksHeld += 1;
}

function unlockBodyScroll() {
    if (bodyScrollLocksHeld === 0) return;
    popBodyScrollLock();
    bodyScrollLocksHeld -= 1;
}

function focusSearchInput() {
    void nextTick(() => {
        searchInputRef.value?.focus();
    });
}

function handleApplyQuery(value) {
    applyQuery(value);
    focusSearchInput();
}

function runOpenAnimation() {
    playCatalogSearchOpen({
        shell: shellRef.value,
        variant: animVariant.value,
        onComplete: () => {
            markLayerReady();
            focusSearchInput();
        },
    });
}

function runCloseAnimation(onComplete) {
    playCatalogSearchClose({
        shell: shellRef.value,
        variant: animVariant.value,
        onComplete,
    });
}

function beginClose() {
    if (closing) {
        return;
    }

    if (!isVisible.value || !shellRef.value) {
        requestCloseSearch();
        resetSession();
        isVisible.value = false;
        unlockBodyScroll();
        return;
    }

    closing = true;
    requestCloseSearch();
    runCloseAnimation(() => {
        closing = false;
        isVisible.value = false;
        resetSession();
        unlockBodyScroll();
    });
}

function handleEscape(event) {
    if (event.key !== "Escape" || !isVisible.value) {
        return;
    }

    if (showProductDetailModal.value) {
        return;
    }

    beginClose();
}

watch(isOpen, async (open) => {
    if (open) {
        isVisible.value = true;
        lockBodyScroll();
        await nextTick();
        runOpenAnimation();
        return;
    }

    if (isVisible.value && !closing) {
        beginClose();
    }
});

watch(bodyState, async () => {
    await nextTick();
    playSearchPanelCrossfade(bodyPanelRef.value);
});

onMounted(() => {
    window.addEventListener("keydown", handleEscape);
    if (isOpen.value) {
        isVisible.value = true;
        lockBodyScroll();
        void nextTick(() => runOpenAnimation());
    }
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleEscape);
    while (bodyScrollLocksHeld > 0) {
        popBodyScrollLock();
        bodyScrollLocksHeld -= 1;
    }
});

defineExpose({ openSearch: _openSearch });
</script>

<template>
    <teleport to="body">
        <div
            v-if="isVisible"
            ref="shellRef"
            :class="cs.overlay"
            role="dialog"
            aria-modal="true"
            aria-label="Поиск по меню"
        >
            <header :class="[cs.header, headerFocused && cs.headerFocused]">
                <div :class="cs.headerRow">
                    <button
                        type="button"
                        :class="cs.closeBtn"
                        aria-label="Закрыть поиск"
                        @click="beginClose"
                    >
                        <i class="mdi mdi-arrow-left text-lg" />
                    </button>

                    <p :class="cs.headerTitle">
                        Поиск
                    </p>

                    <div :class="cs.searchWrap">
                        <i
                            class="mdi mdi-magnify"
                            :class="cs.searchIcon"
                            aria-hidden="true"
                        />
                        <input
                            ref="searchInputRef"
                            v-model="query"
                            type="search"
                            autocomplete="off"
                            placeholder="Название, ингредиент, тег, категория…"
                            :class="cs.searchInput"
                            @focus="headerFocused = true"
                            @blur="headerFocused = false"
                        />
                        <button
                            v-if="query.trim()"
                            type="button"
                            :class="cs.clearBtn"
                            aria-label="Очистить запрос"
                            @click="clearQuery"
                        >
                            <i class="mdi mdi-close text-lg" />
                        </button>
                    </div>
                </div>
            </header>

            <div
                ref="bodyScrollRef"
                :class="cs.body"
            >
                <div
                    ref="bodyPanelRef"
                    :class="cs.bodyInner"
                >
                    <template v-if="bodyState === 'idle'">
                        <CatalogSearchIdlePanel
                            :title="idleHint.title"
                            :lead="idleHint.lead"
                            :examples="idleHint.examples"
                            :history="searchHistory"
                            @apply-query="handleApplyQuery"
                        />

                        <CatalogSearchDiscoverFeed
                            v-if="hasDiscoverFeed"
                            :products="discoverItems"
                            :scroll-root="bodyScrollRef"
                            :variant="isDesktop ? 'desktop' : 'mobile'"
                            :cards-per-row="desktopCardsPerRow"
                            :mobile-card-view-mode="mobileCardViewMode"
                            @load-more="loadMoreDiscover"
                            @product-image-click="openProductDetail"
                        />
                    </template>

                    <div
                        v-else-if="bodyState === 'loading'"
                        :class="cs.loadingWrap"
                    >
                        Загружаем меню…
                    </div>

                    <CatalogSearchEmptyPanel
                        v-else-if="bodyState === 'empty'"
                        :title="emptyHint.title"
                        :lead="emptyHint.lead"
                        :examples="emptyHint.examples"
                        :query="query.trim()"
                        @apply-query="handleApplyQuery"
                        @clear-query="clearQuery"
                    />

                    <template v-else>
                        <p
                            v-if="resultsCountLabel"
                            :class="cs.resultsMeta"
                        >
                            {{ resultsCountLabel }}
                        </p>

                        <CatalogSearchResults
                            :products="results"
                            :loading="loading"
                            :variant="isDesktop ? 'desktop' : 'mobile'"
                            :cards-per-row="desktopCardsPerRow"
                            :mobile-card-view-mode="mobileCardViewMode"
                            @product-image-click="openProductDetail"
                        />
                    </template>
                </div>
            </div>

            <ProductDetailModalBase
                v-model="showProductDetailModal"
                :product="selectedProduct"
                :variant="isDesktop ? 'desktop' : 'mobile'"
            />
        </div>
    </teleport>
</template>
