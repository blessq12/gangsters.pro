<script>
import { mapStores } from "pinia";
import { appStore } from "../../stores/appStorage";
import { localStore } from "../../stores/localStore";

export default {
    data() {
        return {
            thumbs: this.product["thumbnails"],
            images: this.product["images"],
        };
    },
    computed: {
        ...mapStores(localStore, appStore),

        getThumbs() {
            if (this.thumbs)
                return this.thumbs.length > 0 && this.thumbs !== undefined
                    ? this.thumbs[0]["small"]
                    : "/images/placeholder/product-image-empty-512x512.jpg";
        },
    },
    props: {
        product: {
            type: Object,
            required: true,
        },
    },
};
</script>

<template>
    <div class="product-card group">
        <div class="relative aspect-square rounded-xl overflow-hidden mb-4">
            <img
                :src="getThumbs"
                :alt="product.name"
                class="w-full h-full object-cover transition-all duration-300 group-hover:scale-105"
                @click="appStore.currentAdditional = product"
                data-bs-toggle="modal"
                data-bs-target="#additional"
            />

            <!-- Badges -->
            <div class="absolute top-3 left-3 z-10 flex flex-wrap gap-1.5">
                <span
                    v-if="product.hit"
                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-white bg-red-500 rounded"
                >
                    <i class="mdi mdi-fire mr-1"></i>
                    Хит
                </span>
                <span
                    v-if="product.spicy"
                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-white bg-amber-500 rounded"
                >
                    <i class="mdi mdi-pepper-hot mr-1"></i>
                    Острый
                </span>
                <span
                    v-if="product.kidsAllow"
                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-white bg-emerald-500 rounded"
                >
                    <i class="mdi mdi-baby-face-outline mr-1"></i>
                    Для детей
                </span>
                <span
                    v-if="product.onion"
                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-white bg-purple-500 rounded"
                >
                    <i class="mdi mdi-sprout mr-1"></i>
                    С луком
                </span>
                <span
                    v-if="product.garlic"
                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-white bg-indigo-500 rounded"
                >
                    <i class="mdi mdi-flower-tulip mr-1"></i>
                    С чесноком
                </span>
            </div>

            <!-- Favorite Button -->
            <button
                class="favorite-btn"
                @click="localStore.manageStore('fav', product)"
            >
                <transition
                    enter-active-class="animate__animated animate__faster animate__bounceIn"
                    leave-active-class="animate__animated animate__faster animate__bounceOut"
                >
                    <i
                        class="mdi"
                        :class="[
                            localStore.checkExist('fav', product)
                                ? 'mdi-heart text-red-500'
                                : 'mdi-heart-outline text-neutral-600',
                        ]"
                    ></i>
                </transition>
            </button>

            <!-- Quantity Badge -->
            <transition
                enter-active-class="animate__animated animate__faster animate__zoomIn"
                leave-active-class="animate__animated animate__faster animate__zoomOut"
            >
                <div
                    v-if="localStore.checkExist('cart', product)"
                    class="quantity-badge"
                >
                    <span class="text-2xl font-bold">{{
                        localStore.getQty(product)
                    }}</span>
                </div>
            </transition>
        </div>

        <div class="px-4 pb-4 space-y-3">
            <h3
                class="text-lg font-semibold text-neutral-900 leading-tight group-hover:text-neutral-700 transition-colors"
            >
                {{ product.name }}
            </h3>

            <div class="flex items-end justify-between">
                <div>
                    <div class="text-2xl font-bold text-neutral-900">
                        {{ product.price }} ₽
                    </div>
                    <div class="text-sm text-neutral-500 flex items-center">
                        <i class="mdi mdi-scale mr-1"></i>
                        {{ product.weight }} г
                    </div>
                </div>

                <transition
                    enter-active-class="animate__animated animate__faster animate__fadeIn"
                    leave-active-class="animate__animated animate__faster animate__fadeOut"
                >
                    <button
                        v-if="!localStore.checkExist('cart', product)"
                        class="btn-add-to-cart"
                        @click="localStore.manageStore('cart', product)"
                    >
                        <i class="mdi mdi-cart-plus mr-2"></i>
                        В корзину
                    </button>
                    <div v-else class="quantity-controls">
                        <button
                            class="quantity-btn"
                            @click="localStore.manageQty(false, product)"
                        >
                            <i class="mdi mdi-minus"></i>
                        </button>
                        <button
                            class="quantity-btn"
                            @click="localStore.manageQty(true, product)"
                        >
                            <i class="mdi mdi-plus"></i>
                        </button>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.product-card {
    @apply bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300;
}

.favorite-btn {
    @apply absolute top-3 right-3 w-10 h-10 rounded-full bg-white/90
    backdrop-blur-sm shadow-sm flex items-center justify-center
    transition-all duration-300 hover:bg-white hover:scale-110;

    i {
        @apply text-2xl transition-colors duration-300;
    }
}

.quantity-badge {
    @apply absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2
    bg-black/80 backdrop-blur-sm rounded-full w-16 h-16
    flex items-center justify-center shadow-lg text-white;
}

.btn-add-to-cart {
    @apply px-4 py-2.5 bg-gradient-to-r from-neutral-900 to-neutral-800
    text-white text-sm font-medium rounded-xl
    transition-all duration-300 hover:from-neutral-800 hover:to-neutral-700
    flex items-center shadow-sm hover:shadow hover:scale-105;
}

.quantity-controls {
    @apply flex gap-2;
}

.quantity-btn {
    @apply w-10 h-10 flex items-center justify-center
    bg-gradient-to-r from-neutral-100 to-neutral-50
    text-neutral-900 rounded-xl transition-all duration-300
    hover:from-neutral-200 hover:to-neutral-100 hover:scale-105
    shadow-sm hover:shadow;

    i {
        @apply text-xl;
    }
}
</style>
