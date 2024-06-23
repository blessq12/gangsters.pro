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
    computed: {
        ...mapStores(appStore, localStore)
    },
    watch: {
        'appStore.modal': function(val) {
            const bodyClassList = document.body.classList;
            val ? bodyClassList.add('overflow-hidden') : bodyClassList.remove('overflow-hidden');
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
                                <h4 class="mb-0">
                                    <template v-if="appStore.modalName === 'cart'">Корзина</template>
                                    <template v-else-if="appStore.modalName === 'fav'">Избранное</template>
                                    <template v-else-if="appStore.modalName === 'user'">Пользователь</template>
                                </h4>
                                <button class="btn-close" @click.stop="appStore.modal = false"></button>
                            </div>
                        </div>
                        <div class="row overflow-auto" style="height: 90vh;">
                            <div class="col pb-5">
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
</style>