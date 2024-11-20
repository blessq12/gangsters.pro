<script>
import { localStore } from '../../stores/localStore'
import { mapStores } from 'pinia'

export default {
    props: {
        product: Object,
        isFavorite: Boolean
    },
    data() {
        return {
            //
        }
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
            if (this.product.thumbnails.length && this.product.thumbnails !== undefined) {
                return this.product.thumbnails[0].small
            }
            return false
        }
    }
}
</script>

<template>
    <div class="product">
        <div 
            class="image bg-image rounded overflow-hidden position-relative" 
            v-lazy:background-image="getThumbs"
        >
            <div class="count" v-if="!isFavorite">
                <span>{{ product.qty }}</span>
            </div>
        </div>
        <div class="content">
            <span>{{ product.name }}</span>
            <p class="mb-1"><b>Цена: </b>{{ product.price }} руб.</p>
            <div v-if="!isFavorite">
                <div class="cart-qty rounded">
                    <div class="btn-group">
                        <button @click="localStore.manageQty( false, product )" class="btn btn-primary">
                            <i class="fa fa-minus"></i>
                        </button>
                        <button @click="localStore.manageQty( true, product )" class="btn btn-primary">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="btn-group">
                    <transition
                        enter-active-class="animate__animated animate__faster animate__fadeIn"
                        leave-active-class="animate__animated animate__faster animate__fadeOut"
                        mode="out-in"
                    >
                        <button class="btn from-cart" @click="localStore.manageStore('cart', product)" v-if="localStore.checkExist('cart', product)" >
                            <i class="fa fa-minus"></i>
                        </button>
                        <button class="btn to-cart" @click="localStore.manageStore('cart', product)" v-else>
                            <i class="fa fa-plus "></i>
                            
                        </button>
                    </transition>
                    <button class="btn trash" @click="localStore.manageStore('fav', product)">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>



<style scoped lang="sass">
.btn-group
    button
        background: lighten($color-main, 20%)
        padding: 8px 40px !important
        &:hover
            background: darken($color-main, 10%)
    .btn
        &.to-cart
            background: #28a745
            &:hover
                background: darken(#28a745, 10%) !important
        &.from-cart
            background: #dc3545
            &:hover
                background: darken(#dc3545, 10%)
        &.trash
            background: lighten(#dc3545, 50%)
            border-color: #dc3545 !important
            border: 1px solid #dc3545
            border-left: none
            color: #dc3545
            &:hover
                background: lighten(#dc3545, 20%)
                border-color: darken(#dc3545, 10%)
                color: #fff

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
        .cart-qty
            display: flex
            align-items: center
            justify-content: start
            overflow: hidden
            width: fit-content
            button
                margin: 0
                padding: 0
                // background: none
                border: none
                outline: none
                min-width: 40px
                min-height: 30px
                @media(min-width: 992px)
                    min-width: 55px
                    min-height: 35px
                // background: lighten($color-main, 10%)
                color: #fff
                font-size: 16px
                font-weight: 500
                transition: all .3s
                
</style>
