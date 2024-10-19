<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage';
import { localStore } from '../../stores/localStore';

export default {
    props: {
        links: {
            type: Object,
            required: false
        }
    },
    mounted() {
    },
    data: () => ({
        currentItem: null
    }),
    computed: {
        ...mapStores(appStore, localStore)
    },
    methods: {
        scrollToActiveItem() {
            if (this.currentItem) {
                const bo = this.$refs.backoverlay;
                const activeItem = this.$refs[this.currentItem];
                bo.style.maxWidth = activeItem.offsetWidth + 'px';
                bo.style.left = activeItem.offsetLeft + 'px';
            }
        }
    },
    watch: {
        'appStore.modal': function (val) {
            const bodyClassList = document.body.classList;
            val ? this.currentItem = this.appStore.modalName : null;
            val ? setTimeout(() => { this.scrollToActiveItem();}, 200) : null;
            val ? bodyClassList.add('overflow-hidden') : bodyClassList.remove('overflow-hidden');
        },
        'appStore.modalName': function (val) {
            this.currentItem = val;
            setTimeout(() => {
                this.scrollToActiveItem();
            }, 50);
        }
    }
}
</script>

<template>
    <teleport to="body">
        <transition
            enter-active-class="animate__animated animate__fadeIn"
            leave-active-class="animate__animated animate__fadeOut"
            mode="out-in"
        >
            <div class="overlay" v-if="appStore.modal"></div>
        </transition>

        <transition
            enter-active-class="animate__animated animate__slideInRight"
            leave-active-class="animate__animated animate__slideOutRight"
            mode="out-in"
        >    
            <div class="wrap" v-if="appStore.modal" @click.self="appStore.modal = false">
                <div class="menu py-4 px-2">
                    <div class="container">
                        <div class="row mb-4">
                            <div class="col d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">Меню</h4>
                                <button class="btn-close" @click.stop="appStore.modal = false"></button>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <ul class="list-unstyled  p-0 d-flex nav-icons" ref="menuItems">
                                    <div class="backoverlay" ref="backoverlay"></div>
                                    <li @click="appStore.modalName = 'cart'" :class="{ 'active': appStore.modalName === 'cart' }" ref="cart">
                                        <div class="counter" v-if="localStore.cart.length > 0">
                                            <span>{{ localStore.cart.length }}</span>
                                        </div>
                                        <i class="fa fa-shopping-cart"></i>
                                        <span>Корзина</span>
                                    </li>
                                    <li @click="appStore.modalName = 'fav'" :class="{ 'active': appStore.modalName === 'fav' }" ref="fav">
                                        <div class="counter" v-if="localStore.fav.length > 0">
                                            <span>{{ localStore.fav.length }}</span>
                                        </div>
                                        <i class="fa fa-heart"></i>
                                        <span>Избранное</span>
                                    </li>
                                    <li @click="appStore.modalName = 'user'" :class="{ 'active': appStore.modalName === 'user' }" ref="user">
                                        <i class="fa fa-user" ></i>
                                        <span>Профиль</span>
                                    </li>
                                    
                                    <li @click="appStore.modalName = 'menu'" :class="{ 'active': appStore.modalName === 'menu' }" ref="menu">
                                        <i class="fa fa-list-ul"></i>
                                        <span>Меню</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row overflow-auto" style="max-height: 100vh;">
                            <div class="col pb-5" style="min-height: 150vh;">
                                <component :is="appStore.modalName + '-component'"></component>
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
    align-items: center
    z-index: 10
    .menu
        position: fixed
        width: 100%
        @media(min-width: 768px)
            border-radius: 12px 0 0 12px
        box-shadow: 0 0 55px rgba(255, 255, 255, 0.3)
        @media(min-width: 768px)
            width: 50%
        height: 100%
        top: 0
        left: 0
        @media(min-width: 768px)
            left: unset
            right: 0 !important
        background: white
// menu styles
.menu
    .nav-icons
        overflow: auto
        position: relative
        gap: 10px
        .backoverlay
            position: absolute
            top: 0
            left: 0px
            height: 100%
            width: 100%
            max-width: 100px
            border-radius: 16px
            @media(min-width: 768px)
                max-width: 125px
            background: $color-main
            transition: all 0.3s
        li
            flex: 1
            display: flex
            align-items: center
            justify-content: center
            flex-direction: column
            gap: 5px
            padding: 10px
            color: $color-main
            position: relative
            cursor: pointer
            max-width: 100px
            @media(min-width: 768px)
                max-width: 125px
            &.active
                color: white
            i
                font-size: 20px
            .counter
                position: absolute
                top: 7px
                right: 7px
                width: 20px
                height: 20px
                border-radius: 50%
                background: red
                color: white
                display: flex
                align-items: center
                justify-content: center
                font-size: 12px
            
</style>