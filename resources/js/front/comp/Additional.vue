<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage'
export default {
    computed: {
        ...mapStores( appStore )
    },
    watch: {
        'appStore.additional': {
            handler(val) {
                if (val){ document.body.classList.add('overflow-hidden') } else { document.body.classList.remove('overflow-hidden') }
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
                <div class="overlay" @click="appStore.additional = !appStore.additional"></div>
                <div class="container invisible position-relative">
                    <div class="row row-cols-1 row-cols-md-1 row-cols-lg-2 justify-content-center">
                        <div class="col">
                            <div class="additional visible bg-light p-5 rounded">
                                Подробнее о товаре
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
</style>