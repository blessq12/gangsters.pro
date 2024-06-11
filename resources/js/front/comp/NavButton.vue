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

    <button @click="appStore.modal = true; appStore.modalName=target" class="btn btn-outline-primary btn-sm">
        <span class="d-none d-lg-block">{{ target == 'user' ? 'меню'  : ''}}</span>
        <i :class="target == 'user' ? 'fa fa-bars px-1' : 'fa fa-heart px-1'"></i>
    </button>
</template>

<style lang="sass" scoped>

</style>