<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
export default{
    computed:{
        ...mapStores(localStore)
    }
}
</script>

<template>
    <transition
        enter-active-class="animate__animated animate__fadeIn"
        leave-active-class="animate__animated animate__fadeOut"
        mode="out-in"
    >
    <div class="row" v-if="localStore.cart.length" key="cart">
        <div class="col-6">
            <h4>Корзина</h4>
            <p class="mb-0">Стоимость: <b>{{ localStore.totalCart }} RUR</b></p>
            <p class="mb-0">Кол-во наборов: <b>{{ localStore.totalKits }} шт</b></p>
        </div>
        <div class="col-6">
            <transition-group
                class="list-unstyled p-0 m-0"
                tag="ul"
                enter-active-class="animate__animated animate__fadeInRight"
                leave-active-class="animate__animated animate__fadeOutRight"
            >
                    <li v-for="product in localStore.cart" :key="product.id">
                        <single-product :prod="product" comp="cart"></single-product>
                    </li>
            </transition-group>

        </div>
    </div>
    <div class="row" v-else key="emptyCart">
        <div class="col-12 text-center">
            <h2>В вашей корзине ничего нет</h2>
        </div>
    </div>
    </transition>
</template>

<style lang="sass">
</style>