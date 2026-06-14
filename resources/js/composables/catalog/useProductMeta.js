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
            .map((item) => {
                if (typeof item === "string") {
                    const name = item.trim();
                    return name ? { name, amount: null, unit: "", isAllergen: false } : null;
                }

                if (!item || (!item.name && !item.amount)) return null;

                return {
                    name: item.name || "",
                    amount: item.amount,
                    unit: item.unit || "",
                    isAllergen: Boolean(item.is_allergen),
                };
            })
            .filter(Boolean);
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

