<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage'
import { localStore } from '../../stores/localStore';
export default {
    computed: {
        ...mapStores( appStore, localStore )
    },
    mounted() {
        if (appStore.currentAdditional !== null) {   
            this.prodAdditional = {
                onion: this.appStore.currentAdditional.onion,
                garlic: this.appStore.currentAdditional.garlic,
                kidsAllow: this.appStore.currentAdditional.kidsAllow,
                hit: this.appStore.currentAdditional.hit,
                spicy: this.appStore.currentAdditional.spicy
            }
        }
    },
    data: () => ({
        prodAdditional: {},
        prodImg: [],
        imgIndex: 0
    }),
    watch: {
        'appStore.additional': {
            handler(val) {
                if (val) { document.body.classList.add('overflow-hidden') }
                else { document.body.classList.remove('overflow-hidden') }
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
                <div class="container position-relative">
                    <div class="additional-window">
                        <div class="row mb-4">
                            <div class="col">
                                <h4 class="fw-semibold mb-0">{{ appStore.currentAdditional.name }}</h4>
                            </div>
                            <div class="col text-end">
                                <button class="btn-close" @click="appStore.additional = !appStore.additional; appStore.currentAdditional = null"></button>
                            </div>
                        </div>
                        <div class="row-cols-1">
                            <div class="col mb-4 mb-lg-0">
                                <transition-group
                                    tag="ul"
                                    class="images list-unstyled"
                                    enter-active-class="animate__animated animate__fadeIn"
                                    leave-active-class="animate__animated animate__fadeOut"
                                >
                                    <li 
                                        class="bg-image rounded"
                                        style="min-height: 200px;"
                                        v-for="e in appStore.currentAdditional.images"
                                        :style="'background: url(' + e + ')'"
                                        :key="e"
                                        v-show="Array.from(appStore.currentAdditional.images).indexOf(e) == imgIndex"
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
                            <div class="col">
                                <ul class="list-unstyled d-flex align-items-center">
                                    <li v-for="e in prodAdditional" :key="e" style="margin-right: 6px;">{{ e }}</li>
                                </ul>
                                <p><b>Состав:</b> {{ appStore.currentAdditional.consist }} </p>
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
    .overlay
        position: absolute
        z-index: 1
        display: none
        @media(min-width: 768px)
            display: block
    .additional-window
        padding: 24px 0
        .images
            position: relative
            min-height: 200px
            li
                position: absolute
                height: 100%
                width: 100%
</style>