<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
export default{
    props:{
        goods: Object
    },
    data:()=>({}),
    computed:{
        ...mapStores(localStore)
    }
}
</script>

<template>
    <div class="container" v-if="goods.length">
        <div class="row">
            <div class="col-12" style="margin-bottom: 48px;">
                <ul class="list-unstyled p-0 m-0 categories">
                    <li v-for="category in goods" :key="category.id">
                        <button>
                            {{ category.name }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-12" v-for="category in goods" :key="category.id">
                <h4>{{ category.name }}</h4>
                <ul class="list-unstyled p-0 category-list" v-if="category.products.length">
                    <li v-for="product in category.products" :key="product.id">
                        <div class="product">
                            <div class="header bg-image" v-if="product.thumb_medium.length" :style="'background:url('+ product.thumb_medium[0].path +')'">
                            </div>
                            <div class="header bg-image" style="background: url('/images/placeholder/product-image-empty-512x512.jpg');" v-else></div>
                            <div class="content">
                                <h5>{{ product.name }}</h5>
                                <p>{{ product.consist }}</p>
                            </div>
                            <div class="footer">

                                <button type="button" class="btn btn-primary" @click="localStore.manageStore(product, 'cart')">
                                
                                    {{  localStore.checkItemInStore(product, 'cart') ? 'asdasd' : 'hhhghhghg' }}
                                
                                </button>
                                
                                
                                <button type="button" class="btn btn-danger mx-1"
                                    @click="localStore.manageStore(product, 'fav')"
                                >
                                    <transition
                                        enter-active-class="animate__animated animate__fadeIn"
                                        leave-active-class="animate__animated animate__fadeOut"
                                        :duration="50"
                                        mode="out-in"
                                    >
                                        <i class="fa fa-heart-o" v-if="!localStore.checkItemInStore( product, 'fav')"></i>
                                        <i class="fa fa-heart" v-else></i>
                                    </transition>

                                </button>
                                <button class="btn btn-outline-primary rounded-circle px-3 fw-bold"
                                    @click="$emit('toggleAdditional', product)"
                                >i</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <ul v-else class="list-unstyled p-0 category-list">
                    <li v-for="e in 4">
                        <div class="product placeholder-glow">
                            <div class="header bg-dark placeholder"></div>
                            <div class="content">
                                <h5 class="placeholder col-7 rounded"></h5>
                                <p class="placeholder col-12 mb-0 rounded"></p>
                                <p class="placeholder col-8 rounded"></p>
                            </div>
                            <div class="footer">
                                <button type="button" class="btn placeholder col-4" style="margin-right: 6px;"></button>
                                <button type="button" class="btn placeholder col-2"></button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container" v-else>
        <div class="row">
            <div class="col-12" style="margin-bottom: 48px;">
                <ul class="list-unstyled p-0 m-0 categories placeholder-glow">
                    <li v-for="e in 5"><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-12 placeholder-glow" v-for="e in 4">
                <h4 class="placeholder col-5 mb-4"></h4>
                <ul class="category-list list-unstyled p-0">
                    <li v-for="e in 5">
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h4 class="placeholder col-9"></h4>
                                <p class="placeholder col-12 mb-0"></p>
                                <p class="placeholder col-5 mb-0"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                    
                </ul>
            </div>
        </div>
    </div>
</template>

<style lang="sass" scoped>
.categories
    display: flex
    align-items: center
    white-space: nowrap 
    overflow: hidden
    overflow-x: scroll
    &::-webkit-scrollbar
        display: none
    li
        margin-right: 16px
        button
            background: $color-main
            border: none
            padding: 18px 22px
            font-size: 1.2rem
            border-radius: $default-radius
            transition: all .3s
            &:hover
                background: $color-secondary
                color: #fff
.category-list
    display: flex
    white-space: nowrap
    align-items: start
    overflow: hidden
    overflow-x: scroll
    margin-bottom: 48px
    &::-webkit-scrollbar
        display: none
    li
        margin-right: 24px
        .product
            min-width: 300px
            white-space: normal
            .header
                width: 100%
                height: 180px
                background: $color-main
                margin-bottom: 12px
                border-radius: $default-radius
            .content
                margin-bottom: 24px
                h5
                p
@media(min-width: 992px)
    .category-list
        flex-wrap: wrap
        li
            width: 33.333%
            margin-right: 0
            margin-bottom: 24px
            padding: 6px

@media(min-width: 1200px)
    .category-list
            flex-wrap: wrap
            li
                width: 25%

</style>