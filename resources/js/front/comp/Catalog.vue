<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage';
import { localStore } from '../../stores/localStore';
export default {
    props: {
        goods: Object
    },
    computed: {
        ...mapStores( appStore, localStore )
    },
    methods: {
        showImage(prod) {
            if (prod.images.length) {
                return prod.thumbs[0].medium
            } else {
                return 'https://via.placeholder.com/512x512'
            }
        }
    }
}
</script>

<template>
    <div class="category" v-for="category in goods" :key="category.uri">
        <div class="row mb-4">
            <div class="col">
                <div class="section-title">
                    <h2>
                        {{ category.name }}
                    </h2>
                </div>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-3 mb-4 category-list">
            <div class="col rounded" v-for="product in category.products" :key="product.id">
                <div class="product">
                    <div class="header bg-image rounded" :style="'background: url('+ showImage(product) +')'">
                        <div class="badge">
                            <button type="button" class="btn btn-light" @click="localStore.manageStore('fav', product)" v-if="!localStore.checkExist('fav', product)">
                                <i class="fa fa-heart-o"></i>
                            </button>
                            <button type="button" class="btn btn-danger" @click="localStore.manageStore('fav', product)" v-else>
                                <i class="fa fa-heart"></i>
                            </button>
                            <button 
                                type="button" 
                                class="additional btn"
                                @click="appStore.additional = !appStore.additional; appStore.currentAdditional = product"
                            >i</button>
                        </div>
                    </div>
                    <div class="content">
                        <span>{{ product.name }}</span>
                    </div>
                    <div class="footer">

                        <button type="button" class="btn rounded" @click="localStore.manageStore('cart', product)" v-if="!localStore.checkExist('cart', product)">
                            + В корзину
                        </button>
                        <button type="button" class="btn rounded active" @click="localStore.manageStore('cart', product)" v-else>
                            В корзине
                        </button>
                        <div class="d-block">
                            <span class="d-block price">{{ product.price }}</span>
                            <span class="d-block weight">{{ product.weight }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="sass" scoped>
.category
    .col
        max-width: 320px
</style>