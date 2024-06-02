<script>
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
        }
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
    
    <ul class="story-list" v-if="stories.length">
        <li v-for="story in stories" :key="story.id" class="bg-primary d-block text-center">
            <div class="story-item rounded bg-image" :style="'background:url(' + story.thumb + ')'" @click="show = !show; currentStory = story">
            </div>
            <div class="story-footer py-2 text-light">
                {{ story.name }}
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
    .interactive
        padding: 12px
        position: absolute
        width: 100%

    .story-content
        display: flex
        justify-content: center
        height: 100vh
        max-height: 95vh
        width: 450px
        overflow: hidden
        position: relative
        z-index: 11
    .overlay
        z-index: 9
</style>
