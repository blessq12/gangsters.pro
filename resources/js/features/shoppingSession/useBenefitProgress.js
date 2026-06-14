import { useCheckoutSession } from "../checkout/useCheckoutSession";
import { formatKopecksToRub } from "../../utils/moneyFormat";

/** @deprecated Используйте useCheckoutSession */
export function useBenefitProgress() {
    const sessionView = useCheckoutSession();

    return {
        hasBenefitsProgress: sessionView.hasBenefitsProgress,
        hasActiveBenefits: sessionView.hasActiveBenefits,
        canShowBenefitsBanner: sessionView.canShowBenefitsBanner,
        delivery: sessionView.delivery,
        gift: sessionView.gift,
        deliveryProgressPercent: sessionView.deliveryProgressPercent,
        giftProgressPercent: sessionView.giftProgressPercent,
        deliveryLabel: sessionView.deliveryLabel,
        giftLabel: sessionView.giftLabel,
        benefitLines: sessionView.benefitLines,
        formatKopecksToRub,
    };
}
