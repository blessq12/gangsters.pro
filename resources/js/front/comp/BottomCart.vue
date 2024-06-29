<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage';
import { localStore } from '../../stores/localStore';

export default {
computed: {
    ...mapStores(localStore, appStore),
    cartTotal() {
        return this.localStore.cartTotal;
    },
    cartQty() {
        return this.localStore.cartQty;
    },
    favTotal() {
        return this.localStore.favTotal;
    }
}
}
</script>

<template>

    <transition enter-active-class="animate__animated animate__slideInUp" leave-active-class="animate__animated animate__slideOutDown" mode="out-in">
        <div class="bottom-cart" v-if="localStore.cartQty">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="d-block">
                            <p class="mb-0">
                                Сумма: 
                                    <transition 
                                    enter-active-class="animate__animated animate__fadeIn" 
                                    leave-active-class="animate__animated animate__fadeOut" 
                                    mode="out-in"
                                    >
                                        <span class="fw-semibold" v-if="cartTotal" :key="cartTotal">{{ cartTotal }}</span>
                                    </transition> 
                                руб.
                            </p>
                            <p class="mb-0">
                                Наборов: 
                                    <transition 
                                    enter-active-class="animate__animated animate__fadeIn" 
                                    leave-active-class="animate__animated animate__fadeOut" 
                                    mode="out-in"
                                    >
                                        <span class="fw-semibold" v-if="cartQty" :key="cartQty">{{ cartQty }}</span>
                                    </transition> 
                                шт.
                            </p>
                        </div>
                    </div>
                    <div class="col text-end">
                        <button type="button" class="btn footer rounded" @click="appStore.modal = !appStore.modal; appStore.modalName = 'cart'">
                            <i class="fa fa-shopping-basket"></i> Корзина
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
    
</template>

<style scoped lang="sass">
.bottom-cart 
    border-top: 1px solid #dedede
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.4)
    position: fixed
    bottom: 0
    width: 100%
    color: #fff
    background: $color-main
    padding: 12px 0
    z-index: 10
button.footer
    background: transparent
    color: #fff
    border: 1px solid #fff
    padding: 14px 18px
    white-space: nowrap
    &:hover
        background: #fff
        color: $color-main
    &.active
        background: orange
</style>
