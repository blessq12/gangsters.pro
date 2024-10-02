<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
import { userStore } from '../stores/userStore';
import { appStore } from '../stores/appStorage';
import { useToast } from 'vue-toastification'
const toast = useToast()

export default {
    props:{
        links: Object,
        company: Object
    },
    mounted() {
        this.localStore.loadStorage()
        this.userStore.loadStore()
        this.appStore.defineDevice()

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
    },
    watch: {
        'userStore.authStatus': {
            handler(val) {
                if (val) {
                    toast.info('Вы успешно авторизовались')
                } else {
                    toast.warning('Вы вышли из аккаунта')
                }
            },
            immediate: true,
        }
    }
}
</script>

<template>

</template>

<style lang="sass" scoped>
</style>