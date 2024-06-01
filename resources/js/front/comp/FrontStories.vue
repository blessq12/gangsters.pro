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
            } else {
                this.clearTimer();
            }
        }
    },
    methods: {
        startTimer() {
            this.clearTimer();
            this.progress = 0; // Reset progress
            const interval = 150; // Update every 150ms
            const duration = 15000; // 15 seconds
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
                <div class="story-viewer" style="display: flex; justify-content: center; align-items: center; height: 100%;">
                    <div class="story-content" @mousedown="pauseTimer" @mouseup="resumeTimer" @touchstart="pauseTimer" @touchend="resumeTimer">
                        <img :src="currentStory.image" alt="Story Image" style="width: 100%; border-radius: 10px; position: relative;">
                        <div class="progress-bar" style="position: absolute; top: 0; left: 0; width: 100%;">
                            <div class="progress" :style="{ width: progress + '%' }"></div>
                        </div>
                    </div>
                </div>
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
    .story-content
        padding: 8px
        @media (min-width: 768px)
            padding: 24px
        .progress-bar
            width: 100%
            height: 5px
            background: rgba(255, 255, 255, 0.3)
            border-radius: 5px
            overflow: hidden
            margin-top: 10px
            .progress
                height: 100%
                background: #fff
                transition: all .3s
</style>
