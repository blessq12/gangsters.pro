<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../../../design/useAppDesign";
import { useFavoritesCommands } from "../../../../features/favorites/useFavoritesCommands";
import { useFavoritesReadModel } from "../../../../features/favorites/useFavoritesReadModel";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../../../shared/domainEvents";
import { formatMoneyRublesRu } from "../../../../utils/moneyFormat";

const panels = useAppDesign().components.dockPanels;
const favoritesCommands = useFavoritesCommands();
const favoritesReadModel = useFavoritesReadModel();

const favoriteItems = computed(() => favoritesReadModel.items.value);

const s = panels.shared;
const f = panels.favorites;

const handleAddToCart = (item) => {
    if (!item?.productSnapshot?.id) return;
    emitDomainEvent(DOMAIN_EVENTS.CART_ADD_REQUESTED, {
        product: item.productSnapshot,
        qty: 1,
        source: "favorites",
    });
};

const formatPrice = (value) => formatMoneyRublesRu(value);
</script>

<template>
    <div :class="s.shell">
        <div :class="s.stackSimple">
            <p :class="s.typography.panelTitle">
                Избранное
            </p>

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
                <li
                    v-for="item in favoriteItems"
                    :key="item.productId"
                    :class="f.row"
                >
                    <div :class="s.minWidth0">
                        <p :class="f.productName">
                            {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
                        </p>
                        <p :class="f.productPrice">
                            {{ formatPrice(item.productSnapshot?.price) }} ₽
                        </p>
                    </div>

                    <div :class="f.actionRow">
                        <button
                            type="button"
                            :class="f.actionAddToCart"
                            @click="handleAddToCart(item)"
                        >
                            В корзину
                        </button>
                        <button
                            type="button"
                            :class="f.actionRemove"
                            @click="favoritesCommands.remove(item.productId)"
                        >
                            Убрать
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped></style>
