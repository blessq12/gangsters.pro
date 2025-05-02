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
            <div class="absolute top-3 left-3 z-[10] flex flex-wrap gap-1.5">
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

            <button
                class="favorite-btn"
                @click="localStore.manageStore('fav', product)"
            >
                <i
                    class="mdi mdi-clock-outline"
                    :class="[
                        localStore.checkExist('fav', product)
                            ? 'mdi-heart text-red-500'
                            : 'mdi-heart-outline text-neutral-600',
                    ]"
                ></i>
            </button>

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
                class="text-sm md:text-base font-medium text-neutral-900 leading-tight group-hover:text-neutral-700"
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

                <div class="flex gap-4">
                    <button
                        v-if="!localStore.checkExist('cart', product)"
                        class="btn-add-to-cart"
                        @click="localStore.manageStore('cart', product)"
                    >
                        <i class="mdi mdi-cart-plus text-2xl"></i>
                    </button>
                    <button
                        v-if="localStore.checkExist('cart', product)"
                        class="btn-add-to-cart"
                        @click="localStore.manageStore('cart', product)"
                    >
                        <i
                            class="mdi mdi-cart-plus text-2xl text-green-500"
                        ></i>
                    </button>

                    <button
                        v-if="!localStore.checkExist('fav', product)"
                        class="btn-add-to-cart"
                        @click="localStore.manageStore('fav', product)"
                    >
                        <i class="mdi mdi-heart text-2xl"></i>
                    </button>
                    <button
                        v-if="localStore.checkExist('fav', product)"
                        class="btn-add-to-cart"
                        @click="localStore.manageStore('fav', product)"
                    >
                        <i class="mdi mdi-heart text-2xl text-red-500"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.product-card {
    @apply bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300;
}

.favorite-btn {
    @apply absolute top-2 right-2 sm:top-3 sm:right-3 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/90
    backdrop-blur-sm shadow-sm flex items-center justify-center
    transition-all duration-300 hover:bg-white hover:scale-110;

    i {
        @apply text-xl sm:text-2xl transition-colors duration-300;
    }
}

.quantity-badge {
    @apply absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2
    bg-black/80 backdrop-blur-sm rounded-full w-12 h-12 sm:w-16 sm:h-16
    flex items-center justify-center shadow-lg text-white;
}

.btn-add-to-cart {
    @apply px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-neutral-900 to-neutral-800
    text-white text-xs sm:text-sm font-medium rounded-xl
    transition-all duration-300 hover:from-neutral-800 hover:to-neutral-700
    flex items-center shadow-sm hover:shadow hover:scale-105;

    i {
        @apply mr-1 sm:mr-2;
    }
}

.quantity-controls {
    @apply flex gap-1.5 sm:gap-2;
}

.quantity-btn {
    @apply w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center
    bg-gradient-to-r from-neutral-100 to-neutral-50
    text-neutral-900 rounded-xl transition-all duration-300
    hover:from-neutral-200 hover:to-neutral-100 hover:scale-105
    shadow-sm hover:shadow;

    i {
        @apply text-lg sm:text-xl;
    }
}
</style>
