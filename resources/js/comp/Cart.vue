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
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-6 mb-4 mb-md-0">
                <div class="d-flex justify-content-between">
                    <h4>Корзина</h4>
                    <button class="btn btn-close" @click="$emit('close')"></button>
                </div>
                <p>
                    Оформляйте заказы онлайн и участвуйте в нашей бонусной системе кешбэка. С каждого онлайн заказа вам будут начисляться бонусные монеты "Gangsta" койны, которымии можно оплачивать последующие заказы частично или полностью
                </p>
                <h5>Детали корзины</h5>
                <ul class="list-unstyled p-0 m-0 mb-4">
                    <li>Стоимость: <b>{{ localStore.totalCart }} рублей</b></li>
                    <li>Количество наборов <b>{{ localStore.totalKits }} штук</b></li>
                </ul>
                <div class="alert alert-warning mb-4">
                    С этого заказа будет начислено XX койнов
                </div>
                <button type="button" class="btn btn-primary">Перейти к оформлению</button>
                <button type="button" class="btn btn-danger mx-2">Очистить корзину</button>
            </div>
            <div class="col-12 col-md-6">
                <transition-group
                    tag="ul"
                    v-if="localStore.cart.length"
                    class="list-unstyled p-0 m-0"
                    enter-active-class="animate__animated animate__fadeInRight"
                    leave-active-class="animate__animated animate__fadeOutRight"
                >
                    <li v-for="prod in localStore.cart" :key="prod.id">
                        <single-product :prod="prod" comp="cart"></single-product>
                    </li>
                </transition-group>
                <div class="text-center" v-else>
                    <p class="text-danger">
                        В корзине еще нет ни одного товара
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="sass"></style>