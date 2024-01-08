<script>
export default{
    props:{
        links: Object
    },
    data:()=>({
        menuOpen: false
    })
}
</script>

<template>
    <button class="btn border border-dark d-lg-none" @click="menuOpen = !menuOpen">
        <i class="fa fa-bars"></i>
    </button>
    <transition 
        enter-active-class="animate__animated animate__fadeInRight"
        leave-active-class="animate__animated animate__fadeOutRight"
        mode="out-in"
    >
        <div v-if="menuOpen" class="position-fixed w-100 h-100 top-0 left-0 m-nav text-light" style="z-index: 10;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-12">
                        <div class="py-4 d-flex align-items-center justify-content-between border-bottom">
                            <h2 class="mb-0">Меню навигации</h2>
                            <button class="btn-close bg-light p-2" @click="menuOpen = !menuOpen"></button>
                        </div>
                    </div>
                    <div class="col-12 col-md-12">
                        <div class="py-4">
                            <ul class="list-unstyled p-0 m-0">
                                <a :href="item.url" v-for="item in links" :key="item.id" class="text-decoration-none btn btn-light w-100 mb-2">
                                    <li>
                                        {{ item.name }}
                                    </li>
                                </a>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style lang="sass" scoped>
.m-nav
    background: $color-dark
</style>