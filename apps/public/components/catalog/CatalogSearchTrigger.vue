<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { useCatalogSearch } from "../../modules/catalog/application/search";

const cs = useAppDesign().components.catalog.search;
const { openSearch } = useCatalogSearch();

const props = defineProps({
    inputId: {
        type: String,
        default: "catalog-search-trigger",
    },
    placeholder: {
        type: String,
        default: "Найти в меню…",
    },
    wrapClass: {
        type: String,
        default: "",
    },
    inputClass: {
        type: String,
        default: "",
    },
    iconClass: {
        type: String,
        default: "",
    },
});

function handleOpen() {
    openSearch();
}
</script>

<template>
    <label
        class="sr-only"
        :for="props.inputId"
    >Поиск в меню</label>
    <div
        :class="[props.wrapClass || cs.searchWrap, cs.triggerWrap]"
        role="button"
        tabindex="0"
        aria-label="Открыть поиск по меню"
        @click="handleOpen"
        @keydown.enter.prevent="handleOpen"
        @keydown.space.prevent="handleOpen"
    >
        <i
            class="mdi mdi-magnify"
            :class="props.iconClass || cs.searchIcon"
            aria-hidden="true"
        />
        <input
            :id="props.inputId"
            type="search"
            readonly
            :placeholder="props.placeholder"
            :class="[cs.searchInput, props.inputClass, 'cursor-pointer']"
            tabindex="-1"
            @focus="handleOpen"
        />
        <span
            :class="cs.triggerAffordance"
            aria-hidden="true"
        >
            <i class="mdi mdi-chevron-right text-lg" />
        </span>
    </div>
</template>
