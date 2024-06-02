<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
import { userStore } from '../stores/userStore';
import { appStore } from '../stores/appStorage';

export default {
    mounted() {
        this.localStore.loadStorage()
        this.userStore.loadStore()
        this.appStore.defineDevice()
        alert(this.appStore.device)
    },
    computed: {
        ...mapStores(
            localStore,
            userStore,
            appStore
        )
    }
}
</script>

<template>

</template>

<style lang="sass" scoped>
</style>