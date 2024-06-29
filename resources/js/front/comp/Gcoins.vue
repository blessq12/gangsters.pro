<script>
import { appStore } from '../../stores/appStorage'
import { mapStores } from 'pinia'

export default {
   computed: {
        ...mapStores(appStore),
    },
    methods: {
    hideWelcome() {
        this.appStore.welcome = false;
        localStorage.setItem('welcome', 'false');
    }
    }
};
</script>

<template>
    <transition 
        appear-active-class="animate__animated animate__fadeIn"
        leave-active-class="animate__animated animate__fadeOutUp"
        mode="out-in"
    >
        <div class="container" v-if="appStore.welcome">
            <div class="row ">
                <div class="col">
                    <div class="wrapper ">
                        <div class="row">
                            <div class="col d-none d-lg-block"></div>
                            <div class="col-12 col-lg-8 position-relative">
                                <div class="d-flex justify-content-between">
                                    <h2>Gangster's Coins - наша валюта</h2>
                                    <i class="fa fa-times fa-2x" style="color: white;cursor: pointer;" @click="hideWelcome()"></i>
                                </div>
                                <p class="mb-3">
                                    Gangster's Coins - это наша уникальная валюта, которая позволяет вам получать кэшбэк с каждого заказа. 
                                    Чем больше вы заказываете, тем больше монет вы зарабатываете. 
                                    Используйте накопленные монеты для получения скидок на будущие заказы и наслаждайтесь нашими вкусными блюдами еще больше!
                                </p>
                                <small class="mb-3 d-block">
                                    *Для получения монет требуется регистрация на сайте.
                                </small>
                                <button class="w-100 justify-content-center justify-content-lg-start btn btn-cta" @click="appStore.modal = true; appStore.modalName = 'user'">
                                    Получить монеты
                                </button>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>

</template>

<style scoped lang="sass">
.btn-cta
    background: transparent
    border: 1px solid #fff !important
    color: #fff !important
    border: none
    padding: 12px 24px
    border-radius: 16px
    font-size: 16px
    font-weight: 600
    position: relative
    display: flex
    align-items: center
    transition: all 0.3s ease
    @media(min-width: 768px)
        width: fit-content !important
    &::after
        content: '\f061'
        font-family: 'Font Awesome 5 Free'
        font-weight: 900
        display: block
        margin-left: -20px
        opacity: 0
        transition: all 0.3s ease
    &:hover
        background: #fff
        color: $color-main !important
    &:hover::after
        margin-left: 6px
        opacity: 1
.wrapper
    margin-top: 24px
    background: $color-main
    padding: 22px 12 !important
    color: #fff 
    padding: 20px
    border-radius: 16px
    box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.1)
    position: relative
    overflow: hidden
    &::before
        content: ''
        position: absolute
        top: 0
        left: 0
        width: 100%
        height: 100%
        background-image: url('/images/placeholder/falling-gold-coins-money-png.png')
        background-size: cover
        background-position: center
        background-repeat: no-repeat
        z-index: 0
        opacity: 0.5
</style>
