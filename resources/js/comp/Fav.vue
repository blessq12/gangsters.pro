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
    <div class="row" v-if="localStore.fav.length" key="fav">
        <div class="col-6">
            <h4>Избранное:</h4>
            <p>Товаров в избранном: <b>{{ localStore.fav.length }}</b></p>
            <p>Стоимость: <b>{{ localStore.totalFav }}</b></p>
            <div class="d-flex">
                <button type="button" class="btn btn-outline-primary">
                    Перенести в корзиину
                </button>
                <button 
                    type="button" 
                    class="btn btn-danger mx-2"
                    @click="localStore.clearStore('fav')"
                >
                    Удалить все
                </button>
            </div>
        </div>
        <div class="col-6">
            <transition-group
                tag="ul"
                class="list-unstyled p-0 m-0"
                enter-active-class="animate__animated animate__fadeInRight"
                leave-active-class="animate__animated animate__fadeOutRight"
            >
                <li v-for="fav in localStore.fav" :key="fav.id">
                    <single-product :prod="fav" comp="fav"></single-product>
                </li>
            </transition-group>
        </div>
    </div>
    <div class="row" v-else key="emptyFav">
        <div class="col-12 text-center">
            <h2>В избранном ничего нет</h2>
        </div>
    </div>
</transition>
</template>

<style lang="sass"></style>