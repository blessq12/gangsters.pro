import { fetchDeliveryRequest } from "../../api/deliveryApi";
import { normalizeDeliveryData } from "../../domain/delivery/deliveryMappers";

export async function fetchDeliveryData() {
    const payload = await fetchDeliveryRequest();
    const raw = payload && typeof payload === "object" ? payload.data : null;
    return normalizeDeliveryData(raw);
}
