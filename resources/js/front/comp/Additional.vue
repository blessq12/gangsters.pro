<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage'
import { localStore } from '../../stores/localStore';
export default {
    computed: {
        ...mapStores( appStore, localStore )
    },
    mounted() {
    },
    updated(){},
    data: () => ({
        prodAdditional: {},
        prodImg: [],
        imgIndex: 0
    }),
    watch: {
        'appStore.additional': {
            handler(val) {
                if (val) {
                    document.body.classList.add('overflow-hidden')
                    this.prodAdditional = {
                    hit: this.appStore.currentAdditional.hit,
                    spicy: this.appStore.currentAdditional.spicy,
                    kidsAllow: this.appStore.currentAdditional.kidsAllow,
                    garlic: this.appStore.currentAdditional.garlic,
                    onion: this.appStore.currentAdditional.onion,
                }
                }
                else {
                    document.body.classList.remove('overflow-hidden')
                    this.prodAdditional =  {}
                }

            }
        },
        imgIndex(val) {
            if (val > this.appStore.currentAdditional.images.length - 1) {
                this.imgIndex = 0
            } 
            if (val < 0)
            {
                this.imgIndex = this.appStore.currentAdditional.images.length - 1
            }
        }
    }
}
</script>

<template>
    <teleport to="body">
        <transition
            enter-active-class="animate__animated animate__fadeIn"
            leave-active-class="animate__animated animate__fadeOut"
        >
            <div class="wrap" v-if="appStore.additional" >
                <div class="overlay" @click="appStore.additional=!appStore.additional"></div>
                <div class="container position-relative" style="z-index: 10;">
                    <div class="row justify-content-center">
                        <div class="col col-lg-10">
                            <div class="additional-window">

                                <div class="row mb-4">
                                    <div class="col">
                                        <h4 class="fw-semibold mb-0">{{ appStore.currentAdditional.name }}</h4>
                                    </div>
                                    <div class="col text-end">
                                        <button class="btn-close" @click="appStore.additional = !appStore.additional; appStore.currentAdditional = null"></button>
                                    </div>
                                </div>
                                <div class="row row-cols-1 row-cols-md-2">
                                    <div class="col mb-4 mb-lg-0" v-if="appStore.currentAdditional.images.length">
                                        <transition-group
                                            tag="ul"
                                            class="images list-unstyled"
                                            enter-active-class="animate__animated animate__fadeIn"
                                            leave-active-class="animate__animated animate__fadeOut"
                                        >
                                            <li 
                                                class="bg-image rounded"
                                                style="min-height: 200px;"
                                                v-for="img in appStore.currentAdditional.images"
                                                :style="'background: url(' + img + ')'"
                                                :key="img"
                                                v-show="Array.from(appStore.currentAdditional.images).indexOf( img ) == imgIndex"
                                            >
                                            </li>
                                        </transition-group>
                                        <div class="row" v-if="appStore.currentAdditional.images.length > 1">
                                            <div class="col btn-group">
                                                <button class="btn btn-outline-primary btn-sm" @click="imgIndex--"><i class="fa fa-arrow-left"></i></button>
                                                <button class="btn btn-outline-primary btn-sm" @click="imgIndex++"><i class="fa fa-arrow-right"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col mb-4 mb-lg-0">
                                        <ul class="images list-unstyled">
                                            <li class="bg-image rounded" style="background: url('/images/placeholder/phldr-512.png'); background-size: contain !important;"></li>
                                        </ul>
                                    </div>
                                    <div class="col">
                                        <ul class="list-unstyled prod-additionals d-flex align-items-center">
                                            <li v-for="(value, key, index) in prodAdditional" :key="key" style="margin-right: 6px;">
                                                <div class="disable" v-if="!value" style="background: url('/images/additionals/disabled.png');"></div>
                                                <img :src="'/images/additionals/' + key + '.png'" alt="">
                                            </li>
                                        </ul>
                                        <p><b>Состав:</b> {{ appStore.currentAdditional.consist }} </p>
                                        <div class="row">
                                            <div class="col">
                                                <p class="mb-0"><b>Цена: </b> {{ appStore.currentAdditional.price }} руб.</p>
                                                <p class="mb-0"><b>Масса нетто: </b> {{ appStore.currentAdditional.weight }} гр.</p>
                                            </div>
                                        </div>
                                        <div class="row mt-4">
                                            <div class="col">
                                                <button 
                                                    class="btn btn-outline-primary rounded-pill"
                                                    :class="{'active' : localStore.checkExist('cart', appStore.currentAdditional)}"
                                                    @click="localStore.manageStore('cart', appStore.currentAdditional )"
                                                >
                                                    {{
                                                        localStore.checkExist('cart', appStore.currentAdditional) ?
                                                        'В корзине' :
                                                        "В корзину "
                                                    }}
                                                    <i 
                                                        class="fa mx-1"
                                                        :class="{
                                                            'fa-check': localStore.checkExist('cart', appStore.currentAdditional),
                                                            'fa-plus' : !localStore.checkExist('cart', appStore.currentAdditional),
                                                        }"
                                                    ></i>
                                                </button>
                                                <button class="mx-2 btn btn-outline-primary rounded-pill"
                                                    @click="localStore.manageStore('fav', appStore.currentAdditional )"
                                                >В избранное</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<style lang="sass" scoped>
.wrap
    display: flex
    background: #fff
    overflow: auto
    @media(min-width: 768px)
        background: unset
        align-items: center
    .overlay
        position: absolute
        z-index: 10
        display: none
        @media(min-width: 768px)
            display: block
            background: $bg-overlay
    .additional-window
        padding: 24px 0
        @media(min-width: 768px)
            background: #fff
            border-radius: 12px
            padding: 32px 32px 32px 32px
        .images
            position: relative
            min-height: 300px
            @media(min-width: 768px)
                min-height: 350px
            li
                position: absolute
                height: 100%
                width: 100%
        .prod-additionals
            li
                position: relative
                overflow: hidden
                img
                    max-height: 35px
                    @media(min-width: 768px)
                        max-height: 45px
                .disable              
                    height: 100%
                    width: 100%
                    background: url('/images/additionals/disabled.png')
                    background-position: 0 0 !important
                    background-size: cover !important
                    position: absolute
                    top: 0
                    left: 0
</style>