<template>
    <div class="mx-auto max-w-7xl px-4 md:px-6 mt-12 mb-12">
        <ul
            class="flex items-center overflow-x-auto pb-12 pt-2 scrollbar-hide"
            v-if="sortedStories.length"
            ref="storyItems"
        >
            <StoryItem
                v-for="(story, index) in sortedStories"
                :key="story.id"
                :story="story"
                :index="index"
                :is-viewed="viewedStories.includes(story.id)"
                @select="openStory"
            />
        </ul>
        <ul class="flex items-center" v-else>
            <li
                v-for="e in 5"
                :key="e"
                class="placeholder-glow block text-center"
            >
                <div
                    class="w-[120px] h-[200px] rounded bg-gray-200 animate-pulse"
                ></div>
                <div
                    class="mt-3 h-6 w-full rounded bg-gradient-to-r from-[#f09433] via-[#e6683c] to-[#dc2743] animate-pulse"
                ></div>
            </li>
        </ul>

        <StoryModal
            v-if="show"
            :story="currentStory"
            :progress="progress"
            @close="closeStory"
            @pause="pauseTimer"
            @resume="resumeTimer"
            @prev="prevStory"
            @next="nextStory"
        />
    </div>
</template>

<script>
import gsap from "gsap";
import { mapStores } from "pinia";
import { defineComponent } from "vue";
import { appStore } from "../../stores/appStorage";
import StoryItem from "./StoryItem.vue";
import StoryModal from "./StoryModal.vue";

export default defineComponent({
    name: "FrontStories",
    components: {
        StoryItem,
        StoryModal,
    },
    props: {
        stories: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            show: false,
            currentStory: null,
            progress: 0,
            isPaused: false,
            debugMode: false,
            viewedStories: [],
        };
    },
    computed: {
        sortedStories() {
            return [...this.stories].sort((a, b) => {
                const aViewed = this.viewedStories.includes(a.id);
                const bViewed = this.viewedStories.includes(b.id);
                return aViewed - bViewed;
            });
        },
        currentStoryIndex() {
            return this.currentStory
                ? this.sortedStories.findIndex(
                      (story) => story.id === this.currentStory.id
                  )
                : -1;
        },
        ...mapStores(appStore),
    },
    methods: {
        openStory(story) {
            this.currentStory = story;
            this.show = true;
            this.markStoryAsViewed(story.id);
            document.body.style.overflow = "hidden";
            this.startTimer();
        },
        closeStory() {
            this.show = false;
            this.currentStory = null;
            document.body.style.overflow = "auto";
            this.clearTimer();
        },
        markStoryAsViewed(storyId) {
            this.viewedStories = [...new Set([...this.viewedStories, storyId])];
            localStorage.setItem(
                "viewedStories",
                JSON.stringify(this.viewedStories)
            );
        },
        startTimer() {
            if (this.debugMode) return;

            this.clearTimer();
            this.progress = 0;
            const duration = 10000;

            gsap.to(this, {
                progress: 100,
                duration: duration / 1000,
                ease: "linear",
                onUpdate: () => {
                    if (this.isPaused)
                        gsap.to(this, { progress: this.progress });
                },
                onComplete: () => this.closeStory(),
            });
        },
        clearTimer() {
            gsap.killTweensOf(this);
        },
        pauseTimer() {
            this.isPaused = true;
        },
        resumeTimer() {
            this.isPaused = false;
        },
        initializeViewedStories() {
            const saved = localStorage.getItem("viewedStories");
            if (saved) {
                this.viewedStories = JSON.parse(saved);
            }
        },
        animateStoryItems() {
            gsap.from(this.$refs.storyItems?.children || [], {
                scale: 0.8,
                opacity: 0,
                stagger: 0.1,
                duration: 0.8,
                ease: "back.out(1.7)",
            });
        },
        prevStory() {
            const prevIndex = this.currentStoryIndex - 1;
            if (prevIndex >= 0) {
                this.currentStory = this.sortedStories[prevIndex];
                this.markStoryAsViewed(this.currentStory.id);
                this.startTimer();
            }
        },
        nextStory() {
            const nextIndex = this.currentStoryIndex + 1;
            if (nextIndex < this.sortedStories.length) {
                this.currentStory = this.sortedStories[nextIndex];
                this.markStoryAsViewed(this.currentStory.id);
                this.startTimer();
            } else {
                this.closeStory();
            }
        },
    },
    mounted() {
        this.initializeViewedStories();
        this.$nextTick(this.animateStoryItems);
    },
    beforeUnmount() {
        this.clearTimer();
    },
});
</script>

<style lang="scss" scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
    &::-webkit-scrollbar {
        display: none;
    }
}

@media (max-width: 640px) {
    h1 {
        font-size: 1.125rem; /* 18px */
        line-height: 1.5;
    }

    p {
        font-size: 0.875rem; /* 14px */
        line-height: 1.4;
    }
}

/* Улучшенный контраст текста */
.text-white {
    color: rgba(255, 255, 255, 0.95);
}

.text-neutral-900 {
    color: rgba(23, 23, 23, 1);
}
</style>
