<script>
import { mapStores } from 'pinia';
import {userStore} from './stores/userStore.js'
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
    mounted(){},
    computed:{
        ...mapStores(userStore)
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