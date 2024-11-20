<script>
import { mapStores } from 'pinia';
    import { appStore } from '../../stores/appStorage.js';

export default {
    computed: {
        ...mapStores(appStore)
    },
    mounted() {
        //
    },
    methods: {
        //
        toggleMenu() {
            if (this.appStore.modal == true) {
                this.appStore.modal = false;
            } else {
                this.appStore.modal = true;
                this.appStore.modalName = 'menu';
            }
        }
    }
}
</script>

<template>
    <button class="menu-button" @click="toggleMenu">
        Меню
        <i class="fa fa-bars"></i>
    </button>
</template>

<style scoped lang="scss">

</style>