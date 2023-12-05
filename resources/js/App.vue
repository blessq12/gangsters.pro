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
        overlay: false,
        modal: false,
        additional: false,
        currentProduct: null,
        currentComponent: null
    }),
    watch:{
        overlay(val){
            if (!val){
                this.modal = false
                this.additional = false
                this.currentProduct = null
                this.currentComponent = null
                if (document.body.classList.contains('overflow-hidden')){
                    document.body.classList.remove('overflow-hidden')
                }
            } else {
                if (!document.body.classList.contains('overflow-hidden')){
                    document.body.classList.add('overflow-hidden')
                }
            }
        }
    },
    methods:{
        toggleAdditional(product){
            this.overlay = true
            this.additional = true
            this.currentProduct = product
        }
    }
}
</script>

<template>
    <transition
        enter-active-class="animate__animated animate__fadeIn"
        leave-active-class="animate__animated animate__fadeOut"
    >
        <div class="overlay" v-if="overlay" @click="overlay = !overlay"></div>
    </transition>

    <modal 
        v-if="modal"
        :comp="currentComponent"    
    ></modal>
    
    <additional 
        v-if="additional" 
        :product="currentProduct"
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