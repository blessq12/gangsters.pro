import { useCheckoutSession } from "./useCheckoutSession";

export function useCheckoutCartView() {
    const sessionView = useCheckoutSession();

    return {
        cartReadModel: sessionView,
        benefits: sessionView.benefits,
        cartItems: sessionView.cartItems,
        userCartItems: sessionView.userCartItems,
        systemCartItems: sessionView.systemCartItems,
        totalAmount: sessionView.totalAmount,
        hasDeliveryPricing: sessionView.hasDeliveryPricing,
        itemsTotalAmount: sessionView.itemsTotalAmount,
        deliveryFeeAmount: sessionView.deliveryFeeAmount,
        isDeliveryFree: sessionView.isDeliveryFree,
        userTotalAmount: sessionView.userTotalAmount,
        systemTotalAmount: sessionView.systemTotalAmount,
        promoState: sessionView.promoState,
        benefitsProgress: sessionView.benefitsProgress,
        deliveryBenefit: sessionView.deliveryBenefit,
        giftBenefit: sessionView.giftBenefit,
        hasCartItems: sessionView.hasCartItems,
    };
}
