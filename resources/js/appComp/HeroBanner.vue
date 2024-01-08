<script>
import { es } from 'yup-locales';

export default{
    props:{
        banners: Object
    },
    data:()=>({
        current: 0,
        direction: null
    }),
    methods:{
        slide(){

        }
    },
    watch:{
        current(val){
            if (val >= this.banners.length){
                this.current = 0
            } else if (val < 0){
                this.current = this.banners.length - 1
            }
        }
    }
}
</script>

<template>
    <div class="container">
        <div class="row">
            <div class="col" v-if="banners.length">
                <div class="position-relative d-flex align-items-center">
                    <transition-group
                        class="wrap position-relative overflow-hidden w-100"
                        tag="div"
                        :enter-active-class="direction ? 'animate__animated animate__slideInRight' : 'animate__animated animate__slideInLeft'"
                        :leave-active-class="direction ? 'animate__animated animate__slideOutLeft' : 'animate__animated animate__slideOutRight'"
                    >
                        <div 
                        class="position-absolute w-100 h-100 bg-image"
                        :style="'background: url(' + banner.image.path + ')'" 
                        v-for="banner in banners" :key="banner.id" 
                        v-show="banners.indexOf(banner) == current">
                            <div class="d-flex align-items-end position-relative h-100 p-2 p-md-3">
                                <div class="text-light">
                                    <h4>{{ banner.header }}</h4>
                                    <p class="mb-0">{{ banner.subheader }}</p>
                                </div>
                            </div>
                        </div>
                    </transition-group>
                    <div class="position-absolute d-flex justify-content-between w-100 px-1 px-md-3">
                        <button type="button" class="btn p-0 text-light" @click="current--; direction = false"><i class="fa fa-arrow-left"></i></button>
                        <button type="button" class="btn p-0 text-light" @click="current++; direction = true"><i class="fa fa-arrow-right"></i></button>
                    </div>
                </div>
                
            </div>
            <div class="col" v-else>
                <div class="placeholder-glow wrap position-relative overflow-hidden">
                    <div class="position-absolute w-100 h-100 placeholder"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="sass" scoped>
.wrap
    background: $color-main
    min-height: 220px
    @media(min-width: 576px)
        min-height: 280px
    @media(min-width: 768px)
        min-height: 320px
    @media (min-width: 992px)
        min-height: 400px   
    border-radius: $default-radius !important
    h4
        font-size: 1rem
        margin-bottom: 0
    p
        font-size: .6rem
</style>