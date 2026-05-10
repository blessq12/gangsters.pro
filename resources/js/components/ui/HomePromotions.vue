<script setup>
import { computed, ref } from "vue";
import { useFloatLoop } from "../../composables/animations/useFloatLoop";
import { useSystemReadModel } from "../../features/system/useSystemReadModel";
import { useAppDesign } from "../../design/useAppDesign";

const { promotions, loading } = useSystemReadModel({ autoload: true });

const hp = useAppDesign().components.home.promotions;
const hpShared = hp.shared;
const hpCombo = hp.combo;

const promos = computed(() =>
    (promotions.value || []).map((promo) => ({
        title: promo.title || "",
        description: promo.description || "",
        image: promo.image || "",
    })),
);

const isLoading = computed(() => loading.value.promotions);

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
    <section :class="hpShared.section">
        <h2 :class="hpCombo.heading">
            Актуальные акции
        </h2>

        <!-- Mobile: горизонтальный скролл (больше размер карточек) -->
        <div class="md:hidden">
            <div :class="hpCombo.mobileScroll">
                <template v-if="isLoading">
                    <div
                        v-for="index in 4"
                        :key="index"
                        :class="hpCombo.mobileSkeletonOuter"
                    >
                        <div :class="hpShared.pulseInner" />
                    </div>
                </template>

                <template v-else-if="promos.length">
                    <article
                        v-for="(promo, index) in promos"
                        :key="index"
                        :ref="(el) => registerPromo(el, index)"
                        :class="hpCombo.mobileArticle"
                        @click="openPromo(promo)"
                    >
                        <div :class="hpShared.thumbWrap">
                            <img
                                :src="promo.image"
                                :alt="promo.title"
                                :class="hpShared.thumbImg"
                            />
                        </div>
                    </article>
                </template>

                <template v-else>
                    <div :class="hpShared.emptyText">
                        Акции скоро появятся.
                    </div>
                </template>
            </div>
        </div>

        <!-- Desktop: 4 в строке, выравнивание по центру -->
        <div class="hidden md:block">
            <div :class="hpCombo.desktopGrid">
                <template v-if="isLoading">
                    <div
                        v-for="index in 4"
                        :key="index"
                        :class="hpCombo.desktopSkeleton"
                    ></div>
                </template>

                <template v-else-if="promos.length">
                    <article
                        v-for="(promo, index) in promos"
                        :key="index"
                        :ref="(el) => registerPromo(el, index)"
                        :class="hpCombo.desktopArticle"
                        @click="openPromo(promo)"
                    >
                        <div :class="hpShared.thumbWrap">
                            <img
                                :src="promo.image"
                                :alt="promo.title"
                                :class="hpShared.thumbImg"
                            />
                        </div>
                    </article>
                </template>

                <template v-else>
                    <div :class="hpCombo.emptyDesktopSpan">
                        Акции скоро появятся.
                    </div>
                </template>
            </div>
        </div>

        <BaseModal v-model="showModal" v-if="activePromo">
            <template #header>{{ activePromo.title }}</template>
            <div :class="hpShared.modalStack">
                <div :class="hpShared.modalMedia">
                    <img
                        :src="activePromo.image"
                        :alt="activePromo.title"
                        :class="hpShared.modalImg"
                    />
                </div>
                <p :class="hpShared.modalText">
                    {{ activePromo.description }}
                </p>
            </div>
        </BaseModal>
    </section>
</template>

<style scoped>
.promos-scroll {
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE/Edge */
}

.promos-scroll::-webkit-scrollbar {
    width: 0;
    height: 0;
    display: none; /* Chrome/Safari */
}
</style>
