<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage';
import { localStore } from '../../stores/localStore';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Pagination, Navigation, Autoplay } from 'swiper/modules';

export default {
    components: {
        Swiper,
        SwiperSlide
    },
    computed: {
        ...mapStores(appStore, localStore)
    },
    data() {
        return {
            prodAdditional: {},
            prodImg: [],
            imgIndex: 0,
            modules: [Pagination, Navigation]
        };
    },
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
                    };
                } else {
                    this.prodAdditional = {};
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
};
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
                                            <swiper
                                                :modules="modules"
                                                pagination
                                                navigation
                                                space-between="10"
                                                slides-per-view="1"
                                                :autoplay="{
                                                    delay: 5000,
                                                }"
                                                :loop="appStore.currentAdditional.images.length > 1"
                                            >
                                                <swiper-slide v-for="(image, index) in appStore.currentAdditional.images" :key="index" class="rounded overflow-hidden">
                                                    <img :src="image" class="img-fluid w-100" />
                                                </swiper-slide>
                                            </swiper>
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
                                            <li v-for="(value, key) in prodAdditional" :key="key" style="margin-right: 6px;">
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
                                                <div class="footer-btns">
                                                    <transition
                                                        enter-active-class="animate__animated animate__fadeIn"
                                                        leave-active-class="animate__animated animate__fadeOut"
                                                        mode="out-in"
                                                    >
                                                        <button class="btn rounded cart" @click="localStore.manageStore('cart', appStore.currentAdditional)" v-if="!localStore.checkExist('cart', appStore.currentAdditional)">
                                                            <i class="fa fa-shopping-cart" style="margin-right: 8px;"></i>
                                                        В корзину
                                                    </button>
                                                    <div class="prod-qty" v-else>
                                                        <button class="btn rounded" @click="localStore.manageQty(false, appStore.currentAdditional)">-</button>
                                                        <input type="text" class="form-control" :value="appStore.currentAdditional.qty">
                                                        <button class="btn rounded" @click="localStore.manageQty(true, appStore.currentAdditional)">+</button>
                                                    </div>
                                                    </transition>
                                                    <button class="btn rounded fav" @click="localStore.manageStore('fav', appStore.currentAdditional)" :class="{'active' : localStore.checkExist('fav', appStore.currentAdditional)}">
                                                        <i class="fa fa-heart-o" v-if="!localStore.checkExist('fav', appStore.currentAdditional)"></i>
                                                        <i class="fa fa-heart" v-if="localStore.checkExist('fav', appStore.currentAdditional)" ></i>
                                                    </button>
                                                </div>
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
.prod-qty
    margin-right: 10px
    display: flex
    align-items: center
    button
        border: $color-main 1px solid
        background: $color-main
        color: #fff
        margin-right: 8px
        transition: all .3s
        &:first-child
            border-radius: 12px 0 0 12px !important
            margin: 0
        &:last-child
            border-radius: 0 12px 12px 0 !important
        &:hover
            background: $color-main
            color: #fff
    .form-control
        border-radius: 0
        border-left: none
        border-right: none
        width: 80px
        height: 100%
        display: flex
        align-items: center
        justify-content: center
        text-align: center
.footer-btns
    display: flex
    button
        border: #dedede 1px solid
        margin-right: 8px
        transition: all .3s
        &:last-child
            margin-right: 0
        &.active
            background: $color-main
            border-color: $color-main
            color: #fff
            &.fav
                background: red
                color: #fff
                border-color: red
        &:hover
            background: $color-main
            border-color: $color-main
            color: #fff
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