<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
export default{
    computed:{
        ...mapStores(localStore)
    },
    inject:['company']
}
</script>

<template>
    <nav>
        <div class="container">
            <div class="row">
                <div class="col-6">
                    <a href="/">
                        {{ company.name }}
                    </a>
                </div>
                <div class="col-6">
                    <ul class="list-unstyled p-0 m-0 d-flex justify-content-end">
                        <li>
                            <button class="btn btn-outline-primary position-relative" type="button" @click="$emit('toggleModal', 'cart')">
                                <span 
                                    v-if="localStore.cart.length"
                                    class="bg-danger rounded-circle position-absolute text-white d-flex align-items-center justify-content-center"
                                    style=" top:-12px; right: -12px; width: 25px;height: 25px;"
                                >{{ localStore.cart.length }}</span>
                                <i class="fa fa-shopping-basket"></i>
                            </button>
                        </li>
                        <li>
                            <button class="btn btn-outline-primary position-relative mx-3" type="button" @click="$emit('toggleModal', 'fav')">
                                <span
                                    v-if="localStore.fav.length"
                                    class="bg-danger rounded-circle position-absolute text-white d-flex align-items-center justify-content-center"
                                    style=" top:-12px; right: -12px; width: 25px;height: 25px;"
                                >{{ localStore.fav.length }}</span>
                                <i class="fa fa-heart"></i>
                            </button>
                        </li>
                        <li>
                            <button class="btn btn-outline-primary" type="button" @click="$emit('toggleModal', 'user')">
                                <i class="fa fa-user"></i>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</template>

<style lang="sass">

</style>