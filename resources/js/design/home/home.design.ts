import { homeJumbotronDesign } from "./homeJumbotron.design";
import { homePromotionsDesign } from "./homePromotions.design";
import { secondaryMarketingDesign } from "./secondary.design";

export const homeDesign = {
    jumbotron: homeJumbotronDesign,
    promotions: homePromotionsDesign,
    secondary: secondaryMarketingDesign,
} as const;
