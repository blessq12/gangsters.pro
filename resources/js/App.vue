<script>
import { mapStores } from 'pinia';
import { userStore } from './stores/userStore.js';
import { localStore } from './stores/localStore.js';
export default{
    props:{
        goods: Object,
        company: Object,
        stories: Object,
        banners: Object
    },
    provide(){
        return {
            goods: this.goods,
            company: this.company
        }
    },
    mounted(){
        this.localStore.loadStores()
    },
    computed:{
        ...mapStores(userStore, localStore)
    }
}
</script>

<template>
    <navbar></navbar>
    <section>
        <stories :stories="stories"></stories>
    </section>
    <section class="pt-0">
        <hero-banner :banners="banners"></hero-banner>
    </section>
    <section class="pt-0">
        <catalog :goods="goods"></catalog>
    </section>
    <Footer></Footer>
</template>

<style lang="sass">
</style>