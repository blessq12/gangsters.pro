<script setup>
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useUiStore } from "../../modules/shell/store/uiStore";

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: [Number, String, null],
        default: null,
    },
    allLabel: {
        type: String,
        default: "Все",
    },
    showAll: {
        type: Boolean,
        default: true,
    },
    variant: {
        type: String,
        default: "desktop", // desktop | mobile
    },
    /**
     * При уходе бара вверх — рендер fixed-копии с island-отступом.
     * Исходный бар остаётся в потоке без sticky.
     */
    pinOnScroll: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

const dc = useAppDesign().components.catalog.categories;
const uiStore = useUiStore();
const FIXED_TOP_PX = 16;

const islandRef = ref(null);
const pinnedIslandRef = ref(null);
const rowRef = ref(null);
const pinnedRowRef = ref(null);
const isPinned = ref(false);
const pinnedStyle = ref({});

const isMobile = () => props.variant === "mobile";

const islandClasses = computed(() => {
    const dim =
        props.pinOnScroll && uiStore.chromeScrollDimmed
            ? dc.islandScrollDimmed
            : "";
    return [
        dc.island,
        dc.islandScrollDimTransition,
        dim,
        isMobile() ? dc.islandPaddingMobile : dc.islandPaddingDesktop,
    ];
});

const rowClasses = computed(() =>
    isMobile() ? dc.rowMobile : dc.rowDesktop,
);

/** Бар, по которому spy скроллит пилюли: fixed-копия или исходный. */
const activeBarEl = computed(() => {
    if (isPinned.value && pinnedIslandRef.value) {
        return pinnedIslandRef.value;
    }
    return islandRef.value;
});

function pillKey(value) {
    return value == null || value === "" ? "__all__" : String(value);
}

function pillClasses(forValue) {
    const active =
        props.modelValue == null && forValue == null
            ? true
            : props.modelValue != null &&
              forValue != null &&
              String(props.modelValue) === String(forValue);
    const mob = isMobile();
    const base = [dc.pillBase, mob ? dc.pillSizingMobile : dc.pillSizingDesktop];
    if (active) {
        base.push(mob ? dc.pillActiveMobile : dc.pillActiveDesktop);
    } else {
        base.push(dc.pillInactive);
    }
    return base;
}

const handleSelect = (value) => {
    emit("update:modelValue", value);
    emit("change", value);
};

function updatePinnedGeometry() {
    const el = islandRef.value;
    if (!el) return;
    const rect = el.getBoundingClientRect();
    pinnedStyle.value = {
        top: `${FIXED_TOP_PX}px`,
        left: `${Math.round(rect.left)}px`,
        width: `${Math.round(rect.width)}px`,
    };
}

function syncPinnedRowScroll() {
    if (!isMobile() || !rowRef.value || !pinnedRowRef.value) return;
    pinnedRowRef.value.scrollLeft = rowRef.value.scrollLeft;
}

function setPinned(next) {
    if (isPinned.value === next) {
        if (next) updatePinnedGeometry();
        return;
    }
    isPinned.value = next;
    if (next) {
        updatePinnedGeometry();
        nextTick(() => syncPinnedRowScroll());
    }
}

function evaluatePinFromRect(rect) {
    if (!props.pinOnScroll) {
        setPinned(false);
        return;
    }
    // Исходный остров ушёл выше линии fixed-top → показываем копию.
    setPinned(rect.top < FIXED_TOP_PX);
}

let pinObserver = null;

function disconnectPinObserver() {
    if (pinObserver) {
        pinObserver.disconnect();
        pinObserver = null;
    }
}

function connectPinObserver() {
    disconnectPinObserver();
    if (!props.pinOnScroll || typeof IntersectionObserver === "undefined") {
        setPinned(false);
        return;
    }
    const el = islandRef.value;
    if (!el) return;

    pinObserver = new IntersectionObserver(
        ([entry]) => {
            if (!entry) return;
            const rect = entry.boundingClientRect;
            evaluatePinFromRect(rect);
        },
        {
            root: null,
            // «Линия» на FIXED_TOP_PX от верха viewport.
            rootMargin: `-${FIXED_TOP_PX}px 0px 0px 0px`,
            threshold: [0, 1],
        },
    );
    pinObserver.observe(el);
    evaluatePinFromRect(el.getBoundingClientRect());
}

function onWindowResize() {
    if (!isPinned.value) return;
    updatePinnedGeometry();
}

watch(
    () => props.pinOnScroll,
    async () => {
        await nextTick();
        connectPinObserver();
    },
);

onMounted(async () => {
    await nextTick();
    connectPinObserver();
    window.addEventListener("resize", onWindowResize, { passive: true });
});

onBeforeUnmount(() => {
    disconnectPinObserver();
    window.removeEventListener("resize", onWindowResize);
});

defineExpose({
    islandEl: islandRef,
    pinnedIslandEl: pinnedIslandRef,
    isPinned,
    activeBarEl,
});
</script>

<template>
    <div :class="dc.outer">
        <div
            ref="islandRef"
            :class="islandClasses"
        >
            <div
                ref="rowRef"
                :class="rowClasses"
            >
                <button
                    v-if="showAll"
                    type="button"
                    :class="pillClasses(null)"
                    :data-catalog-category-pill="pillKey(null)"
                    @click="handleSelect(null)"
                >
                    {{ allLabel }}
                </button>

                <button
                    v-for="category in categories"
                    :key="category.id ?? category.uri"
                    type="button"
                    :class="pillClasses(category.id ?? category.uri)"
                    :data-catalog-category-pill="pillKey(category.id ?? category.uri)"
                    @click="handleSelect(category.id ?? category.uri)"
                >
                    {{ category.name }}
                </button>
            </div>
        </div>
    </div>

    <Teleport to="body">
        <div
            v-if="pinOnScroll && isPinned"
            ref="pinnedIslandRef"
            :class="[islandClasses, dc.islandFixed]"
            :style="pinnedStyle"
        >
            <div
                ref="pinnedRowRef"
                :class="rowClasses"
            >
                <button
                    v-if="showAll"
                    type="button"
                    :class="pillClasses(null)"
                    :data-catalog-category-pill="pillKey(null)"
                    @click="handleSelect(null)"
                >
                    {{ allLabel }}
                </button>

                <button
                    v-for="category in categories"
                    :key="`pinned-${category.id ?? category.uri}`"
                    type="button"
                    :class="pillClasses(category.id ?? category.uri)"
                    :data-catalog-category-pill="pillKey(category.id ?? category.uri)"
                    @click="handleSelect(category.id ?? category.uri)"
                >
                    {{ category.name }}
                </button>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.cats-scroll {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.cats-scroll::-webkit-scrollbar {
    width: 0;
    height: 0;
    display: none;
}
</style>
