<script>
import gsap from "gsap";
import { mapStores } from "pinia";
import { appStore } from "../../stores/appStorage";

export default {
    props: {
        stories: Array,
    },
    data() {
        return {
            show: false,
            currentStory: null,
            timer: null,
            progress: 0,
            isPaused: false,
            debugMode: false,
            viewedStories: [],
        };
    },
    computed: {
        storyImage(story) {
            return `/uploads/${story.image}`;
        },
        sortedStories() {
            return [...this.stories].sort((a, b) => {
                const aViewed = this.viewedStories.includes(a.id);
                const bViewed = this.viewedStories.includes(b.id);
                return aViewed - bViewed;
            });
        },
        ...mapStores(appStore),
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.startTimer();
                document.body.style.overflow = "hidden";
                if (this.currentStory) {
                    this.viewedStories = [
                        ...new Set([
                            ...this.viewedStories,
                            this.currentStory.id,
                        ]),
                    ];
                    localStorage.setItem(
                        "viewedStories",
                        JSON.stringify(this.viewedStories)
                    );
                }

                this.$nextTick(() => {
                    gsap.fromTo(
                        this.$refs.storyContent,
                        { y: 100, opacity: 0 },
                        { y: 0, opacity: 1, duration: 0.5, ease: "power2.out" }
                    );
                });
            } else {
                this.clearTimer();
                document.body.style.overflow = "auto";
            }
        },
        currentStory(newStory) {
            if (newStory) this.adjustModalSize();
        },
        stories: {
            handler() {
                this.$nextTick(() => {
                    gsap.from(".story-item", {
                        scale: 0.8,
                        opacity: 0,
                        stagger: 0.1,
                        duration: 0.5,
                        ease: "back.out(1.7)",
                    });
                });
            },
            immediate: true,
        },
    },
    methods: {
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
                onComplete: () => {
                    this.show = false;
                    this.currentStory = null;
                    this.clearTimer();
                },
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
        onHoverStory(index) {
            gsap.to(`.story-item-${index}`, {
                scale: 1.05,
                boxShadow: "0 8px 16px rgba(0,0,0,0.1)",
                duration: 0.3,
                ease: "power2.out",
            });
        },
        onLeaveStory(index) {
            gsap.to(`.story-item-${index}`, {
                scale: 1,
                boxShadow: "none",
                duration: 0.3,
                ease: "power2.out",
            });
        },
        adjustModalSize() {
            if (!this.currentStory) return;
            const img = new Image();
            img.src = this.currentStory.image;
            img.onload = () => {
                const aspectRatio = img.width / img.height;
                const modal = this.$refs.storyContent;
                if (aspectRatio > 1) {
                    modal.style.width = `min(90vw, ${90 * aspectRatio}vh)`;
                    modal.style.height = "90vh";
                } else {
                    modal.style.width = "90vw";
                    modal.style.height = `min(90vh, ${90 / aspectRatio}vw)`;
                }
            };
        },
    },
    mounted() {
        const saved = localStorage.getItem("viewedStories");
        if (saved) {
            this.viewedStories = JSON.parse(saved);
        }

        this.$nextTick(() => {
            gsap.from(this.$refs.storyItems, {
                scale: 0.8,
                opacity: 0,
                stagger: 0.1,
                duration: 0.8,
                ease: "back.out(1.7)",
            });
        });
    },
    beforeDestroy() {
        this.clearTimer();
    },
};
</script>

<template>
    <ul
        class="story-list pt-2 Управление списками историй"
        v-if="sortedStories.length"
    >
        <li
            v-for="(story, index) in sortedStories"
            :key="story.id"
            class="d-block text-center"
            @mouseenter="onHoverStory(index)"
            @mouseleave="onLeaveStory(index)"
            ref="storyItems"
        >
            <div
                :class="[
                    'story-wrap',
                    { viewed: viewedStories.includes(story.id) },
                ]"
            >
                <div
                    :class="[
                        'story-item',
                        'rounded',
                        'bg-image',
                        `story-item-${index}`,
                    ]"
                    :style="'background-image: url(' + story.thumb + ');'"
                    @click="
                        show = !show;
                        currentStory = story;
                    "
                >
                    <i
                        v-if="viewedStories.includes(story.id)"
                        class="fa fa-eye viewed-icon"
                    ></i>
                </div>
            </div>
            <div class="story-footer">
                <span>{{ story.name }}</span>
            </div>
        </li>
    </ul>
    <ul class="story-list" v-else>
        <li v-for="e in 5" class="placeholder-glow d-block text-center">
            <div
                class="story-item rounded bg-image placeholder"
                style="width: 120px; height: 200px"
            ></div>
            <div
                class="story-footer-placeholder py-2 placeholder d-block rounded"
            ></div>
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
            <div class="wrap p-2" v-if="show">
                <div
                    ref="storyContent"
                    @mousedown="pauseTimer"
                    @mouseup="resumeTimer"
                    @touchstart="pauseTimer"
                    @touchend="resumeTimer"
                    class="story-modal w-auto overflow-hidden rounded"
                    style="max-height: max-content"
                >
                    <div class="story-container" style="max-height: 700px">
                        <div class="control-bar">
                            <div class="progress-bar">
                                <div
                                    class="progress"
                                    :style="{ width: progress + '%' }"
                                ></div>
                            </div>
                            <button @click="show = false" class="btn-close">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <div class="story-image-container">
                            <img
                                :src="currentStory?.image"
                                class="story-image"
                                :alt="currentStory?.name"
                            />
                        </div>
                    </div>
                </div>
                <div class="overlay" @click="show = !show"></div>
            </div>
        </transition>
    </teleport>
</template>

<style lang="scss" scoped>
.wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 9999;
    padding: 0;
}

.story-modal {
    position: relative;
    z-index: 10000;
    width: min(90vw, calc(100vh - 60px) * 0.5625);
    height: min(calc(90vw * 1.778), 90vh);
    display: flex;
    align-items: center;
    justify-content: center;
}

.story-container {
    width: 100%;
    height: 100%;
    position: relative;
    background: transparent;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.control-bar {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0.5), transparent);
    z-index: 10001;
}

.progress-bar {
    flex-grow: 1;
    background: rgba(255, 255, 255, 0.3);
    height: 4px;
    border-radius: 2px;
    overflow: hidden;
}

.progress {
    background: #fff;
    height: 100%;
    border-radius: 2px;
    transition: width 0.1s linear;
}

.btn-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s;

    &:hover {
        background: rgba(255, 255, 255, 0.3);
    }
}

.story-image-container {
    flex: 1;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.story-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    display: block;
}

.overlay {
    background: rgba(0, 0, 0, 0.9);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9998;
}

.story-list {
    display: flex;
    align-items: center;
    overflow-x: auto;
    padding: 0 0 48px 0;
    margin: 0;
    list-style: none;
    &::-webkit-scrollbar {
        display: none;
    }
}

.story-list li {
    cursor: pointer;
    min-width: 120px;
    height: 160px;
    margin-right: 10px;
    @media (min-width: 768px) {
        min-width: 140px;
        height: 240px;
        margin-right: 14px;
    }
}

.story-wrap {
    width: 100%;
    height: 100%;
    position: relative;
    padding: 4px;
    background: linear-gradient(45deg, #ff9a9e, #fad0c4, #fad0c4, #ff9a9e);
    border-radius: 16px;
    transition: transform 0.3s;
    &.viewed {
        background: linear-gradient(45deg, #ccc, #eee);
        opacity: 0.8;
    }
}

.story-item {
    height: 100%;
    width: 100%;
    position: relative;
    border-radius: 12px;
    background-size: cover;
    background-position: center;
}

.viewed-icon {
    position: absolute;
    top: 8px;
    right: 8px;
    color: #fff;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    padding: 6px;
    font-size: 0.9rem;
}

.story-footer {
    margin-top: 8px;
    text-align: center;
    padding: 0 8px;
    span {
        font-weight: 700;
        font-size: 0.9rem;
        color: #333;
        text-transform: capitalize;
    }
}

.story-footer-placeholder {
    position: relative;
    margin-top: 12px;
    height: 25px;
    &::before {
        content: "";
        display: block;
        width: 100%;
        height: 100%;
        border-radius: 20px;
        background: linear-gradient(45deg, #f09433, #e6683c, #dc2743);
        position: absolute;
        top: 0;
        left: 0;
        z-index: -1;
    }
}

@media (orientation: portrait) {
    .story-modal {
        width: 90vw;
        height: min(calc(90vw * 1.778), 90vh);
    }
}

@media (orientation: landscape) {
    .story-modal {
        width: min(90vw, calc(90vh * 0.5625));
        height: 90vh;
    }
}
</style>
