/**
 * Числа КБЖУ для отображения (карточка, модалка).
 * @returns {{ calories: number, proteins: number, fats: number, carbs: number } | null}
 */
export function getProductNutritionNumbers(product) {
    const n = product?.nutrition ?? product?.raw?.nutrition;
    if (!n || typeof n !== "object") return null;
    return {
        calories: Number(n.calories) || 0,
        proteins: Number(n.proteins) || 0,
        fats: Number(n.fats) || 0,
        carbs: Number(n.carbs) || 0,
    };
}

export function hasProductNutrition(product) {
    const n = getProductNutritionNumbers(product);
    return Boolean(
        n && (n.calories || n.proteins || n.fats || n.carbs),
    );
}
