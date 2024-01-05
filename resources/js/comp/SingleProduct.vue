<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
export default{
    props:{
        prod: Object,
        comp: String
    },
    computed:{
        ...mapStores(localStore)
    },
    mounted(){
    }
}
</script>

<template>
    <div class="single-product">
        <div class="image bg-image rounded" v-if="prod.thumb_small.length" :style="'background: url(' + prod.thumb_small[0].path + ')'"></div>
        <div class="image bg-image rounded" v-else style="background: url('/images/placeholder/product-image-empty-128x128.jpg');"></div>
        <div class="content">
            <div class="mb-2">
                <h5>{{ prod.name }}</h5>
                <div class="d-flex">
                    <span>{{ prod.weight }}</span>
                    <span class="px-2">{{ prod.price }} rur</span>
                </div>
            </div>
            <div class="d-flex" v-if="comp == 'fav'">
                <button 
                    type="button" 
                    class="btn btn-outline-primary btn-sm"

                >
                    В корзину
                    <i class="fa fa-plus"></i>
                </button>
                <button 
                    type="button" 
                    class="btn btn-outline-danger btn-sm mx-2"
                    @click="localStore.manageStore(prod, 'fav')"
                >
                    <i class="fa fa-trash"></i>
                </button>
            </div>
            <div class="btn-group" role="group" aria-label="Basic example" v-if="comp == 'cart'">
                <button type="button" class="btn btn-outline-primary btn-sm"
                    @click="localStore.qtyManager(prod, false)"
                >
                    <i class="fa fa-minus"></i>
                </button>
                <span class="mx-3 d-flex align-items-center">
                    {{ prod.qty }}
                </span>
                <button type="button" class="btn btn-outline-primary btn-sm"
                    @click="localStore.qtyManager(prod, true)"
                >
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<style lang="sass">

</style>