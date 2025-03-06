<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage';

export default {
    props: {
        stories: Object
    },
    data() {
        return {
            show: false,
            currentStory: null,
            timer: null,
            progress: 0,
            isPaused: false,
            debugMode: false // Set this to true to prevent the timer for debugging
        };
    },
    computed: {
        storyImage(story) {
            return `/uploads/${story.image}`;
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
            if (this.debugMode) return; // Prevent timer if in debug mode

            this.clearTimer();
            this.progress = 0;
            const interval = 100;
            const duration = 10000;
            const step = (interval / duration) * 100;

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
};
</script>

<template>
    <ul class="story-list pt-2" v-if="!stories.length">
        <li class="d-block text-center">
            <service-advertisment></service-advertisment>
        </li>
        <li v-for="story in stories" :key="story.id" class="d-block text-center">
            <div class="story-wrap">
                <div class="story-item rounded bg-image" :style="'background:url(' + story.thumb + ');'" @click="show = !show; currentStory = story"></div>
            </div>
            <div class="story-footer">
                <span>{{ story.name }}</span>
            </div>
        </li>
    </ul>
    <ul class="story-list" v-else>
        <li v-for="e in 5" class="placeholder-glow d-block text-center">
            <div class="story-item rounded bg-image placeholder" style="width: 160px; height: 200px;"></div>
            <div class="story-footer-placeholder py-2 placeholder d-block rounded"></div>
        </li>
    </ul>
    <teleport to="body">
        <transition enter-active-class="animate__animated animate__fadeIn" leave-active-class="animate__animated animate__fadeOut" mode="out-in">
            <div class="overlay" v-if="appStore.modal"></div>
        </transition>
        <transition enter-active-class="animate__animated animate__fadeIn" leave-active-class="animate__animated animate__fadeOut" mode="out-in">
            <div class="wrap" v-if="show">
                <div class="story-content" @mousedown="pauseTimer" @mouseup="resumeTimer" @touchstart="pauseTimer" @touchend="resumeTimer">
                    <div class="story-image position-relative overflow-hidden h-100" style="z-index: 11; max-width: 400px;">
                        <img :src="currentStory?.image" class="h-100 w-100 rounded" style="object-fit: contain; position: relative;">
                        <div class="control-bar">
                            <div class="progress-bar">
                                <div class="progress" :style="{ width: progress + '%' }"></div>
                            </div>
                            <div class="btn-holder">
                                <button @click="show = false" class="">
                                    &times;
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="overlay" @click="show = !show"></div>
            </div>
        </transition>
    </teleport>
</template>

<style lang="sass" scoped>
.control-bar
    display: flex
    align-items: center
    justify-content: center !important
    position: absolute
    top: 20px
    left: 0
    width: 100% !important
    height: 5px
.progress-bar
    width: 80% !important
    background: rgba(255, 255, 255, 0.2)
    height: 5px
.btn-holder
    padding: 0 0 0 15px
    display: flex
    align-items: center
    button
        background-color: transparent
        border: 0
        border-radius: 50%
        color: #fff
        padding: 0
        display: flex
        align-items: center
        justify-content: center
        font-size: 1.5rem !important
        font: inherit
        cursor: pointer
        outline: inherit
.story-content
    position: relative
.overlay
    background: rgba(0, 0, 0, 0.6)
    position: fixed
    z-index: 10
.story-image
    img
        max-width: 400px !important
        width: 100%
.wrap
    display: flex
    align-items: center
    justify-content: center
    width: 100%
    height: 100%
    position: fixed
    top: 0
    left: 0
    padding: 12px
    z-index: 10

.story-list
    display: flex
    align-items: center
    overflow: hidden
    overflow-x: scroll
    padding: 0
    padding-bottom: 48px
    margin: 0
    list-style: none

    &::-webkit-scrollbar
        display: none

    li
        cursor: pointer
        height: 95px
        min-width: 160px
        margin-right: 8px

        @media (min-width: 768px)
            height: 120px
            min-width: 220px
            margin-right: 12px

        transition: all 0.3s

        &:hover
            transform: scale(1.02)

        .story-wrap
            width: 100%
            height: 100%
            position: relative
            padding: 3px 3px
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888)
            border-radius: 18px

            .story-item
                height: 100%
                width: 100%
                position: relative
                transition: transform 0.3s

.story-footer
    position: relative
    z-index: 1
    margin-top: 6px
    text-align: start
    padding: 0 8px

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

.progress
    background-color: #ff0000
    transition: all .3s
</style>
