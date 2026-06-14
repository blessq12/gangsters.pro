import {
    fetchMarketingBannersRequest,
    fetchMarketingPromotionsRequest,
} from "../../api/marketingApi";
import {
    normalizeMarketingBanner,
    normalizeMarketingPromotion,
} from "../../domain/marketing/marketingMappers";

export async function fetchMarketingBanners() {
    const payload = await fetchMarketingBannersRequest();
    const raw = Array.isArray(payload?.data) ? payload.data : [];
    return raw.map((row) => normalizeMarketingBanner(row)).filter(Boolean);
}

export async function fetchMarketingPromotions() {
    const payload = await fetchMarketingPromotionsRequest();
    const raw = Array.isArray(payload?.data) ? payload.data : [];
    return raw.map((row) => normalizeMarketingPromotion(row)).filter(Boolean);
}
