<script>
export default {
    props: {
        'banners' : Object
    },
    data: () => ({
        current: 0,
        direction: true
    }),
    watch: {
        current(val, oldval) {
            if (val > oldval) {
                this.direction = true
            } else {
                this.direction = false
            }
            if (val < 0) {
                this.current = this.banners.length - 1
            } else if (val > this.banners.length - 1) {
                this.current = 0
            }
        }
    }
}
</script>

<template>
    <div class="banner-wrap rounded" v-if="banners.length">
        <transition-group
            :enter-active-class="`animate__animated animate__slideIn${ direction ? 'Right' : 'Left' }`"
            :leave-active-class="`animate__animated animate__slideOut${ direction ? 'Left' : 'Right' }`"
        >
            <div 
                class="banner-item bg-image" 
                v-for="e in banners" 
                :key="e.id"
                v-show=" banners.indexOf(e) === current"
                :style="'background: url( /uploads/' + e.image + ' )'"
            >
            </div>
        </transition-group>
        <div class="banner-nav">
            <button type="button" class="btn" @click="current--">
                <i class="fa fa-arrow-left"></i>
            </button>
            <button type="button" class="btn" @click="current++">
                <i class="fa fa-arrow-right"></i>
            </button>
        </div>
    </div>
    <div class="banner-wrap rounded placeholder-glow" v-else>
        <div class="banner-item bg-image placeholder" style="height: 100%; width: 100%;"></div>
    </div>
</template>

<style lang="sass" scoped>

</style>