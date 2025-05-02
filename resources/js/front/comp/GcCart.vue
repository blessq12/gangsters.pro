<script>
import { mapStores } from "pinia";
import { appStore } from "../../stores/appStorage";
import { localStore } from "../../stores/localStore";
import { userStore } from "../../stores/userStore";

export default {
    mounted() {
        //
        if (this.userStore.authStatus) {
            this.cart = true;
        }
        window.addEventListener("scroll", this.updateScrollTop); // Add this line
    },
    beforeDestroy() {
        window.removeEventListener("scroll", this.updateScrollTop); // Clean up the event listener
    },
    data: () => ({
        cart: false,
        cartContent: false,
        contentHide: false,
        images: {
            cart: "/images/cart-icon.png",
            coin: "/images/coin-icon.png",
        },
        scrolled: false,
        scrollTop: 0,
    }),
    computed: {
        ...mapStores(localStore, userStore, appStore),
        scrollHeight() {
            return window.scrollY;
        },
    },
    methods: {
        openModal(name) {
            this.appStore.modal = true;
            this.appStore.modalName = name;
        },
        updateScrollTop() {
            this.scrollTop = window.scrollY;
        },
        scrltop() {
            window.scrollTo({
                top: 0,
                behavior: "smooth",
            });
        },
    },
    watch: {
        cart(val) {
            this.contentHide = true;
            setTimeout(() => {
                this.contentHide = false;
                this.cartContent = val ? true : false;
            }, 500);
        },
        "localStore.cart": {
            handler(newVal, oldVal) {
                if (newVal.length > 0) {
                    this.cart = true;
                } else {
                    this.cart = false;
                }
            },
            deep: true,
        },
        scrollTop(newVal) {
            if (newVal > 800) {
                this.scrolled = true;
            } else {
                this.scrolled = false;
            }
        },
    },
};
</script>

<template>
    <div class="wrapper position-fixed invisible">
        <div class="container py-4 py-lg-5 h-100">
            <div class="row h-100 align-items-end">
                <div class="col">
                    <div class="row align-items-center">
                        <div class="col">
                            <div
                                class="d-flex align-items-center justify-content-start"
                            >
                                <div
                                    class="icon visible cursor-pointer"
                                    :style="{
                                        background: cart
                                            ? `url(${images.cart})`
                                            : `url(${images.coin})`,
                                    }"
                                    @click="
                                        cart
                                            ? openModal('cart')
                                            : openModal('user')
                                    "
                                ></div>
                                <div
                                    class="content invisible fs-4"
                                    :class="{ hide: contentHide }"
                                >
                                    <div class="cart" v-if="cartContent">
                                        <span class="d-block"
                                            >Сумма:
                                            {{ localStore.cartTotal }}
                                            руб.</span
                                        >
                                        <span class="d-block"
                                            >Количество:
                                            {{ localStore.cartQty }} шт.</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col text-end">
                            <transition
                                enter-active-class="animate__animated animate__fadeIn"
                                leave-active-class="animate__animated animate__fadeOut"
                            >
                                <button
                                    class="btn btn-main visible"
                                    v-if="scrolled"
                                    title="Наверх"
                                    @click="scrltop"
                                >
                                    <i class="fa fa-arrow-up"></i>
                                    <p class="m-0 d-none d-md-block">Наверх</p>
                                </button>
                            </transition>
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
    padding: 5px 12px
    visibility: visible
    width: fit-content

    color: #fff

    @media (min-width: 768px)
        padding: 10px 25px
    span
        display: block
        font-weight:300
        line-height: 1
        font-size: clamp(14px, 3.2vw, 16px)
        margin-bottom: 4px
.wrapper
    top: 0
    left: 0
    width: 100%
    height: 100%
    z-index: 9
.icon
    width: 80px
    height: 80px
    background-position: center !important
    background-size: contain !important
    background-repeat: no-repeat !important
    @media (min-width: 768px)
        width: 100px
        height: 100px
.content
    width: 100%
    word-wrap: no-break
    white-space: nowrap
    overflow: hidden
    transition: all .3s
    &.hide
        width: 0
</style>
