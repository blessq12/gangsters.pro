<script setup>
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../../../design/useAppDesign";
import { useFavoritesStore } from "../../../../stores/favoritesStore";
import FavoritesDockItem from "./FavoritesDockItem.vue";

const panels = useAppDesign().components.dockPanels;
const favoritesStore = useFavoritesStore();
const { favorites: favoriteItems } = storeToRefs(favoritesStore);

const f = panels.favorites;
</script>

<template>
    <DockPanelLayout
        title="Избранное"
        description="Сохранённые позиции — добавь в корзину или убери из списка."
    >
        <div
            v-if="!favoriteItems.length"
            :class="f.emptyState"
        >
            Избранное пока пустое. Ткни сердечко на карточке, и позиция появится тут.
        </div>

        <ul
            v-else
            :class="f.ul"
        >
            <FavoritesDockItem
                v-for="item in favoriteItems"
                :key="item.productId"
                :item="item"
            />
        </ul>
    </DockPanelLayout>
</template>

<style scoped></style>
