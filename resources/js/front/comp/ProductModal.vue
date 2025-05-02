<script>
import gsap from "gsap";
import { mapStores } from "pinia";
import { appStore } from "../../stores/appStorage";
import { localStore } from "../../stores/localStore";

export default {
    props: {
        product: {
            type: Object,
            required: true,
        },
    },
    data: () => ({
        selectedSize: null,
        quantity: 1,
        activeImage: null,
    }),
    mounted() {
        this.activeImage = this.product.image;
        this.initializeAnimations();
    },
    computed: {
        ...mapStores(appStore, localStore),
        isInFavorites() {
            return this.localStore.fav.some(
                (item) => item.id === this.product.id
            );
        },
    },
    methods: {
        initializeAnimations() {
            gsap.from(".product-gallery", {
                x: -20,
                opacity: 0,
                duration: 0.5,
                ease: "power3.out",
            });
            gsap.from(".product-info", {
                x: 20,
                opacity: 0,
                duration: 0.5,
                delay: 0.2,
                ease: "power3.out",
            });
        },
        setActiveImage(image) {
            gsap.to(".main-image img", {
                opacity: 0,
                duration: 0.2,
                onComplete: () => {
                    this.activeImage = image;
                    gsap.to(".main-image img", {
                        opacity: 1,
                        duration: 0.2,
                    });
                },
            });
        },
        addToCart() {
            if (!this.selectedSize) {
                return;
            }

            const cartItem = {
                id: this.product.id,
                size: this.selectedSize,
                quantity: this.quantity,
                price: this.product.price,
                name: this.product.name,
                image: this.product.image,
            };

            this.localStore.addToCart(cartItem);
            this.appStore.modal = false;
        },
        toggleFavorite() {
            if (this.isInFavorites) {
                this.localStore.removeFromFav(this.product.id);
            } else {
                this.localStore.addToFav(this.product);
            }
        },
        incrementQuantity() {
            if (this.quantity < 10) this.quantity++;
        },
        decrementQuantity() {
            if (this.quantity > 1) this.quantity--;
        },
    },
};
</script>

<template>
    <div class="product-modal p-6">
        <div class="modal-header flex items-center justify-between mb-6">
            <h4 class="text-2xl font-semibold text-gray-800">
                {{ product.name }}
            </h4>
            <button
                class="close-button rounded-full p-2 hover:bg-gray-100 transition-colors"
                @click="appStore.modal = false"
            >
                <i class="fa fa-times text-gray-600"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="product-gallery space-y-4">
                <div
                    class="main-image aspect-square rounded-2xl overflow-hidden bg-gray-100"
                >
                    <img
                        :src="activeImage"
                        :alt="product.name"
                        class="w-full h-full object-cover"
                    />
                </div>
                <div
                    class="thumbnail-list grid grid-cols-4 gap-2"
                    v-if="product.gallery && product.gallery.length"
                >
                    <button
                        v-for="(image, index) in [
                            product.image,
                            ...product.gallery,
                        ]"
                        :key="index"
                        class="thumbnail aspect-square rounded-xl overflow-hidden bg-gray-100 hover:ring-2 ring-primary-500 transition-all"
                        :class="{ 'ring-2': activeImage === image }"
                        @click="setActiveImage(image)"
                    >
                        <img
                            :src="image"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                    </button>
                </div>
            </div>

            <div class="product-info space-y-6">
                <div class="price-block flex items-center gap-4">
                    <div
                        class="current-price text-2xl font-semibold text-primary-600"
                    >
                        {{ product.price }} ₽
                    </div>
                    <div
                        class="old-price text-lg text-gray-400 line-through"
                        v-if="product.oldPrice"
                    >
                        {{ product.oldPrice }} ₽
                    </div>
                </div>

                <div class="description text-gray-600 leading-relaxed">
                    {{ product.description }}
                </div>

                <div class="size-selector">
                    <h5 class="text-lg font-medium text-gray-800 mb-3">
                        Выберите размер
                    </h5>
                    <div class="size-list flex flex-wrap gap-2">
                        <button
                            v-for="size in product.sizes"
                            :key="size"
                            class="w-12 h-12 rounded-xl border-2 transition-all"
                            :class="
                                selectedSize === size
                                    ? 'border-primary-500 bg-primary-500 text-white'
                                    : 'border-gray-200 hover:border-primary-500'
                            "
                            @click="selectedSize = size"
                        >
                            {{ size }}
                        </button>
                    </div>
                </div>

                <div class="quantity-selector">
                    <h5 class="text-lg font-medium text-gray-800 mb-3">
                        Количество
                    </h5>
                    <div
                        class="quantity-controls inline-flex items-center rounded-xl bg-gray-100 p-1"
                    >
                        <button
                            class="w-10 h-10 rounded-lg hover:bg-white hover:shadow-sm transition-all"
                            @click="decrementQuantity"
                        >
                            <i class="fa fa-minus text-gray-600"></i>
                        </button>
                        <span
                            class="w-10 h-10 flex items-center justify-center font-medium"
                        >
                            {{ quantity }}
                        </span>
                        <button
                            class="w-10 h-10 rounded-lg hover:bg-white hover:shadow-sm transition-all"
                            @click="incrementQuantity"
                        >
                            <i class="fa fa-plus text-gray-600"></i>
                        </button>
                    </div>
                </div>

                <div class="actions flex gap-4">
                    <button
                        class="flex-1 h-14 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors"
                        @click="addToCart"
                    >
                        Добавить в корзину
                    </button>
                    <button
                        class="w-14 h-14 rounded-xl transition-all"
                        :class="
                            isInFavorites
                                ? 'bg-red-500 text-white hover:bg-red-600'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                        "
                        @click="toggleFavorite"
                    >
                        <i
                            class="fa"
                            :class="isInFavorites ? 'fa-heart' : 'fa-heart-o'"
                        ></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="sass" scoped>
.product-modal
    max-height: calc(100vh - 4rem)
    overflow-y: auto

.product-gallery, .product-info
    will-change: transform, opacity
</style>
