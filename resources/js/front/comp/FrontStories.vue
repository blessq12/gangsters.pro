<script>
import { mapStores } from 'pinia'
import { appStore } from '../../stores/appStorage';

export default {
    props: {
        'stories' : Object
    },
    data: () => ({
        show: false,
        currentStory: null,
        timer: null,
        progress: 0, // Add progress data property
        isPaused: false // Add isPaused data property
    }),
    computed: {
        storyImage(story){
            return '/uploads/' + story.image;
        },
        ...mapStores(appStore)
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.startTimer();
                document.body.style.overflow = 'hidden';
            } else {
                this.clearTimer();
                document.body.style.overflow = 'auto';
            }
        }
    },
    methods: {
        startTimer() {
            this.clearTimer();
            this.progress = 0; // Reset progress
            const interval = 100; // Update every 150ms
            const duration = 10000; // 10 seconds
            const step = interval / duration * 100; // Calculate step size

            this.timer = setInterval(() => {
                if (!this.isPaused) {
                    this.progress += step;
                    if (this.progress >= 100) {
                        this.show = false;
                        this.currentStory = null;
                        this.clearTimer();
                    }
                }
            }, interval);
        },
        clearTimer() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },
        pauseTimer() {
            this.isPaused = true;
        },
        resumeTimer() {
            this.isPaused = false;
        }
    },
    beforeDestroy() {
        this.clearTimer();
    }
}
</script>

<template>
    
    <ul class="story-list pt-2" v-if="stories.length">
        <li v-for="story in stories" :key="story.id" class="d-block text-center" >
            <div class="story-item rounded bg-image" :style="'background:url(' + story.thumb + ');'" @click="show = !show; currentStory = story">
            </div>
            <div class="story-footer">
                <span>{{ story.name }}</span>
            </div>
        </li>
    </ul>
    <ul class="story-list" v-else>
        <li v-for="e in 5" class="placeholder-glow d-block text-center">
            <div class="story-item rounded bg-image placeholder" style="width: 160px;height: 200px;">
            </div>
            <div class="story-footer-placeholder py-2 placeholder d-block rounded">
            </div>
        </li>
    </ul>
    <teleport to="body">
        <transition
            enter-active-class="animate__animated animate__fadeIn"
            leave-active-class="animate__animated animate__fadeOut"
            mode="out-in"
        >
            <div class="overlay" v-if="appStore.modal"></div>
        </transition>
        <transition
            enter-active-class="animate__animated animate__fadeIn"
            leave-active-class="animate__animated animate__fadeOut"
            mode="out-in"
        >
            <div class="wrap" v-if="show">
                
                <div 
                    class="story-content rounded bg-image" 
                    @mousedown="pauseTimer" 
                    @mouseup="resumeTimer" 
                    @touchstart="pauseTimer"
                    @touchend="resumeTimer"
                    :style="{ backgroundImage: 'url(' + currentStory.image + ')' }"
                >
                <div class="d-flex align-items-center justify-content-between interactive">
                    <div class="progress-bar">
                        <div class="progress" :style="{ width: progress + '%' }"></div>
                    </div>
                    <button class="btn-close" @click="show=!show"></button>
                </div>
                </div>
                <div class="overlay" @click="show=!show"></div>
            </div>
        </transition>
    </teleport>
</template>

<style lang="sass" scoped>
.overlay
    background: rgba(0, 0, 0, .6)
    position: fixed
.progress-bar
    width: 90%
    height: 5px
    background: rgba(255, 255, 255, 0.3)
    border-radius: 5px
    overflow: hidden
    .progress
        height: 100%
        background: #fff
        transition: all .3s
.wrap
    position: fixed
    display: flex
    align-items: center
    justify-content: center
    width: 100%
    height: 100%
    top: 0
    left: 0
    padding: 12px
    z-index: 10
    .interactive
        padding: 12px
        position: absolute
        width: 100%
    .story-content
        display: flex
        justify-content: center
        height: 100vh
        max-height: 85vh
        @media(min-width: 768px)
            max-height: 95%
        width: 450px
        overflow: hidden
        position: relative
        z-index: 11
    .overlay
        z-index: 9
.story-list
    display: flex
    justify-content: start
    align-items: center
    overflow: visible
    li
        transition: all .3s
        &:hover
            transform: scale(1.02)
    .story-item
        position: relative
        transition: transform .3s
        &::before
            content: ''
            display: block
            width: 105%
            height: 105%
            border-radius: 20px
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888)
            opacity: .6
            position: absolute
            top: 50%
            left: 50%
            transform: translate(-50%, -50%)
            z-index: -1
.story-footer
    position: relative
    z-index: 1
    margin-top: 12px
    span
        font-weight: 600
        text-transform: capitalize
.story-footer-placeholder
    position: relative
    margin-top: 12px
    height: 25px
    &::before
        content: ''
        display: block
        width: 100%
        height: 100%
        border-radius: 20px
        background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888)
        position: absolute
        top: 0
        left: 0
        z-index: -1
</style>
