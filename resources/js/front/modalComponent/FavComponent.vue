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
                        <div class="fav-card">
                            <div class="image bg-image rounded" 
                            :style="'background: url(' + (item.thumbs.length ? item.thumbs[0].small : '/images/placeholder/phldr-512.png') + ');'"
                            >
                        </div>
                        <div class="d-block">
                            <div class="content">
                                <span>{{ item.name }}</span>
                                <p class="mb-0"><b>Цена: </b>{{ item.price }} руб.</p>
                            </div>
                            <div class="footer">
                                
                                <button 
                                type="button" 
                                class="btn btn-sm btn-primary rounded-pill"
                                :class="{'btn-primary' : !localStore.checkExist('cart', item), 'btn-success' : localStore.checkExist('cart', item)}"
                                @click="localStore.manageStore('cart', item)"
                                >
                                <i class="fa fa-shopping-cart px-3"></i>
                            </button>
                            
                            <button type="button" class="btn btn-sm btn-danger rounded-pill " @click="localStore.manageStore('fav', item)">
                                <i class="fa fa-trash px-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
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
            <div class="row align-items-center">
                <div class="col text-center">
                    <img src="/images/placeholder/empty-storage.png" alt="" class="img-fluid" style="max-height: 280px">
                </div>
                <div class="col">
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

</style>