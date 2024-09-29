<script>
import { mapStores } from "pinia";
import { localStore } from "../../stores/localStore";

export default {
    data: () => ({
        cart: false,
        cartContent: false,
        contentHide: false,
        images: {
            cart: '/images/cart-icon.png',
            coin: '/images/coin-icon.png'
        }
    }),
    computed: {
        ...mapStores(localStore)
    },
    watch: {
        cart(val) {
            this.contentHide = true
            setTimeout(() => {
                this.contentHide = false
                this.cartContent = val ? true : false
            }, 500)
        },
        'localStore.cart': {
            handler(newVal, oldVal) {
                if (newVal.length > 0) {
                    this.cart = true
                } else {
                    this.cart = false    
                }
            },
            deep: true
        }
    }
}
</script>

<template>
<div class="wrapper position-fixed invisible">
    <div class="container py-4 py-lg-5 h-100">
        <div class="row h-100 align-items-end">
            <div class="col">
                <div class="d-flex align-items-center justify-content-start">
                    <div class="icon visible" :style="{background: cart ? `url(${images.cart})` : `url(${images.coin})`}">
                        
                    </div>
                    <div class="content invisible fs-4" :class="{ 'hide': contentHide }">
                        <div class="cart" v-if="cartContent">
                            <span class="d-block">Сумма: {{ localStore.cartTotal }}</span>
                            <span class="d-block">Количество: {{ localStore.cartQty }}</span>
                        </div>
                        <div class="coin" v-else>
                            <span class="fw-medium">Получайте кэшбэк с Gangster Coin!</span>
                            <span class="d-block">
                                Зарегистрируйтесь и начните зарабатывать<br> кэшбэк с каждой покупки!
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>    
</template>

<style scoped lang="sass">
.cart, .coin
    background: #fff
    padding: 10px 25px
    visibility: visible
    width: fit-content
    span
        display: block
        font-weight:300
        line-height: 1
        font-size: clamp(18px, 3.2vw, 22px)
        margin-bottom: 4px
.wrapper
    top: 0
    left: 0
    width: 100%
    height: 100%
    z-index: 20
.icon
    width: 100px
    height: 100px
    background-position: center !important
    background-size: contain !important
    background-repeat: no-repeat !important
.content
    width: 100%
    word-wrap: no-break
    white-space: nowrap
    overflow: hidden
    transition: all .3s
    &.hide
        width: 0
</style>