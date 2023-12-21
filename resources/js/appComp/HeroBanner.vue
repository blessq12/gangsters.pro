<script>
export default{
    props:{
        banners: Object
    },
    data:()=>({
        current: 1,
        direction: null
    }),
    methods:{
        slide(dir){
            console.log(dir)
            if (dir){
                this.direction = true
                if(this.current < this.banners.length -1 ){
                    this.current++
                } else {
                    this.current = 1 
                }
            }

            else {
                this.direction = false
                if (this.current !== 1){
                    this.current-- 
                } else {
                    this.current = this.banners.length - 1
                }
            }
            setTimeout(() => {
                this.direction = null
            }, 100);
        }
    }
}
</script>

<template>
    <div class="container">
        <div class="row">

            <div class="col-12" v-if="banners.length">
                <div class="hero-wrap position-relative d-flex align-items-center">
                    <div class="position-absolute d-flex justify-content-between w-100 nav" style="z-index: 11;">
                        <button class="btn" @click="slide(false)">
                            <i class="fa fa-arrow-left text-light"></i>
                        </button>
                        <button class="btn" @click="slide(true)">
                            <i class="fa fa-arrow-right text-light"></i>
                        </button>
                    </div>
                   <transition-group
                        :enter-active-class="direction ? 'animate__animated animate__fadeInRight' : 'animate__animated animate__fadeInLeft'"
                        :leave-active-class="direction ? 'animate__animated animate__fadeOutLeft' : 'animate__animated animate__fadeOutRight'"
                   >
                        <div 
                            v-show="current == banner.id"
                            class="bg-image h-100 w-100 img position-absolute top-0 left-0" 
                            v-for="banner in banners" 
                            :key="banner.id" 
                            :style="'background: url('+ banner.image.path +')'"
                        >
                        <div class="position-absolute top-0 w-100 h-100 hero-overlay"></div>
                            <div class="h-100 w-100 d-flex align-items-end position-relative text-light" style="z-index: 1;">
                                <div>
                                    <h4>{{ banner.header }}</h4>
                                    <p v-if="banner.subheader !== null">
                                        {{ banner.subheader }}
                                    </p>
                                </div>
                            </div>
                        </div>
                   </transition-group>
                </div>
            </div>

            <div class="col-12 placeholder-glow" v-else>
                <div class="hero-wrap position-relative placeholder p-4 d-flex align-items-end">
                    <div class="placegolder-glow w-100">
                        <h2 class="placeholder col-4 bg-light"></h2>
                        <br>
                        <p class="placeholder col-7 bg-light"></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style lang="sass">

</style>