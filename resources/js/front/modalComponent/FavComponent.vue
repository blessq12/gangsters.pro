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
            <transition-group
                tag="ul"
                class="fav-list"
                enter-active-class="animate__animated animate__fadeIn"
                leave-active-class="animate__animated animate__fadeOut"
            >
                <li v-for="item in localStore.fav" :key="item.id">
                    <ProductComponentSmall :product="item" :is-favorite="true" />
                </li>
            </transition-group>
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
            <div class="row align-items-center row-cols-1">
                <div class="col text-center">
                    <img src="/images/placeholder/empty-fav.png" alt="" class="img-fluid" style="max-height: 280px">
                </div>
                <div class="col text-center">
                    <h5 class="fw-semibold">В избранном ничего нет</h5>
                    <p>
                        В избранном можно хранить понравившиеся позиции и добавлять их в корзину
                    </p>
                </div>
            </div>
        </div>
    </transition>
</template>

<style lang="sass" scoped>
.fav-list
    li
        margin-bottom: 0
</style>