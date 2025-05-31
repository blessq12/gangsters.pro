<template>
    <li
        class="block text-center story-item min-w-[120px] h-[160px] mr-2.5 md:min-w-[140px] md:h-[240px] md:mr-3.5 cursor-pointer"
        @mouseenter="onHoverStory"
        @mouseleave="onLeaveStory"
    >
        <div
            :class="[
                'w-full h-full relative p-1 rounded-2xl bg-gradient-to-tr from-[#ff9a9e] via-[#fad0c4] to-[#ff9a9e]',
                {
                    'from-gray-400 via-gray-300 to-gray-400 opacity-90':
                        isViewed,
                },
            ]"
        >
            <div
                :class="[
                    'h-full w-full relative rounded-xl bg-cover bg-center',
                    `story-item-${index}`,
                ]"
                :style="'background-image: url(' + story.thumb + ');'"
                @click="$emit('select', story)"
            >
                <i
                    v-if="isViewed"
                    class="fa fa-check absolute top-2 right-2 text-white bg-black/70 rounded-full p-1.5 text-sm"
                ></i>
            </div>
        </div>
        <div class="mt-2 text-center px-2">
            <span class="font-bold text-sm text-gray-800 capitalize">{{
                story.name
            }}</span>
        </div>
    </li>
</template>

<script>
import gsap from "gsap";

export default {
    name: "StoryItem",
    props: {
        story: {
            type: Object,
            required: true,
        },
        index: {
            type: Number,
            required: true,
        },
        isViewed: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        onHoverStory() {
            gsap.to(`.story-item-${this.index}`, {
                scale: 1.05,
                boxShadow: "0 8px 16px rgba(0,0,0,0.1)",
                duration: 0.3,
                ease: "power2.out",
                overwrite: true,
            });
        },
        onLeaveStory() {
            gsap.to(`.story-item-${this.index}`, {
                scale: 1,
                boxShadow: "none",
                duration: 0.3,
                ease: "power2.out",
                overwrite: true,
            });
        },
    },
};
</script>
