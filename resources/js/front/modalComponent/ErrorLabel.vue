<script>

export default {
    props: {
        errorBag: Object,
        name: String
    }
}
</script>

<template>

    <transition
        enter-active-class="animate__animated animate__fadeIn" 
        leave-active-class="animate__animated animate__fadeOut" 
        mode="out-in"
    >
        <div class="d-inline text-danger mx-2" v-if="errorBag[name]"> 
            <div class="d-flex align-items-center position-relative">
                {{ errorBag[name] }}  <i class="fa fa-arrow-down"></i>
            </div> 
        </div>
    </transition>

</template>

<style lang="sass" scoped>
@keyframes slide 
    0%
        top: 3px 
    50%
        top: 7px    
    100%
        top: 3px

.position-relative  
    i
        position: absolute
        right: -20px
        animation: slide infinite 1s ease-in-out
</style>