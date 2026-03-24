<script setup>
import { computed, ref } from "vue";
import { useFloatLoop } from "../../composables/animations/useFloatLoop";
import { useSystemStore } from "../../stores/systemStore";

const systemStore = useSystemStore();

const promos = computed(() =>
    (systemStore.promotions || []).map((promo) => ({
        title: promo.title || "",
        description: promo.description || "",
        image: promo.image || "",
    })),
);

const isLoading = computed(() => systemStore.loadingPromotions);

const showModal = ref(false);
const activePromo = ref(null);

const promoRefs = ref([]);

const registerPromo = (el, index) => {
    if (el) {
        promoRefs.value[index] = el;
    }
};

useFloatLoop(promoRefs, {
    y: 7,
    x: 5,
    duration: 3.2,
    stagger: 0.25,
});

const openPromo = (promo) => {
    activePromo.value = promo;
    showModal.value = true;
};

</script>

<template>
    <section class="my-12">
        <h2 class="text-lg sm:text-xl font-semibold text-slate-100 mb-4">
            Актуальные акции
        </h2>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
            <template v-if="isLoading">
                <div
                    v-for="index in 3"
                    :key="index"
                    class="aspect-[16/9] w-full rounded-2xl border border-white/10 bg-slate-800/60 animate-pulse"
                ></div>
            </template>
            <template v-else-if="promos.length">
                <article
                    v-for="(promo, index) in promos"
                    :key="index"
                    :ref="(el) => registerPromo(el, index)"
                    class="group rounded-2xl relative cursor-pointer"
                    @click="openPromo(promo)"
                >
                    <div class="aspect-[16/9] w-full overflow-hidden">
                        <img
                            :src="promo.image"
                            :alt="promo.title"
                            class="h-full w-full object-cover grayscale group-hover:grayscale-0 transition-transform duration-500 ease-out group-hover:scale-105"
                        />
                    </div>
                </article>
            </template>
            <template v-else>
                <div class="col-span-2 sm:col-span-3 py-4 text-center text-xs text-slate-500">
                    Акции скоро появятся.
                </div>
            </template>
        </div>

        <BaseModal v-model="showModal" v-if="activePromo">
            <template #header>{{ activePromo.title }}</template>
            <div class="space-y-3">
                <div class="aspect-[16/9] w-full overflow-hidden rounded-xl">
                    <img
                        :src="activePromo.image"
                        :alt="activePromo.title"
                        class="h-full w-full object-cover"
                    />
                </div>
                <p class="text-sm text-slate-100 leading-relaxed">
                    {{ activePromo.description }}
                </p>
            </div>
        </BaseModal>
    </section>
</template>

<style scoped></style>
