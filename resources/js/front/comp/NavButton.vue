<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage.js'
import { localStore } from '../../stores/localStore';
export default {
    mounted() {
    },
    props: {
        target: String
    },
    computed: {
        ...mapStores( appStore , localStore )
    }
}
</script>

<template>
    <button type="button" class="btn btn-outline-primary" v-if="target == 'fav'" @click="appStore.modal = !appStore.modal; appStore.modalName = 'fav'">
        <span v-if="localStore.fav.length" class="nav-badge">{{ localStore.fav.length }}</span>
        <i class="fa fa-heart-o" ></i>
    </button>
    <button type="button" class="btn btn-outline-primary" v-if="target == 'cart'" @click="appStore.modal = !appStore.modal; appStore.modalName = 'cart'">
        <span v-if="localStore.cart.length" class="nav-badge">{{ localStore.cart.length }}</span>
        <i class="fa fa-shopping-basket" ></i>
    </button>
    <button type="button" class="btn btn-outline-primary" v-if="target == 'user'" @click="appStore.modal = !appStore.modal; appStore.modalName = 'user'">
        <i class="fa fa-user-o" ></i>
    </button>
</template>

<style lang="sass" scoped>

</style>