<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage'
import { localStore } from '../../stores/localStore';

export default {
    computed: {
        ...mapStores(appStore, localStore)
    },
    mounted() {
        // Component is mounted
    },
    updated() {
        // Component is updated
    },
    data: () => ({
        prodAdditional: {},
        prodImg: [],
        imgIndex: 0
    }),
    watch: {
        'appStore.additional': {
            handler(val) {
                document.body.classList.toggle('overflow-hidden', val);
                if (val) {
                    this.prodAdditional = {
                        hit: this.appStore.currentAdditional.hit,
                        spicy: this.appStore.currentAdditional.spicy,
                        kidsAllow: this.appStore.currentAdditional.kidsAllow,
                        garlic: this.appStore.currentAdditional.garlic,
                        onion: this.appStore.currentAdditional.onion,
                    }
                } else {
                    this.prodAdditional = {}
                }
                
            }
        },
        imgIndex(val) {
            const length = this.appStore.currentAdditional.images.length;
            if (val >= length) {
                this.imgIndex = 0;
            } else if (val < 0) {
                this.imgIndex = length - 1;
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
            <div class="wrap" v-if="appStore.additional">
                <div class="overlay" @click="appStore.additional = false"></div>
                <div class="container position-relative" style="z-index: 10;">
                    <div class="row justify-content-center">
                        <div class="col col-lg-10">
                            <div class="additional-window">
                                <div class="row row-cols-1 row-cols-md-2">
                                    <div class="col mb-4 mb-lg-0" v-if="appStore.currentAdditional.images.length">
                                        <div class="images-wrap">
                                            <transition-group
                                                tag="ul"
                                                class="images list-unstyled m-0 p-0"
                                                enter-active-class="animate__animated animate__fadeIn"
                                                leave-active-class="animate__animated animate__fadeOut"
                                            >
                                                <li 
                                                    class="bg-image rounded"
                                                    style="min-height: 200px;"
                                                    v-for="img in appStore.currentAdditional.thumbs"
                                                    :style="'background: url(' + img.large + ')'"
                                                    :key="img"
                                                    v-show="Array.from(appStore.currentAdditional.thumbs).indexOf(img) == imgIndex"
                                                >
                                                </li>
                                            </transition-group>
                                            <div class="nav-group h-100">
                                                <button 
                                                    class="h-100"
                                                    @click="imgIndex -= 1"
                                                >
                                                    <i class="fa fa-chevron-left"></i>
                                                </button>
                                                <button 
                                                    class="h-100"
                                                    @click="imgIndex += 1"
                                                >
                                                    <i class="fa fa-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col mb-4 mb-lg-0" v-else>
                                        <ul class="images list-unstyled">
                                            <li class="bg-image rounded" style="background: url('/images/placeholder/phldr-512.png'); background-size: contain !important;"></li>
                                        </ul>
                                    </div>
                                    <div class="col">
                                        <div class="row justify-content-between mb-2">
                                            <div class="col-10 d-flex align-items-center">
                                                <h5 class="mb-0">{{ appStore.currentAdditional.name }}</h5>
                                            </div>
                                            <div class="col-2 text-end">
                                                <button class="btn btn-close" @click="appStore.additional = false"></button>
                                            </div>
                                        </div>
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
                                                    @click="localStore.manageStore('cart', appStore.currentAdditional)"
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
                                                    @click="localStore.manageStore('fav', appStore.currentAdditional)"
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
    z-index: 11
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
        .images-wrap
            position: relative
            width: 100%
            .nav-group
                width: 100%
                position: absolute
                top: 50%
                transform: translateY(-50%)
                display: flex
                align-items: center
                justify-content: space-between
                button
                    background: transparent
                    border: none
                    color: #fff
                    width: 40px
                    cursor: pointer
                    &:first-child
                        background: linear-gradient(to right, rgba(255, 255, 255, .5), transparent)
                        border-radius: 16px 0 0 16px
                    &:last-child
                        background: linear-gradient(to left, rgba(255, 255, 255, .5), transparent)
                        border-radius: 0 16px 16px 0
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