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
    <button @click="appStore.modal = true; appStore.modalName=target" class="menu-button">
        <slot></slot>
    </button>
</template>

<style lang="sass" scoped>

</style>