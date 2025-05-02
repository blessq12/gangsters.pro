<script>
import { mapStores } from "pinia";
import { localStore } from "../../stores/localStore";

export default {
    props: {
        product: Object,
        isFavorite: Boolean,
    },
    data() {
        return {
            //
        };
    },
    mounted() {
        //
    },
    methods: {
        //
    },
    computed: {
        ...mapStores(localStore),
        getThumbs() {
            if (
                this.product.thumbnails &&
                this.product.thumbnails.length &&
                this.product.thumbnails !== undefined
            ) {
                return this.product.thumbnails[0].small;
            }
            return "/images/placeholder/product-image-empty-128x128.jpg";
        },
    },
};
</script>

<template>
    <div class="flex items-center mb-2">
        <div
            class="min-h-[80px] min-w-[80px] lg:min-h-[120px] lg:min-w-[120px] mr-2 lg:mr-3 bg-image position-center bg-cover rounded overflow-hidden relative"
            v-lazy:background-image="getThumbs"
            @error="
                $event.target.style.backgroundImage =
                    'url(/images/placeholder/product-image-empty-128x128.jpg)'
            "
        >
            <div
                class="absolute inset-0 bg-black/20 flex items-center justify-center"
                v-if="!isFavorite"
            >
                <span
                    class="text-3xl lg:text-5xl font-medium lg:font-bold text-white"
                    >{{ product.qty }}</span
                >
            </div>
        </div>
        <div class="content">
            <span
                class="block text-xs lg:text-lg font-medium lg:font-semibold"
                >{{ product.name }}</span
            >
            <p class="mb-1 text-sm"><b>Цена: </b>{{ product.price }} руб.</p>
            <div v-if="!isFavorite">
                <div
                    class="flex items-center justify-start overflow-hidden w-fit rounded"
                >
                    <div class="flex items-center gap-2 py-2">
                        <button
                            @click="localStore.manageQty(false, product)"
                            class="cursor-pointer bg-red-200 hover:bg-red-300 rounded-xl py-1 px-3 md:py-2 md:px-4 transition-colors duration-200 font-semibold shadow-md"
                        >
                            <i class="mdi mdi-minus text-red-700"></i>
                        </button>
                        <button
                            @click="localStore.manageQty(true, product)"
                            class="cursor-pointer bg-green-200 hover:bg-green-300 rounded-xl py-1 px-3 md:py-2 md:px-4 transition-colors duration-200 font-semibold shadow-md"
                        >
                            <i class="mdi mdi-plus text-green-700"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="flex items-center gap-2 py-2">
                    <transition
                        enter-active-class="animate__animated animate__faster animate__fadeIn"
                        leave-active-class="animate__animated animate__faster animate__fadeOut"
                        mode="out-in"
                    >
                        <button
                            class="cursor-pointer bg-red-200 hover:bg-red-300 rounded-xl py-1 px-3 md:py-2 md:px-4 transition-colors duration-200 font-semibold shadow-md"
                            @click="localStore.manageStore('cart', product)"
                            v-if="localStore.checkExist('cart', product)"
                        >
                            <i class="mdi mdi-cart-remove text-red-700"></i>
                        </button>
                        <button
                            class="cursor-pointer bg-green-200 hover:bg-green-300 rounded-xl py-1 px-3 md:py-2 md:px-4 transition-colors duration-200 font-semibold shadow-md"
                            @click="localStore.manageStore('cart', product)"
                            v-else
                        >
                            <i class="mdi mdi-cart-plus text-green-700"></i>
                        </button>
                    </transition>
                    <button
                        class="cursor-pointer bg-red-200 hover:bg-red-300 rounded-xl py-1 px-3 md:py-2 md:px-4 transition-colors duration-200 font-semibold shadow-md hover:shadow-lg"
                        @click="localStore.manageStore('fav', product)"
                    >
                        <i class="mdi mdi-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
