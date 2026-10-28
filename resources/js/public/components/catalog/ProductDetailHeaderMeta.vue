<script setup>
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
} from "vue";
import { playTooltipClose, playTooltipOpen } from "../../animations/animationManager";
import { useCatalogItemDisplay } from "../../modules/catalog/application/models";
import { useProductMeta } from "../../modules/catalog/application/models";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    product: {
        type: Object,
        default: null,
    },
});

const di = useAppDesign().components.catalog.modal.detailInfo;

const productRef = computed(() => props.product);

const { nutrition, hasNutrition, ingredients, ingredientsText } =
    useProductMeta(productRef);
const {
    isProduct,
    isSet,
    setLines,
    setCountLabel,
    hasSetComposition,
} = useCatalogItemDisplay(productRef);

const showMetaRow = computed(
    () =>
        (isProduct.value &&
            (hasNutrition.value || ingredients.value.length > 0)) ||
        (isSet.value && (hasSetComposition.value || setCountLabel.value)),
);

const activeTooltip = ref(null); // 'nutrition' | 'ingredients' | 'composition' | null
const tooltipPosition = ref({ left: 0, top: 0 });
const tooltipRef = ref(null);
const nutritionLinkRef = ref(null);
const ingredientsLinkRef = ref(null);
const compositionLinkRef = ref(null);

const tooltipWidthClass = computed(() => {
    if (
        activeTooltip.value === "ingredients" ||
        activeTooltip.value === "composition"
    ) {
        return di.tooltipWide;
    }
    return di.tooltipNarrow;
});

function getAnchorEl(type) {
    if (type === "nutrition") return nutritionLinkRef.value;
    if (type === "ingredients") return ingredientsLinkRef.value;
    if (type === "composition") return compositionLinkRef.value;
    return null;
}

function closeTooltip() {
    if (!activeTooltip.value) return;
    const currentEl = tooltipRef.value;
    if (!currentEl) {
        activeTooltip.value = null;
        return;
    }
    playTooltipClose(currentEl, () => {
        activeTooltip.value = null;
    });
}

function computeTooltipPosition() {
    const type = activeTooltip.value;
    if (!type) return;

    const anchorEl = getAnchorEl(type);
    const anchorRect = anchorEl?.getBoundingClientRect();
    const tipRect = tooltipRef.value?.getBoundingClientRect();

    if (!anchorRect || !tipRect) return;

    const margin = 8;

    let left = anchorRect.left;
    left = Math.max(
        margin,
        Math.min(left, window.innerWidth - tipRect.width - margin),
    );

    let top = anchorRect.bottom + margin;
    top = Math.min(top, window.innerHeight - tipRect.height - margin);

    tooltipPosition.value = { left, top };
}

async function openTooltip(type) {
    if (type === "nutrition" && !hasNutrition.value) return;
    if (type === "ingredients" && !ingredients.value.length) return;
    if (type === "composition" && !hasSetComposition.value) return;

    activeTooltip.value = type;
    await nextTick();
    computeTooltipPosition();
    playTooltipOpen(tooltipRef.value);
}

function toggleNutritionTooltip() {
    if (activeTooltip.value === "nutrition") {
        closeTooltip();
        return;
    }
    openTooltip("nutrition");
}

function toggleIngredientsTooltip() {
    if (activeTooltip.value === "ingredients") {
        closeTooltip();
        return;
    }
    openTooltip("ingredients");
}

function toggleCompositionTooltip() {
    if (activeTooltip.value === "composition") {
        closeTooltip();
        return;
    }
    openTooltip("composition");
}

let outsideClickHandler = null;
onMounted(() => {
    outsideClickHandler = (e) => {
        if (!activeTooltip.value) return;

        const tipEl = tooltipRef.value;
        const nutritionEl = nutritionLinkRef.value;
        const ingredientsEl = ingredientsLinkRef.value;
        const compositionEl = compositionLinkRef.value;

        if (
            tipEl?.contains(e.target) ||
            nutritionEl?.contains(e.target) ||
            ingredientsEl?.contains(e.target) ||
            compositionEl?.contains(e.target)
        ) {
            return;
        }

        closeTooltip();
    };

    document.addEventListener("click", outsideClickHandler);
});

onUnmounted(() => {
    if (outsideClickHandler) {
        document.removeEventListener("click", outsideClickHandler);
    }
    closeTooltip();
});
</script>

<template>
    <div
        v-if="showMetaRow"
        :class="di.metaLinksRow"
    >
        <button
            v-if="hasNutrition && isProduct"
            type="button"
            ref="nutritionLinkRef"
            :class="[
                di.metaLink,
                activeTooltip === 'nutrition' ? di.metaLinkActive : '',
            ]"
            @click.stop="toggleNutritionTooltip"
        >
            КБЖУ
        </button>
        <button
            v-if="ingredients.length && isProduct"
            type="button"
            ref="ingredientsLinkRef"
            :class="[
                di.metaLink,
                activeTooltip === 'ingredients' ? di.metaLinkActive : '',
            ]"
            @click.stop="toggleIngredientsTooltip"
        >
            Состав
        </button>
        <button
            v-if="hasSetComposition"
            type="button"
            ref="compositionLinkRef"
            :class="[
                di.metaLink,
                activeTooltip === 'composition' ? di.metaLinkActive : '',
            ]"
            @click.stop="toggleCompositionTooltip"
        >
            Состав набора
        </button>
        <span
            v-if="isSet && setCountLabel"
            :class="di.metaLinkMuted"
        >
            {{ setCountLabel }}
        </span>
    </div>

    <Teleport to="body">
        <div
            v-if="activeTooltip"
            ref="tooltipRef"
            :class="[di.teleportTooltipBase, tooltipWidthClass]"
            :style="{
                left: `${tooltipPosition.left}px`,
                top: `${tooltipPosition.top}px`,
            }"
            role="dialog"
        >
            <template v-if="activeTooltip === 'nutrition'">
                <div :class="di.nutritionBlock">
                    <div :class="di.nutritionRow">
                        <span :class="di.nutritionLabel">Калории</span>
                        <span :class="di.nutritionVal">
                            {{ nutrition.calories }} ккал
                        </span>
                    </div>
                    <div :class="di.nutritionRow">
                        <span :class="di.nutritionLabel">Белки</span>
                        <span :class="di.nutritionVal">
                            {{ nutrition.proteins }} г
                        </span>
                    </div>
                    <div :class="di.nutritionRow">
                        <span :class="di.nutritionLabel">Жиры</span>
                        <span :class="di.nutritionVal">
                            {{ nutrition.fats }} г
                        </span>
                    </div>
                    <div :class="di.nutritionRow">
                        <span :class="di.nutritionLabel">Углеводы</span>
                        <span :class="di.nutritionVal">
                            {{ nutrition.carbs }} г
                        </span>
                    </div>
                </div>
            </template>

            <template v-else-if="activeTooltip === 'ingredients'">
                <div :class="di.ingredientsBlock">
                    <div :class="di.ingredientsHeading">
                        Состав
                    </div>
                    <div :class="di.ingredientsBody">
                        {{ ingredientsText }}
                    </div>
                </div>
            </template>

            <template v-else-if="activeTooltip === 'composition'">
                <div :class="di.compositionBlock">
                    <div :class="di.compositionHeading">
                        Состав набора
                    </div>
                    <div
                        v-for="line in setLines"
                        :key="line.productId"
                        :class="di.compositionRow"
                    >
                        <span :class="di.compositionName">
                            {{ line.productName }}
                        </span>
                        <span :class="di.compositionQty">
                            ×{{ line.quantity }}
                        </span>
                    </div>
                </div>
            </template>
        </div>
    </Teleport>
</template>
