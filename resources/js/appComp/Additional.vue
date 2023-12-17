<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
import { getTransitionRawChildren } from 'vue';
export default{
    emits:['close'],
    props:{
        product: Object,
        render: Boolean
    },
    computed:{
        ...mapStores(localStore)
    },
    mounted(){
        this.overlay = !this.overlay
        setTimeout(()=>{
            if (this.render){
                this.show = true
            }
        },200)
    },
    data:()=>({
        show: false,
        overlay: false
    }),
    methods:{
        closeAdditional(){
            this.show=!this.show
            this.overlay=!this.overlay
            setTimeout(()=>{
                this.$emit('close')
            },200)
        }
    }
}
</script>

<template>
    <overlay :overlay="overlay" @click="closeAdditional"></overlay>
    <div class="additional-wrap">
        <div class="container">
            <div class="row">
                <div class="col-12 d-flex justify-content-center">
                    
                    <transition
                        enter-active-class="animate__animated animate__zoomIn"
                        leave-active-class="animate__animated animate__zoomOut"
                    >
                        <div class="additional" v-if="show">
                            <div class="row h-100">
                                <div class="col-6 ">
                                    <div class="h-100">
                                        <ul>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="h-100">
                                        <h2>{{ product.name }}</h2>
                                        <p>
                                            Состав: {{ product.consist }}
                                        </p>
                                        <div class="d-flex">
                                            <span class="btn btn-outline-primary">
                                                {{ product.weight }} гр
                                            </span>
                                            <span class="btn btn-outline-primary mx-2">
                                                {{ product.price }} руб
                                            </span>
                                        </div>    
                                        <div class="d-flex mt-4">
                                            <button 
                                                class="btn"
                                                :class="localStore.checkItemInStore( product , 'cart') ? 'btn-primary' : 'btn-outline-primary'"
                                                @click="localStore.manageStore( product, 'cart')"
                                            >
                                                {{ localStore.checkItemInStore( product , 'cart') ? 'Уже в корзине' : 'Добавить в корзину' }}
                                            </button>
                                            <button 
                                                class="btn mx-2"
                                                :class="localStore.checkItemInStore( product , 'fav') ? 'btn-danger' : 'btn-outline-danger text-danger'"
                                                @click="localStore.manageStore( product, 'fav' )"
                                            >
                                                <i class="fa fa-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>

                </div>
            </div>
       </div>

    </div>
</template>

<style lang="sass">

</style>