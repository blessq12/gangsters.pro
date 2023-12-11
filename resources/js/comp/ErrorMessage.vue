<script>
export default{
    props:{
        name: String,
        errors: Object,
        class: String
    }
}
</script>

<template>
    <transition
        enter-active-class="animate__animated animate__fadeIn"
        leave-active-class="animate__animated animate__fadeOut"
    >
        <span v-if="errors[name]" :class="class">
            {{ errors[name] }}
        </span>
    </transition>
</template>

<style lang="sass" scoped>
span
    font-size: .7rem
</style>