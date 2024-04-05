<script>
import { localStore } from '../../stores/localStore';
import { mapStores } from 'pinia';

export default {
    computed: {
        ...mapStores( localStore )
    }
}

// localStore.cartTotal() - return a total cost from cart
</script>

<template>
    <div v-if="localStore.cart.length">
        <ul class="cart-list">
            <li v-for="item in localStore.cart">
                <div class="fav-card">
                    <div class="image bg-image rounded" style="background: url('http://via.placeholder.com/256x256');">
                    </div>
                    <div class="d-blo">
                        <div class="content">
                            <span>{{ item.name }}</span>
                            <p class="mb-0">{{ item.consist }}</p>
                        </div>
                        <div class="footer">
                            <div class="qty">
                                <button type="button" class="btn" @click="localStore.manageQty(false, item)">
                                    <i class="fa fa-minus"></i>
                                </button>
                                <span>{{ item.qty ? item.qty : 'Нет' }}</span>
                                <button type="button" class="btn" @click="localStore.manageQty(true, item)">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <button type="button" class="btn btn-danger rounded" @click="localStore.manageStore('cart', item)">Удалить</button>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <div v-else>
        <div class="row">
            <div class="col text-center">
                <p class="mb-0">Пусто</p>
            </div>
        </div>
    </div>
    

</template>

<style lang="sass" scoped>

</style>