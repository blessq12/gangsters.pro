<script>
export default{
    emits:['close'],
    props:{
        comp: String
    },
    mounted(){
        this.overlay = true
        setTimeout(()=>{
            this.show = true
        },200)
    },
    data:()=>({
        overlay: false,
        show: false
    }),
    methods:{
        close(){
            this.show = false
            this.overlay = false
            setTimeout(() => {
                this.$emit('close')
            }, 200);
        }
    }
}
</script>

<template>
    <overlay :overlay="overlay" @click="close"></overlay>
<div class="m-wrap">
        <transition 
        enter-active-class="animate__animated animate__zoomIn"
        leave-active-class="animate__animated animate__zoomOut"
        mode="out-in"
        >
            <div class="m-win" v-if="show">
                <component :is="comp" @close="close"></component>
            </div>
        </transition>
</div>
</template>

<style lang="sass">

</style>