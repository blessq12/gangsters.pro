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
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h4>Избранное</h4>
                    <button type="button" class="btn btn-close" @click="$emit('close')"></button>
                </div>
            </div>
        </div>
        <div class="row flex-column-reverse flex-md-row">
            <div class="col-12 col-md-6 mb-4 mb-md-0">
                
                <p>
                    Добавляйте товары в избранное, чтобы иметь быстрый доступ к понравившися позициями, каждый товар из избранного можно добавлять по-одному в корзину, либо же скопировать все товары из избранного в корзину, чтобы заказ закнимал меньше времени
                </p>
                <button type="button" class="btn btn-primary">Скопировать в корзину</button>
            </div>
            <div class="col-12 col-md-6">
                <transition-group
                    v-if="localStore.fav.length"
                    enter-active-class="animate__animated animate__fadeInRight"
                    leave-active-class="animate__animated animate__fadeOutRight"
                    tag="ul"
                    class="list-unstyled p-0 m-0"
                >
                    <li v-for="prod in localStore.fav" :key="prod.id">
                        <single-product comp="fav" :prod="prod"></single-product>
                    </li>
                </transition-group>
                <div v-else class="text-center">
                    <p class="text-danger">В избранном еще нет ни одного товара</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="sass">

</style>