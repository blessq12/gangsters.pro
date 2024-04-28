<script>
export default {
    props: {
        'stories' : Object
    },
    data: () => ({
        show: false
    }),
    computed: {
        storyImage(story){
            return '/uploads/' + story.image;
        }
    }

}
</script>

<template>
    <ul class="story-list" v-if="stories.length">
        <li v-for="story in stories" :key="story.id" class="bg-primary">
            <div class="story-item rounded bg-image" :style="'background:url(/uploads/' + story.image + ')'" @click="show = !show">
            </div>
        </li>
    </ul>
    <ul class="story-list" v-else>
        <li v-for="e in 5" class="placeholder-glow">
            <div class="story-item rounded bg-image placeholder" style="width: 160px;height: 200px;">
            </div>
        </li>
    </ul>
    <teleport to="body">
        <transition
            enter-active-class="animate__animated animate__fadeIn"
            leave-active-class="animate__animated animate__fadeOut"
            mode="out-in"
        >
            <div class="wrap" v-if="show" @click="show = !show">
            
            </div>
        </transition>
    </teleport>
</template>

<style lang="sass" scoped>
.wrap
    position: fixed
    width: 100%
    height: 100%
    top: 0
    left: 0
    background: rgba(0, 0, 0, .6)
</style>