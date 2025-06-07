<script>
import gsap from "gsap";

export default {
    name: "StoryModal",
    emits: ["close", "pause", "resume", "prev", "next"],
    props: {
        story: {
            type: Object,
            required: true,
        },
        progress: {
            type: Number,
            required: true,
        },
    },
    methods: {
        adjustModalSize() {
            if (!this.story) return;
            const img = new Image();
            img.src = this.story.image;
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
        prevStory() {
            this.$emit("prev");
        },
        nextStory() {
            this.$emit("next");
        },
    },
    watch: {
        story: {
            handler() {
                this.adjustModalSize();
            },
            immediate: true,
        },
    },
    mounted() {
        gsap.to(this.$refs.modalOverlay, {
            opacity: 1,
            duration: 0.3,
            ease: "power2.out",
        });
        gsap.fromTo(
            this.$refs.storyContent,
            { opacity: 0, y: 20 },
            { opacity: 1, y: 0, duration: 0.3, ease: "power2.out" }
        );
    },
    beforeDestroy() {
        gsap.to(this.$refs.modalOverlay, {
            opacity: 0,
            duration: 0.3,
            ease: "power2.in",
        });
        gsap.to(this.$refs.storyContent, {
            opacity: 0,
            y: 20,
            duration: 0.3,
            ease: "power2.in",
        });
    },
};
</script>

<template>
    <teleport to="body">
        <div
            ref="modalOverlay"
            class="fixed inset-0 bg-black/20 backdrop-blur-sm"
            :style="{ opacity: 0, zIndex: 51 }"
        ></div>
        <div
            class="fixed inset-0 flex items-center justify-center p-4 z-[9999]"
        >
            <div
                ref="storyContent"
                @mousedown="$emit('pause')"
                @mouseup="$emit('resume')"
                @touchstart="$emit('pause')"
                @touchend="$emit('resume')"
                class="relative z-[10000] w-full h-full max-w-[90vw] max-h-[90vh] overflow-hidden rounded flex items-center justify-center invisible"
                :style="{ opacity: 0 }"
            >
                <div
                    class="relative w-fit-content h-full flex items-center justify-center visible overflow-hidden"
                >
                    <div
                        class="cursor-pointer absolute left-0 top-0 h-full w-1/8 px-4 flex items-center justify-center bg-gradient-to-r from-black/50 to-transparent"
                        @click="prevStory"
                    >
                        <div
                            class="mdi mdi-chevron-left text-white text-5xl"
                        ></div>
                    </div>
                    <div
                        class="cursor-pointer absolute right-0 top-0 h-full w-1/8 px-4 flex items-center justify-center bg-gradient-to-l from-black/50 to-transparent"
                        @click="nextStory"
                    >
                        <div
                            class="mdi mdi-chevron-right text-white text-5xl"
                        ></div>
                    </div>
                    <div
                        class="absolute top-0 left-0 right-0 p-4 flex items-center gap-3 bg-gradient-to-b from-black/50 to-transparent z-[10001]"
                    >
                        <div
                            class="flex-grow h-1 bg-white/30 rounded-full overflow-hidden"
                        >
                            <div
                                class="h-full bg-white rounded-full transition-[width] duration-100"
                                :style="{ width: progress + '%' }"
                            ></div>
                        </div>
                        <button
                            @click="$emit('close')"
                            class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors"
                        >
                            <i class="mdi mdi-close text-white"></i>
                        </button>
                    </div>
                    <img
                        :src="story?.image"
                        class="max-w-full max-h-full w-auto h-auto object-contain"
                        :alt="story?.name"
                    />
                </div>
            </div>
            <div
                class="fixed inset-0 bg-black/90 z-10"
                @click="$emit('close')"
            ></div>
        </div>
    </teleport>
</template>
