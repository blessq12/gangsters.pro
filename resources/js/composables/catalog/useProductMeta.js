import { computed, unref } from "vue";
import {
    getProductNutritionNumbers,
    hasProductNutrition,
} from "../../utils/catalog/productNutrition";

export function useProductMeta(productSource) {
    const product = computed(() => unref(productSource) ?? null);

    const nutrition = computed(() => getProductNutritionNumbers(product.value));
    const hasNutrition = computed(() => hasProductNutrition(product.value));

    const ingredients = computed(() => {
        const raw = product.value?.raw?.ingredients;
        if (!Array.isArray(raw)) return [];
        return raw
            .filter((i) => i && (i.name || i.amount))
            .map((i) => ({
                name: i.name || "",
                amount: i.amount,
                unit: i.unit || "",
                isAllergen: Boolean(i.is_allergen),
            }));
    });

    const hasIngredients = computed(() => ingredients.value.length > 0);

    const ingredientsText = computed(() =>
        ingredients.value
            .map((i) => i?.name)
            .filter(Boolean)
            .join(", "),
    );

    return {
        nutrition,
        hasNutrition,
        ingredients,
        hasIngredients,
        ingredientsText,
    };
}

