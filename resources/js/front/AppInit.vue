<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
import { userStore } from '../stores/userStore';
export default {
    mounted() {
        this.localStore.loadStorage()
        this.userStore.loadStore()
    },
    computed: {
        ...mapStores(
            localStore,
            userStore
        )
    }
}
</script>

<template>

</template>

<style lang="sass" scoped>
</style>