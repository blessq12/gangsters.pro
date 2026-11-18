<script setup>
import { computed, ref } from "vue";
import { useFloatLoop } from "../../animations/animationManager";
import { storeToRefs } from "pinia";
import { useContentStore } from "../../modules/content/store";
import { hasDocumentBody } from "../../platform/document";
import { useAppDesign } from "../../design/useAppDesign";

const contentStore = useContentStore();
const { promotions, loading } = storeToRefs(contentStore);

const hp = useAppDesign().components.home.promotions;
const hpShared = hp.shared;
const hpDesk = hp.desktopSplit;

const promos = computed(() =>
    (promotions.value || []).map((promo) => ({
        id: promo.id,
        title: promo.title || "",
        description: promo.description || "",
        body: promo.body || "",
        image: promo.image || "",
        hasHtmlBody: hasDocumentBody(promo.body),
    })),
);

const isLoading = computed(
    () => loading.value && promotions.value.length === 0,
);

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
        <h2 :class="hpDesk.heading">
            Актуальные акции
        </h2>

        <div :class="hpDesk.grid">
            <template v-if="isLoading">
                <div
                    v-for="index in 4"
                    :key="index"
                    :class="hpDesk.skeleton"
                ></div>
            </template>

            <template v-else-if="promos.length">
                <article
                    v-for="(promo, index) in promos"
                    :key="promo.id ?? index"
                    :ref="(el) => registerPromo(el, index)"
                    :class="hpDesk.article"
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
                <div :class="hpDesk.emptySpan">
                    Акции скоро появятся.
                </div>
            </template>
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
                <div
                    v-if="activePromo.hasHtmlBody"
                    :class="hpShared.modalText"
                    v-html="activePromo.body"
                />
                <p
                    v-else-if="activePromo.description"
                    :class="hpShared.modalText"
                >
                    {{ activePromo.description }}
                </p>
            </div>
        </BaseModal>
    </section>
</template>

<style scoped></style>
