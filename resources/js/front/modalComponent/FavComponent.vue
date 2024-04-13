<script>
import { mapStores } from 'pinia';
import { localStore } from '../../stores/localStore';
export default {
    computed: {
        ...mapStores( localStore )
    }
}
</script>

<template>
    <transition
        enter-active-class="animate__animated animate__fadeIn"
        leave-active-class="animate__animated animate__fadeOut"
        mode="out-in"
    >
        <div v-if="localStore.fav.length">
            <ul class="fav-list">
                <li v-for="item in localStore.fav">
                    <div class="fav-card">
                        <div class="image bg-image rounded" style="background: url('http://via.placeholder.com/256x256');">
                        </div>
                        <div class="d-block">
                            <div class="content">
                                <span>{{ item.name }}</span>
                                <p class="mb-0">{{ item.consist }}</p>
                            </div>
                            <div class="footer">
                                <button type="button" class="btn btn-secondary rounded">В корзину</button>
                                <button type="button" class="btn btn-danger rounded" @click="localStore.manageStore('fav', item)">Удалить</button>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="cart-footer border-top pt-4 pb-2">
                <ul class="list-unstyled">
                    <li> <b>Стоимость: </b> 
                        <transition
                            enter-active-class="animate__animated animate__fadeIn"
                            leave-active-class="animate__animated animate__fadeOut"
                            mode="out-in"
                        >
                            <span v-if="localStore.favTotal" :key="localStore.favTotal">{{ localStore.favTotal + ' рублей' }}</span>
                        </transition>    
                    </li>
                    <li> <b>Наборов: </b> 
                        <transition
                            enter-active-class="animate__animated animate__fadeIn"
                            leave-active-class="animate__animated animate__fadeOut"
                            mode="out-in"
                        >
                            <span v-if="localStore.fav.length" :key="localStore.fav.length">{{ localStore.fav.length + ' шт' }}</span>
                        </transition>    
                    </li>
                </ul>
            </div>                
            <button class="btn rounded btn-danger" @click="localStore.clearStore('fav')"> <i class="fa fa-trash"></i> Очистить избранное</button>
        </div>
        <div v-else>
            <div class="row">
                <div class="col text-center">
                    <p class="mb-0">
                        Пусто
                    </p>
                </div>
            </div>
        </div>
    </transition>
</template>

<style lang="sass" scoped>

</style>