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
    },
    data:()=>({
        modal: false,
        additional: false,
        currentProduct: null,
        currentComponent: null
    }),
    watch:{},
    methods:{
        toggleAdditional(product){
            this.additional = true
            this.currentProduct = product
        }
    }
}
</script>

<template>
    <modal 
        v-if="modal"
        :comp="currentComponent"
        @close="modal = false"
    ></modal>
    
    <additional 
        v-if="additional" 
        :product="currentProduct"
        :render="additional"
        @close="additional = false"
    ></additional>

    <navbar @toggleModal="(component)=>{ overlay=!overlay;modal=!modal;currentComponent=component }"></navbar>
    
    <section>
        <stories :stories="stories"></stories>
    </section>

    <section class="pt-0">
        <hero-banner :banners="banners"></hero-banner>
    </section>

    <section class="pt-0">
        <catalog 
            :goods="goods"
            @toggleAdditional="toggleAdditional"
        ></catalog>
    </section>

    <Footer></Footer>

</template>

<style lang="sass">
.overlay
    position: fixed
    top: 0
    left: 0
    width: 100%
    height: 100%
    background: rgba(0, 0, 0, .5)
    z-index: 10
</style>