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
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <transition
                enter-active-class="animate__animated animate__zoomIn"
                leave-active-class="animate__animated animate__zoomOut"
                mode="out-in"
                >
                    <div class="mw" v-if="show">
                        <component :is="comp"></component>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</div>
</template>

<style lang="sass">

</style>