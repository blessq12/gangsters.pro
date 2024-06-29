<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
import { userStore } from '../stores/userStore';
import { appStore } from '../stores/appStorage';

export default {
    props:{
        links: Object,
        company: Object
    },
    mounted() {
        this.localStore.loadStorage()
        this.userStore.loadStore()
        this.appStore.defineDevice()
        this.appStore.welcomeCtaShow()
        if (this.modalDebug) {
            this.appStore.modal = true;
            this.appStore.modalName = 'user';
        }
        if (this.links && Object.keys(this.links).length > 0) {
            this.appStore.links = this.links;
        }
        if (this.company) {
            this.appStore.company = this.company;
        }
    },
    data: () => ({
        modalDebug: false
    }),
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