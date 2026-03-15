<script setup>
import { computed } from "vue";

const props = defineProps({
    product: {
        type: Object,
        default: null,
    },
    qtyInCart: {
        type: Number,
        default: 0,
    },
    isFav: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["add-to-cart", "increment", "decrement", "toggle-favorite"]);

const nutrition = computed(() => {
    const n = props.product?.nutrition ?? props.product?.raw?.nutrition;
    if (!n || typeof n !== "object") return null;
    return {
        calories: Number(n.calories) || 0,
        proteins: Number(n.proteins) || 0,
        fats: Number(n.fats) || 0,
        carbs: Number(n.carbs) || 0,
    };
});

const hasNutrition = computed(() => {
    const n = nutrition.value;
    return n && (n.calories || n.proteins || n.fats || n.carbs);
});

const ingredients = computed(() => {
    const raw = props.product?.raw?.ingredients;
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

const tags = computed(() => {
    const raw = props.product?.raw?.tags;
    if (!Array.isArray(raw)) return [];
    return raw.map((t) => t?.code).filter(Boolean);
});

function handleAddToCart() {
    emit("add-to-cart");
}

function handleIncrement() {
    emit("increment");
}

function handleDecrement() {
    emit("decrement");
}

function handleToggleFavorite() {
    emit("toggle-favorite");
}
</script>

<template>
    <div v-if="product" class="product-detail-info">
        <!-- Островок как у карточки: рамка, фон, скругление -->
        <div class="product-detail-info__card">
            <div class="product-detail-info__head">
                <div class="product-detail-info__title-wrap">
                    <h2 class="product-detail-info__title">
                        {{ product.name || product.raw?.name || 'Без названия' }}
                    </h2>
                    <p
                        v-if="product.consist"
                        class="product-detail-info__desc"
                    >
                        {{ product.consist }}
                    </p>
                </div>
                <button
                    type="button"
                    class="product-detail-info__fav"
                    :class="{ 'product-detail-info__fav--active': isFav }"
                    aria-label="Избранное"
                    @click="handleToggleFavorite"
                >
                    <i :class="['mdi', isFav ? 'mdi-heart' : 'mdi-heart-outline']" />
                </button>
            </div>

            <div v-if="product.weight" class="product-detail-info__weight">
                {{ product.weight }} г
            </div>

            <div v-if="hasNutrition" class="product-detail-info__nutrition">
                <p class="product-detail-info__nutrition-title">Пищевая ценность на 100 г</p>
                <div class="product-detail-info__nutrition-grid">
                    <span class="product-detail-info__nutrition-label">Калории</span>
                    <span class="product-detail-info__nutrition-value">{{ nutrition.calories }} ккал</span>
                    <span class="product-detail-info__nutrition-label">Белки</span>
                    <span class="product-detail-info__nutrition-value">{{ nutrition.proteins }} г</span>
                    <span class="product-detail-info__nutrition-label">Жиры</span>
                    <span class="product-detail-info__nutrition-value">{{ nutrition.fats }} г</span>
                    <span class="product-detail-info__nutrition-label">Углеводы</span>
                    <span class="product-detail-info__nutrition-value">{{ nutrition.carbs }} г</span>
                </div>
            </div>

            <div v-if="ingredients.length" class="product-detail-info__ingredients">
                <p class="product-detail-info__ingredients-title">Состав</p>
                <ul class="product-detail-info__ingredients-list">
                    <li
                        v-for="(ing, idx) in ingredients"
                        :key="idx"
                        class="product-detail-info__ingredient"
                        :class="{ 'product-detail-info__ingredient--allergen': ing.isAllergen }"
                    >
                        <span>{{ ing.name }}</span>
                        <span v-if="ing.amount" class="product-detail-info__ingredient-amount">
                            {{ ing.amount }}{{ ing.unit }}
                        </span>
                    </li>
                </ul>
            </div>

            <div v-if="tags.length" class="product-detail-info__tags">
                <span
                    v-for="code in tags"
                    :key="code"
                    class="product-detail-info__tag"
                >
                    {{ code }}
                </span>
            </div>

            <div class="product-detail-info__actions">
                <span class="product-detail-info__price">
                    {{ product.price ?? 0 }} ₽
                </span>
                <div v-if="qtyInCart === 0" class="product-detail-info__cart-wrap">
                    <button
                        type="button"
                        class="product-detail-info__btn-cart"
                        @click="handleAddToCart"
                    >
                        В корзину
                    </button>
                </div>
                <div v-else class="product-detail-info__qty">
                    <button
                        type="button"
                        class="product-detail-info__qty-btn"
                        @click="handleDecrement"
                    >
                        –
                    </button>
                    <span class="product-detail-info__qty-num">{{ qtyInCart }} шт</span>
                    <button
                        type="button"
                        class="product-detail-info__qty-btn"
                        @click="handleIncrement"
                    >
                        +
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.product-detail-info {
    padding: 0;
    height: 100%;
    max-height: 100%;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.product-detail-info__card {
    border-radius: 1rem;
    border: 1px solid rgba(251, 191, 36, 0.3);
    background: rgba(0, 0, 0, 0.75);
    padding: 0.75rem 1rem;
    backdrop-blur: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.9);
    max-height: 100%;
    overflow-y: auto;
}

.product-detail-info__head {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.product-detail-info__title-wrap {
    min-width: 0;
    flex: 1;
}

.product-detail-info__title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.25;
    color: #f8fafc;
}

.product-detail-info__desc {
    margin: 0.25rem 0 0;
    font-size: 0.8125rem;
    line-height: 1.35;
    color: rgba(203, 213, 225, 0.85);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-detail-info__fav {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(0, 0, 0, 0.6);
    color: #e2e8f0;
    font-size: 1.25rem;
    transition: border-color 0.2s, color 0.2s;
}

.product-detail-info__fav:hover {
    border-color: rgba(251, 191, 36, 0.6);
    color: #fcd34d;
}

.product-detail-info__fav--active {
    border-color: #fcd34d;
    color: #fcd34d;
}

.product-detail-info__weight {
    display: inline-flex;
    align-self: flex-start;
    margin-bottom: 0.5rem;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.75);
    font-size: 0.75rem;
    font-weight: 500;
    color: #f1f5f9;
}

.product-detail-info__nutrition {
    margin-bottom: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.03);
}

.product-detail-info__nutrition-title {
    margin: 0 0 0.35rem;
    font-size: 0.65rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
}

.product-detail-info__nutrition-grid {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.25rem 1.5rem;
    font-size: 0.8125rem;
}

.product-detail-info__nutrition-label {
    color: #94a3b8;
}

.product-detail-info__nutrition-value {
    color: #f8fafc;
    font-weight: 500;
}

.product-detail-info__ingredients {
    margin-bottom: 0.5rem;
}

.product-detail-info__ingredients-title {
    margin: 0 0 0.5rem;
    font-size: 0.7rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
}

.product-detail-info__ingredients-list {
    margin: 0;
    padding-left: 1.25rem;
    font-size: 0.8125rem;
    line-height: 1.6;
    color: #cbd5e1;
}

.product-detail-info__ingredient {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
}

.product-detail-info__ingredient--allergen {
    color: #fcd34d;
}

.product-detail-info__ingredient-amount {
    color: #94a3b8;
    flex-shrink: 0;
}

.product-detail-info__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-bottom: 0.5rem;
}

.product-detail-info__tag {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    border: 1px solid rgba(251, 191, 36, 0.3);
    font-size: 0.6875rem;
    color: #fcd34d;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.product-detail-info__actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    padding-top: 0.5rem;
    margin-top: 0.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.product-detail-info__price {
    font-size: 1rem;
    font-weight: 600;
    color: #fcd34d;
    text-shadow: 0 0 12px rgba(251, 191, 36, 0.5);
}

.product-detail-info__cart-wrap {
    flex: 1;
    min-width: 0;
    max-width: 14rem;
}

.product-detail-info__btn-cart {
    width: 100%;
    min-height: 2.5rem;
    padding: 0.4rem 1rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    background: #fcd34d;
    font-weight: 600;
    color: #000;
    box-shadow: 0 0 12px rgba(251, 191, 36, 0.45);
    transition: background-color 0.2s, transform 0.15s;
}

.product-detail-info__btn-cart:hover {
    background: #fde68a;
    transform: scale(1.02);
}

.product-detail-info__qty {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    border: 1px solid rgba(251, 191, 36, 0.6);
    background: rgba(0, 0, 0, 0.7);
}

.product-detail-info__qty-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.5);
    color: #e2e8f0;
    font-size: 1rem;
    transition: background-color 0.2s;
}

.product-detail-info__qty-btn:hover {
    background: rgba(255, 255, 255, 0.1);
}

.product-detail-info__qty-num {
    min-width: 2.5ch;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 600;
    color: #f8fafc;
}
</style>
