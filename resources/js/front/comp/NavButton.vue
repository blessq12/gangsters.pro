<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage.js'
export default {
    props: {
        target: String
    },
    computed: {
        ...mapStores(appStore)
    }
}
</script>

<template>
    <button type="button" class="btn btn-outline-primary" v-if="target == 'fav'" @click="appStore.modal = !appStore.modal">
        <i class="fa fa-heart-o" ></i>
    </button>
    <button type="button" class="btn btn-outline-primary" v-if="target == 'cart'" @click="appStore.modal = !appStore.modal">
        <i class="fa fa-shopping-basket" ></i>
    </button>
    <button type="button" class="btn btn-outline-primary" v-if="target == 'user'" @click="appStore.modal = !appStore.modal">
        <i class="fa fa-user-o" ></i>
    </button>
</template>

<style lang="sass" scoped>

</style>