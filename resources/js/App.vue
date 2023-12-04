<script>
import { mapStores } from 'pinia';
import {userStore} from './stores/userStore.js'
export default{
    props:{
        goods: Object,
        company: Object
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
        <stories :stories="[]"></stories>
    </section>
    <section class="pt-0">
        <hero-banner :banners="[]"></hero-banner>
    </section>
    <section class="pt-0">
        <catalog :goods="[]"></catalog>
    </section>
    <Footer></Footer>
</template>

<style lang="sass">
</style>