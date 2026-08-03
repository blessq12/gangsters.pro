<script setup>
import { computed, unref } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useSelectedGiftSummary } from "../../features/checkout/useSelectedGiftSummary";
import {
    isComplementCartLine,
    isGiftCartLine,
    wizardNonComplementSystemItems,
} from "../../domain/order/normalizeCheckoutCart";

const cf = useAppDesign().components.checkout.confirm;
const s = useAppDesign().components.checkout.shared;

const { checkoutState } = useCheckoutFlowContext();
const { userCartItems, systemCartItems, formatPrice } = checkoutState;

const selectedGiftSummary = useSelectedGiftSummary();

const userLines = computed(() => unref(userCartItems) ?? []);
const systemLines = computed(() => unref(systemCartItems) ?? []);

const complementLines = computed(() =>
    systemLines.value.filter((item) => isComplementCartLine(item)),
);

const giftLines = computed(() => systemLines.value.filter((item) => isGiftCartLine(item)));

const giftLineForOrder = computed(() => {
    if (giftLines.value.length > 0) {
        return giftLines.value.map((item) => ({
            key: item.lineKey,
            name: item.productSnapshot?.name || `Товар #${item.productId}`,
            qty: item.qty,
        }));
    }

    const summary = selectedGiftSummary.value;
    if (!summary) {
        return [];
    }

    return [
        {
            key: `gift:${summary.productId}`,
            name: summary.name,
            qty: summary.qty,
        },
    ];
});

const hasGiftInSummary = computed(() => giftLineForOrder.value.length > 0);

const autoLines = computed(() =>
    wizardNonComplementSystemItems(systemLines.value),
);

function unitPriceRub(item) {
    const kopecks = Number(item?.pricing?.finalUnitPriceKopecks);
    if (Number.isFinite(kopecks)) return kopecks / 100;
    return Number(item?.productSnapshot?.price) || 0;
}

function linePriceLabel(item) {
    if (isComplementCartLine(item) || isGiftCartLine(item)) {
        return "Бесплатно";
    }
    return `${formatPrice(unitPriceRub(item))} ₽`;
}
</script>

<template>
    <ul :class="cf.orderList">
        <li
            v-for="item in userLines"
            :key="item.lineKey"
            :class="cf.orderLineRow"
        >
            <span :class="cf.orderLineTruncate">
                {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
            </span>
            <span :class="cf.orderLineMuted">
                {{ item.qty }} × {{ formatPrice(unitPriceRub(item)) }} ₽
            </span>
        </li>

        <li
            v-for="item in complementLines"
            :key="item.lineKey"
            :class="cf.orderLineRow"
        >
            <span :class="cf.orderLineTruncate">
                {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
                <span :class="cf.badgeTiny">комплект</span>
            </span>
            <span :class="cf.orderLineMuted">
                {{ item.qty }} × {{ linePriceLabel(item) }}
            </span>
        </li>

        <li
            v-for="gift in giftLineForOrder"
            :key="gift.key"
            :class="cf.orderLineRow"
        >
            <span :class="cf.orderLineTruncate">
                {{ gift.name }}
                <span :class="cf.badgeTiny">подарок</span>
            </span>
        </li>

        <li
            v-for="item in autoLines"
            :key="item.lineKey"
            :class="cf.orderLineRow"
        >
            <span :class="cf.orderLineTruncate">
                {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
            </span>
            <span :class="cf.orderLineMuted">
                {{ item.qty }} × {{ linePriceLabel(item) }}
            </span>
        </li>

        <li
            v-if="!userLines.length && !complementLines.length && !autoLines.length && !hasGiftInSummary"
            :class="s.textMutedLine"
        >
            Пусто
        </li>
    </ul>
</template>
