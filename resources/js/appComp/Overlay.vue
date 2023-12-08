<script>
export default{
    props:{
        overlay: Boolean
    },
    mounted(){
        if (!document.body.classList.contains('overflow-hidden')) {
            document.body.classList.add('overflow-hidden')
        }
    },
    beforeUnmount(){
        if (document.body.classList.contains('overflow-hidden')) {
            document.body.classList.remove('overflow-hidden')
        }
    }
}
</script>

<template>
    <transition
        enter-active-class="animate__animated animate__fadeIn"
        leave-active-class="animate__animated animate__fadeOut"
    >
        <div class="overlay" v-if="overlay"></div>
    </transition>
</template>

<style lang="sass">
.overlay
    position: fixed
    top: 0
    left: 0
    width: 100%
    height: 100%
    background: $bg-overlay    
</style>