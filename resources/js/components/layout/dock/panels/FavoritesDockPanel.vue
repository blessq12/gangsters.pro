<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../../../design/useAppDesign";
import { useFavoritesReadModel } from "../../../../features/favorites/useFavorites";
import FavoritesDockItem from "./FavoritesDockItem.vue";

const panels = useAppDesign().components.dockPanels;
const favoritesReadModel = useFavoritesReadModel();

const favoriteItems = computed(() => favoritesReadModel.items.value);

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
