<script>
import { localStore } from '../../stores/localStore'
import { mapStores } from 'pinia'

export default {
    props: {
        product: Object,
        isFavorite: Boolean
    },
    methods: {
    },
    computed: {
        ...mapStores(localStore)
    }
}
</script>

<template>
    <div class="product">
        <div class="image bg-image rounded overflow-hidden position-relative" :style="product.thumbs.length ? { backgroundImage: `url(${product.thumbs[0].small})` } : {backgroundImage: 'url(//via.placeholder.com/150)'}">
            <div class="count" v-if="!isFavorite">
                <span>{{ product.qty }}</span>
            </div>
        </div>
        <div class="content">
            <span>{{ product.name }}</span>
            <p class="mb-1"><b>Цена: </b>{{ product.price }} руб.</p>
            <div v-if="!isFavorite">
                <div class="qty rounded">
                    <button @click="localStore.manageQty( false, product )">-</button>
                    <button @click="localStore.manageQty( true, product )">+</button>
                </div>
            </div>
            <div v-else class="fav-btns">
                <transition
                    enter-active-class="animate__animated animate__faster animate__fadeIn"
                    leave-active-class="animate__animated animate__faster animate__fadeOut"
                    mode="out-in"
                >
                    <button class="from-cart rounded" @click="localStore.manageStore( 'cart',product )" v-if="localStore.checkExist('cart', product)"><i class="fa fa-minus"></i></button>
                    <button class="to-cart rounded" @click="localStore.manageStore( 'cart',product )" v-else><i class="fa fa-cart-plus"></i></button>
                </transition>

                <button class="trash rounded" @click="localStore.manageStore( 'fav',product )"><i class="fa fa-trash"></i></button>
            </div>
        </div>
    </div>
</template>



<style scoped lang="sass">
.fav-btns
    display: flex
    button
        color: #fff
        border: unset
        padding: 0 !important
        margin-right: 8px
        min-width: 55px
        min-height: 30px
        @media(min-width: 992px)
            min-width: 65px
            min-height: 35px
        display: flex
        align-items: center
        justify-content: center
    button.to-cart
        background: green
    button.from-cart
        background: lighten($color-main, 10%)
    button.trash
        background: red
.product 
    display: flex
    align-items: center
    margin-bottom: 8px
    .image
        min-height: 100px
        min-width: 100px
        margin-right: 8px
        @media(min-width: 992px)
            min-height: 120px
            min-width: 120px
            margin-right: 12px
    .count
        position: absolute
        top: 0
        right: 0
        width: 100%
        height: 100%
        background: rgba(0, 0, 0, .2)
        color: #fff
        display: flex
        align-items: center
        justify-content: center
        span
            font-size: 34px
            font-weight: 500
            @media(min-width: 992px)
                font-size: 55px
                font-weight: 700
    img
        max-height: 75px
    .content
        span
            display: block
            font-size: 16px
            font-weight: 500
            @media(min-width: 992px)
                font-size: 18px
                font-weight: 600
        .qty
            display: flex
            align-items: center
            justify-content: start
            overflow: hidden
            width: fit-content
            button
                margin: 0
                padding: 0
                background: none
                border: none
                outline: none
                min-width: 40px
                min-height: 30px
                @media(min-width: 992px)
                    min-width: 55px
                    min-height: 35px
                background: lighten($color-main, 10%)
                color: #fff
                font-size: 16px
                font-weight: 500
                transition: all .3s
                &:hover 
                    background: darken($color-main, 10%)
</style>
