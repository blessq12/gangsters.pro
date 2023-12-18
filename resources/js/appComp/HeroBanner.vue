<script>
export default{
    props:{
        banners: Object
    },
    data:()=>({
        current: 1
    })
}
</script>

<template>
    <div class="container">
        <div class="row">

            <div class="col-12" v-if="banners.length">
                <div class="hero-wrap position-relative">
                    
                   <transition-group
                        enter-active-class="animate__animated animate__fadeInRight"
                        leave-active-class="animate__animated animate__fadeOutLeft"
                   >
                        <div 
                            v-show="current == banner.id"
                            class="bg-image h-100 w-100 py-3 px-4 position-absolute top-0 left-0" 
                            v-for="banner in banners" 
                            :key="banner.id" 
                            :style="'background: url('+ banner.image.path +')'"
                        >
                        <div class="position-absolute top-0 w-100 h-100" style="background: rgba(0,0,0,.6);z-index: 1; left: 0;"></div>
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
                <button @click="current--">prev</button>
                <button class="mx-4" @click="current++">next</button>
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