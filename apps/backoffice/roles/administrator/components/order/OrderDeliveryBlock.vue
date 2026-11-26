<script setup>
import UiCard from "../../../../shell/components/ui/UiCard.vue";
import DeliveryAddressFields from "./DeliveryAddressFields.vue";

defineProps({
    method: { type: String, default: "pickup" },
    street: { type: String, default: "" },
    house: { type: String, default: "" },
    entrance: { type: String, default: "" },
    floor: { type: String, default: "" },
    apartment: { type: String, default: "" },
    comment: { type: String, default: "" },
    latitude: { type: [String, Number], default: "" },
    longitude: { type: [String, Number], default: "" },
    branchCity: { type: String, default: "" },
    streetError: { type: String, default: "" },
    houseError: { type: String, default: "" },
    zoneStatus: { type: String, default: "" },
    readonly: { type: Boolean, default: false },
});

const emit = defineEmits(["update:method", "address-update"]);

/**
 * @param {Record<string, unknown>} patch
 */
function onAddressUpdate(patch) {
    emit("address-update", patch);
}
</script>

<template>
    <UiCard title="Доставка">
        <div v-if="readonly" class="space-y-3 text-sm">
            <p class="text-bo-fg">
                {{ method === "courier" ? "Курьер" : "Самовывоз" }}
            </p>
            <template v-if="method === 'courier'">
                <p v-if="branchCity" class="text-bo-muted">
                    {{ branchCity }}
                </p>
                <p class="text-bo-fg">
                    {{ [street, house].filter(Boolean).join(", ") || "—" }}
                </p>
                <p v-if="apartment || entrance || floor" class="text-bo-muted">
                    <template v-if="entrance">под. {{ entrance }}</template>
                    <template v-if="floor">эт. {{ floor }}</template>
                    <template v-if="apartment">кв. {{ apartment }}</template>
                </p>
                <p v-if="comment" class="text-bo-muted">
                    {{ comment }}
                </p>
            </template>
        </div>
        <div v-else class="space-y-4">
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="h-10 border px-4 text-sm font-medium"
                    :class="
                        method === 'pickup'
                            ? 'border-bo-accent bg-bo-accent/15 text-bo-fg'
                            : 'border-bo-border bg-bo-field text-bo-muted'
                    "
                    @click="$emit('update:method', 'pickup')"
                >
                    Самовывоз
                </button>
                <button
                    type="button"
                    class="h-10 border px-4 text-sm font-medium"
                    :class="
                        method === 'courier'
                            ? 'border-bo-accent bg-bo-accent/15 text-bo-fg'
                            : 'border-bo-border bg-bo-field text-bo-muted'
                    "
                    @click="$emit('update:method', 'courier')"
                >
                    Курьер
                </button>
            </div>

            <DeliveryAddressFields
                v-if="method === 'courier'"
                :street="street"
                :house="house"
                :entrance="entrance"
                :floor="floor"
                :apartment="apartment"
                :comment="comment"
                :latitude="latitude"
                :longitude="longitude"
                :branch-city="branchCity"
                :street-error="streetError"
                :house-error="houseError"
                :zone-status="zoneStatus"
                @update="onAddressUpdate"
            />
        </div>
    </UiCard>
</template>
