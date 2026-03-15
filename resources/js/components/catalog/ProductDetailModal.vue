<script setup>
import { computed } from "vue";
import BaseModal from "../ui/BaseModal.vue";
import { useUserStore } from "../../stores/userStore";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    product: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:modelValue"]);

const userStore = useUserStore();

const primaryImage = computed(() => {
    const p = props.product;
    if (!p) return null;
    if (Array.isArray(p.images) && p.images.length) return p.images[0];
    return null;
});

const productId = computed(() => props.product?.id ?? null);

const qtyInCart = computed(() =>
    productId.value ? userStore.cartQuantityByProduct(productId.value) : 0,
);

const handleAddToCart = () => {
    if (!productId.value) return;
    userStore.addToCart(props.product, 1);
};

const handleIncrement = () => {
    if (!productId.value) return;
    userStore.incrementCart(productId.value);
};

const handleDecrement = () => {
    if (!productId.value) return;
    userStore.decrementCart(productId.value);
};
</script>

<template>
    <BaseModal :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
        <template v-if="product" #header>
            {{ product.name }}
        </template>

        <template v-if="product">
            <div class="space-y-4">
                <div
                    class="aspect-[4/3] w-full overflow-hidden rounded-xl bg-slate-800/50"
                >
                    <img
                        v-if="primaryImage"
                        :src="primaryImage"
                        :alt="product.name"
                        class="h-full w-full object-cover object-center"
                    />
                    <div
                        v-else
                        class="flex h-full items-center justify-center text-sm text-slate-500"
                    >
                        Нет фото
                    </div>
                </div>

                <p
                    v-if="product.consist"
                    class="text-slate-300 leading-relaxed"
                >
                    {{ product.consist }}
                </p>

                <div class="flex items-center justify-between gap-4 border-t border-white/10 pt-4">
                    <span class="text-lg font-semibold text-amber-400">
                        {{ product.price }} ₽
                    </span>
                    <div v-if="qtyInCart === 0" class="flex-1 max-w-[200px]">
                        <button
                            type="button"
                            class="w-full rounded-full bg-amber-400 px-4 py-2.5 text-sm font-semibold text-black transition-colors hover:bg-amber-300"
                            @click="handleAddToCart"
                        >
                            В корзину
                        </button>
                    </div>
                    <div
                        v-else
                        class="inline-flex items-center gap-2 rounded-full border border-amber-400/60 bg-black/50 px-3 py-2"
                    >
                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-slate-200 hover:bg-white/10"
                            @click="handleDecrement"
                        >
                            –
                        </button>
                        <span class="min-w-[2ch] text-center font-semibold text-slate-100">
                            {{ qtyInCart }} шт
                        </span>
                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-slate-200 hover:bg-white/10"
                            @click="handleIncrement"
                        >
                            +
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <template v-else>
            <p class="text-slate-400">Нет данных о товаре.</p>
        </template>
    </BaseModal>
</template>
