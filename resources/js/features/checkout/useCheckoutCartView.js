import { computed } from "vue";
import { useBenefitProgress } from "../shoppingSession/useBenefitProgress";
import { useCartReadModel } from "../shoppingSession/useCartReadModel";

export function useCheckoutCartView() {
    const cartReadModel = useCartReadModel();
    const benefits = useBenefitProgress();

    const cartItems = computed(() => cartReadModel.items.value);
    const userCartItems = computed(() => cartReadModel.userItems.value);
    const systemCartItems = computed(() => cartReadModel.systemItems.value);
    const totalAmount = computed(() =>
        cartReadModel.hasDeliveryPricing.value
            ? cartReadModel.grandTotalWithDelivery.value
            : cartReadModel.totalAmount.value,
    );
    const hasDeliveryPricing = computed(() => cartReadModel.hasDeliveryPricing.value);
    const itemsTotalAmount = computed(() => cartReadModel.itemsTotalAmount.value);
    const deliveryFeeAmount = computed(() => cartReadModel.deliveryFeeAmount.value);
    const isDeliveryFree = computed(() => cartReadModel.isDeliveryFree.value);
    const userTotalAmount = computed(() => cartReadModel.userTotalAmount.value);
    const systemTotalAmount = computed(() => cartReadModel.systemTotalAmount.value);
    const promoState = computed(() => cartReadModel.promoState.value);
    const benefitsProgress = computed(() => cartReadModel.benefitsProgress.value);
    const deliveryBenefit = computed(() => benefits.delivery.value);
    const giftBenefit = computed(() => benefits.gift.value);
    const hasCartItems = computed(() => userCartItems.value.length > 0);

    return {
        cartReadModel,
        benefits,
        cartItems,
        userCartItems,
        systemCartItems,
        totalAmount,
        hasDeliveryPricing,
        itemsTotalAmount,
        deliveryFeeAmount,
        isDeliveryFree,
        userTotalAmount,
        systemTotalAmount,
        promoState,
        benefitsProgress,
        deliveryBenefit,
        giftBenefit,
        hasCartItems,
    };
}
