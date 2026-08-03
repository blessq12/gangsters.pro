/**
 * Плоское представление для утилит отображения (companyDeliveryFacts, карта).
 * @param {object|null|undefined} delivery
 * @returns {object|null}
 */
export function toDeliveryFactsView(delivery) {
    if (!delivery || typeof delivery !== "object") {
        return null;
    }

    const settings = delivery.settings || {};
    const zone = delivery.zone || {};
    const kitchen = zone.kitchen_address || {};

    return {
        min_order_amount_kopecks: settings.min_order_amount_kopecks ?? null,
        delivery_fee_kopecks: settings.delivery_fee_kopecks ?? null,
        outside_zone_delivery_fee_kopecks:
            settings.outside_zone_delivery_fee_kopecks ?? null,
        average_delivery_time_minutes:
            settings.average_delivery_time_minutes ?? null,
        city: kitchen.city,
        street: kitchen.street,
        house: kitchen.house,
        address_comment: kitchen.comment,
        kitchen_latitude: zone.kitchen_latitude ?? null,
        kitchen_longitude: zone.kitchen_longitude ?? null,
        delivery_zone_geojson: zone.delivery_zone_geojson ?? null,
    };
}
