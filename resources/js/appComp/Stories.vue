<script>
export default{
    props:{
        stories: Object
    },
    mounted(){},
    data:()=>({
        openStory: false,
        overlay: false,
        current: null
    }),
    methods:{
        storyState(action){
            if (action == 'open'){
                this.openStory = true
                setTimeout(() => {
                    this.overlay = true
                }, 100)
                if (!document.body.classList.contains('overflow-hidden')){
                    document.body.classList.add('overflow-hidden')
                }
            }   
            if (action == 'close'){
                this.overlay = false
                this.current = null
                setTimeout(() => {
                    this.openStory = false
                }, 300)
                if (document.body.classList.contains('overflow-hidden')){
                    document.body.classList.remove('overflow-hidden')
                }
            }
        }
    }
}
</script>

<template>
    <div class="container">
        <div class="row">
            <div class="col-12" v-if="stories.length">
                <ul class="list-unstyled p-0 m-0 stories">
                    <li v-for="story in stories" class="cursor-pointer" :key="story" @click="()=>{storyState('open'); current = story.image}">
                        <div class="story bg-image" :style="'background: url('+ story.thumb +')'"></div>
                        <h4 class="mb-0 mt-2">{{ story.name }}</h4>
                    </li>
                </ul>
            </div>

            <div class="col-12" v-else>
                <ul class="list-unstyled p-0 m-0 stories placeholder-glow">
                    <li><div class="story placeholder bg-dark"></div></li>
                    <li><div class="story placeholder bg-dark"></div></li>
                    <li><div class="story placeholder bg-dark"></div></li>
                    <li><div class="story placeholder bg-dark"></div></li>
                </ul>
            </div>

        </div>
    </div>

    <div v-if="openStory" class="story-wrap">
    <transition
        enter-active-class="animate__animated animate__fadeIn"
        leave-active-class="animate__animated animate__fadeOut"
    >
        <div class="overlay" v-if="overlay" @click="storyState('close')"></div>
    </transition>
        <div class="d-flex justify-content-center align-items-center h-100 position-relative invisible">
            <div class="overflow-hidden rounded" style="z-index: 10;height: -webkit-fill-avaliable;">
                <img :src="current" alt="" class="rounded visible w-100" >
            </div>
        </div>
    </div>
</template>

<style lang="sass">
.story-wrap
    position: fixed
    top: 0
    left: 0
    height: 100%
    width: 100%
    padding: 24px
    z-index: 20
</style>