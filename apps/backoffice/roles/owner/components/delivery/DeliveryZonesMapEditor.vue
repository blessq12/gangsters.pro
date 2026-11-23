<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from "vue";
import {
    TOMSK_CENTER,
    loadYandexMapsApi,
    readYandexMapsApiKey,
} from "../../../../shell/maps/yandexMapsApi.js";
import {
    TOMSK_RESTRICT_AREA,
    TOMSK_ZOOM,
    geometryToYmapsPolygonCoords,
    hasRenderableGeometry,
    ymapsGeometryToGeoJson,
} from "../../../../shell/maps/yandexGeoJsonCoords.js";
import UiButton from "../../../../shell/components/ui/UiButton.vue";
import UiFormField from "../../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../../shell/components/ui/UiInput.vue";

const props = defineProps({
    /** @type {Array<Record<string, unknown>>} */
    zones: { type: Array, default: () => [] },
    /** [lat, lng] */
    branchCenter: { type: Array, default: null },
    disabled: { type: Boolean, default: false },
});

const mapHost = ref(null);
const statusText = ref("Загрузка карты…");
const selectedIndex = ref(-1);

const form = reactive({
    name: "",
    fee: "",
    is_remote: false,
});

const ZONE_COLORS = ["#C62424", "#2563EB", "#059669", "#D97706", "#7C3AED", "#DB2777"];
const apiKey = readYandexMapsApiKey();

/** @type {any} */
let map = null;
/** @type {Map<string, any>} */
const polygonById = new Map();
/** @type {Map<string, Function[]>} */
const polygonListenerDisposers = new Map();
/** @type {any} */
let branchPlacemark = null;
let destroyed = false;
let drawing = false;
/** Сериализация последнего props.zones — чтобы не пересобирать карту из собственного черновика. */
let lastSyncedZonesKey = "";

const localZones = ref(/** @type {Array<Record<string, any>>} */ ([]));

const selectedZone = computed(() =>
    selectedIndex.value >= 0 ? localZones.value[selectedIndex.value] ?? null : null,
);

function createZoneId() {
    if (typeof crypto !== "undefined" && crypto.randomUUID) {
        return crypto.randomUUID();
    }
    return `zone-${Date.now()}-${Math.floor(Math.random() * 1e6)}`;
}

function zoneColor(index) {
    return ZONE_COLORS[index % ZONE_COLORS.length];
}

function resolveCenter() {
    const center = props.branchCenter;
    if (
        Array.isArray(center)
        && center.length >= 2
        && Number.isFinite(Number(center[0]))
        && Number.isFinite(Number(center[1]))
    ) {
        return [Number(center[0]), Number(center[1])];
    }
    return TOMSK_CENTER;
}

function feeRubles(zone) {
    if (zone?.fee != null) {
        return Math.max(0, Math.round(Number(zone.fee)));
    }
    if (zone?.fee_rubles != null) {
        return Math.max(0, Math.round(Number(zone.fee_rubles)));
    }
    if (zone?.delivery_fee_kopecks != null) {
        return Math.max(0, Math.round(Number(zone.delivery_fee_kopecks) / 100));
    }
    return 0;
}

function normalizeIncoming(rawZones) {
    if (!Array.isArray(rawZones)) {
        return [];
    }
    return rawZones
        .filter((zone) => zone && typeof zone === "object")
        .map((zone, index) => ({
            id: String(zone.id || createZoneId()),
            name: String(zone.name || `Зона ${index + 1}`),
            fee: feeRubles(zone),
            is_remote: Boolean(zone.is_remote),
            geometry: zone.geometry ?? { type: "Polygon", coordinates: [] },
        }));
}

function detachPolygonListeners(zoneId) {
    const disposers = polygonListenerDisposers.get(zoneId) || [];
    disposers.forEach((dispose) => {
        try {
            dispose();
        } catch {
            /* ignore */
        }
    });
    polygonListenerDisposers.delete(zoneId);
}

function stopAllEditing() {
    polygonById.forEach((polygon) => {
        if (polygon?.editor) {
            try {
                polygon.editor.stopEditing();
                polygon.editor.stopDrawing();
            } catch {
                /* ignore */
            }
        }
    });
    drawing = false;
}

function persistPolygonGeometry(zone, polygon) {
    if (!zone || !polygon) {
        return false;
    }
    const geometry = ymapsGeometryToGeoJson(
        polygon.geometry.getType(),
        polygon.geometry.getCoordinates(),
    );
    if (!geometry) {
        return false;
    }
    zone.geometry = geometry;
    return true;
}

function persistAllPolygonGeometries() {
    localZones.value.forEach((zone) => {
        const polygon = polygonById.get(zone.id);
        if (polygon) {
            persistPolygonGeometry(zone, polygon);
        }
    });
}

function attachPolygonListeners(zone, polygon) {
    detachPolygonListeners(zone.id);
    if (!polygon?.editor?.events) {
        return;
    }

    const onDrawingStop = () => {
        drawing = false;
        persistPolygonGeometry(zone, polygon);
        try {
            polygon.editor.startEditing();
        } catch {
            /* ignore */
        }
        statusText.value = "Контур готов. Можно подвинуть вершины или сохранить.";
    };
    const onGeometryChange = () => {
        persistPolygonGeometry(zone, polygon);
    };

    polygon.editor.events.add("drawingstop", onDrawingStop);
    polygon.editor.events.add("geometrychange", onGeometryChange);
    polygonListenerDisposers.set(zone.id, [
        () => polygon.editor.events.remove("drawingstop", onDrawingStop),
        () => polygon.editor.events.remove("geometrychange", onGeometryChange),
    ]);
}

function createPolygonForZone(zone, index, { editable = false } = {}) {
    const ymapsCoords = geometryToYmapsPolygonCoords(zone.geometry);
    if (!ymapsCoords.length || !ymapsCoords[0]?.length) {
        return null;
    }
    const color = zoneColor(index);
    const polygon = new window.ymaps.Polygon(ymapsCoords, {
        hintContent: zone.name,
    }, {
        editorDrawingCursor: "crosshair",
        editorMaxPoints: 80,
        fillColor: `${color}44`,
        strokeColor: color,
        strokeWidth: editable ? 3 : 2,
        draggable: false,
    });
    map.geoObjects.add(polygon);
    polygonById.set(zone.id, polygon);
    attachPolygonListeners(zone, polygon);
    if (editable && !props.disabled && polygon.editor) {
        try {
            polygon.editor.startEditing();
        } catch {
            /* ignore */
        }
    }
    return polygon;
}

function clearPolygons() {
    stopAllEditing();
    polygonById.forEach((_, zoneId) => detachPolygonListeners(zoneId));
    polygonById.forEach((polygon) => map?.geoObjects.remove(polygon));
    polygonById.clear();
}

function rebuildPolygons({ fitBounds = false, preserveEditing = true } = {}) {
    if (!map) {
        return;
    }
    clearPolygons();
    localZones.value.forEach((zone, index) => {
        createPolygonForZone(zone, index, {
            editable: preserveEditing && index === selectedIndex.value,
        });
    });
    if (fitBounds) {
        fitZonesOrCenter();
    }
}

function fitZonesOrCenter() {
    if (!map) {
        return;
    }
    if (polygonById.size === 0) {
        map.setCenter(resolveCenter(), TOMSK_ZOOM, { duration: 0 });
        return;
    }
    const bounds = map.geoObjects.getBounds();
    if (!bounds) {
        map.setCenter(resolveCenter(), TOMSK_ZOOM, { duration: 0 });
        return;
    }
    map.setBounds(bounds, { checkZoomRange: true, zoomMargin: 48 });
}

function updateBranchPlacemark() {
    if (!map) {
        return;
    }
    if (branchPlacemark) {
        map.geoObjects.remove(branchPlacemark);
        branchPlacemark = null;
    }
    const center = resolveCenter();
    if (!props.branchCenter) {
        return;
    }
    branchPlacemark = new window.ymaps.Placemark(
        center,
        { hintContent: "Филиал" },
        { preset: "islands#redDotIcon" },
    );
    map.geoObjects.add(branchPlacemark);
}

function syncFormFromSelected() {
    const zone = selectedZone.value;
    if (!zone) {
        form.name = "";
        form.fee = "";
        form.is_remote = false;
        return;
    }
    form.name = zone.name ?? "";
    form.fee = String(feeRubles(zone));
    form.is_remote = Boolean(zone.is_remote);
}

function applyFormToSelected() {
    const zone = selectedZone.value;
    if (!zone) {
        return;
    }
    const name = String(form.name).trim();
    zone.name = name !== "" ? name : zone.name;
    const fee = Number(form.fee);
    zone.fee = Number.isFinite(fee) ? Math.max(0, Math.round(fee)) : zone.fee;
    zone.is_remote = Boolean(form.is_remote);
}

function activateSelectedEditing() {
    if (!map || selectedIndex.value < 0 || props.disabled) {
        return;
    }
    const zone = selectedZone.value;
    if (!zone) {
        return;
    }
    stopAllEditing();
    polygonById.forEach((polygon, zoneId) => {
        const index = localZones.value.findIndex((item) => item.id === zoneId);
        const color = zoneColor(index >= 0 ? index : 0);
        polygon.options.set("strokeWidth", zoneId === zone.id ? 3 : 2);
        polygon.options.set("strokeColor", color);
        polygon.options.set("fillColor", `${color}44`);
    });

    let polygon = polygonById.get(zone.id);
    if (!polygon && hasRenderableGeometry(zone.geometry)) {
        polygon = createPolygonForZone(zone, selectedIndex.value, { editable: false });
    }
    if (!polygon) {
        statusText.value = "У зоны нет контура. Нажми «Нарисовать контур».";
        return;
    }
    attachPolygonListeners(zone, polygon);
    try {
        polygon.editor.startEditing();
        statusText.value = `Выбрана «${zone.name}». Тяни вершины или правь параметры слева.`;
    } catch {
        statusText.value = "Редактор вершин недоступен. Нажми «Нарисовать контур».";
    }
}

function selectZone(index) {
    if (!Number.isFinite(index) || index < 0 || index >= localZones.value.length) {
        return;
    }
    persistAllPolygonGeometries();
    applyFormToSelected();
    selectedIndex.value = index;
    syncFormFromSelected();
    activateSelectedEditing();
}

function addZone() {
    if (props.disabled) {
        return;
    }
    persistAllPolygonGeometries();
    applyFormToSelected();
    localZones.value.push({
        id: createZoneId(),
        name: `Зона ${localZones.value.length + 1}`,
        fee: 200,
        is_remote: false,
        geometry: { type: "Polygon", coordinates: [] },
    });
    selectedIndex.value = localZones.value.length - 1;
    syncFormFromSelected();
    map?.setCenter(resolveCenter(), TOMSK_ZOOM, { duration: 0 });
    startRedrawSelected();
}

function deleteSelectedZone() {
    if (props.disabled || selectedIndex.value < 0) {
        return;
    }
    const zone = selectedZone.value;
    if (!zone) {
        return;
    }
    const polygon = polygonById.get(zone.id);
    detachPolygonListeners(zone.id);
    if (polygon && map) {
        try {
            polygon.editor?.stopEditing();
        } catch {
            /* ignore */
        }
        map.geoObjects.remove(polygon);
    }
    polygonById.delete(zone.id);
    localZones.value.splice(selectedIndex.value, 1);
    selectedIndex.value = localZones.value.length > 0
        ? Math.min(selectedIndex.value, localZones.value.length - 1)
        : -1;
    syncFormFromSelected();
    rebuildPolygons({ fitBounds: true, preserveEditing: true });
    statusText.value = localZones.value.length ? "Зона удалена." : "Все зоны удалены.";
}

function startRedrawSelected() {
    if (!map || selectedIndex.value < 0 || props.disabled) {
        statusText.value = "Сначала добавь зону.";
        return;
    }
    const zone = selectedZone.value;
    stopAllEditing();
    map.setCenter(resolveCenter(), TOMSK_ZOOM, { duration: 0 });

    const existing = polygonById.get(zone.id);
    if (existing) {
        detachPolygonListeners(zone.id);
        map.geoObjects.remove(existing);
        polygonById.delete(zone.id);
    }

    drawing = true;
    const color = zoneColor(selectedIndex.value);
    const polygon = new window.ymaps.Polygon([], {}, {
        editorDrawingCursor: "crosshair",
        editorMaxPoints: 80,
        fillColor: `${color}44`,
        strokeColor: color,
        strokeWidth: 3,
    });
    map.geoObjects.add(polygon);
    polygonById.set(zone.id, polygon);

    const onDrawingStop = () => {
        drawing = false;
        persistPolygonGeometry(zone, polygon);
        attachPolygonListeners(zone, polygon);
        try {
            polygon.editor.startEditing();
        } catch {
            /* ignore */
        }
        statusText.value = "Контур готов. Проверь параметры и нажми «Сохранить».";
    };
    polygon.editor.events.add("drawingstop", onDrawingStop);
    polygonListenerDisposers.set(zone.id, [
        () => polygon.editor.events.remove("drawingstop", onDrawingStop),
    ]);
    polygon.editor.startDrawing();
    statusText.value = "Кликай по карте. Двойной клик — завершить контур.";
}

function onFormFieldChange() {
    applyFormToSelected();
}

function zonesKey(zones) {
    try {
        return JSON.stringify(normalizeIncoming(zones));
    } catch {
        return String(Date.now());
    }
}

function syncFromProps({ force = false } = {}) {
    if (!map || destroyed || drawing) {
        return;
    }
    const nextKey = zonesKey(props.zones);
    if (!force && nextKey === lastSyncedZonesKey) {
        updateBranchPlacemark();
        return;
    }
    lastSyncedZonesKey = nextKey;
    localZones.value = normalizeIncoming(props.zones);
    if (localZones.value.length === 0) {
        selectedIndex.value = -1;
    } else if (selectedIndex.value < 0 || selectedIndex.value >= localZones.value.length) {
        selectedIndex.value = 0;
    }
    syncFormFromSelected();
    updateBranchPlacemark();
    rebuildPolygons({ fitBounds: localZones.value.length > 0, preserveEditing: true });
    statusText.value = localZones.value.length
        ? "Зоны загружены. Выбери зону слева для правки."
        : "Добавь зону и нарисуй контур.";
}

/**
 * Снимок зон для сохранения (с актуальными контурами с карты).
 * @returns {Array<Record<string, unknown>>}
 */
function snapshotZones() {
    persistAllPolygonGeometries();
    applyFormToSelected();
    return localZones.value.map((zone) => ({
        id: zone.id,
        name: zone.name,
        fee: feeRubles(zone),
        is_remote: Boolean(zone.is_remote),
        geometry: zone.geometry,
    }));
}

defineExpose({ snapshotZones });

async function bootstrap() {
    if (!apiKey) {
        statusText.value = "Нет VITE_YANDEX_MAPS_API_KEY — карта недоступна";
        return;
    }
    if (!mapHost.value) {
        return;
    }
    try {
        await loadYandexMapsApi(apiKey);
        if (destroyed || !mapHost.value) {
            return;
        }
        map = new window.ymaps.Map(mapHost.value, {
            center: resolveCenter(),
            zoom: TOMSK_ZOOM,
            controls: ["zoomControl", "typeSelector"],
        }, {
            restrictMapArea: TOMSK_RESTRICT_AREA,
        });
        statusText.value = "Карта загружена.";
        syncFromProps();
    } catch (err) {
        statusText.value = err instanceof Error ? err.message : "Не удалось загрузить карту";
    }
}

watch(
    () => [props.zones, props.branchCenter, props.disabled],
    () => {
        if (!map || drawing) {
            return;
        }
        syncFromProps();
    },
    { deep: true },
);

onMounted(async () => {
    await nextTick();
    await bootstrap();
});

onBeforeUnmount(() => {
    destroyed = true;
    clearPolygons();
    if (map) {
        map.destroy();
        map = null;
    }
    branchPlacemark = null;
});
</script>

<template>
    <div class="grid gap-4 lg:grid-cols-[minmax(18rem,22rem)_minmax(0,1fr)]">
        <aside class="space-y-4 border border-bo-border bg-bo-card p-4">
            <div>
                <h2 class="text-base font-semibold text-bo-fg">
                    Зоны доставки
                </h2>
                <p class="mt-1 text-xs text-bo-muted">
                    Выбери зону, задай параметры и контур на карте, затем сохрани.
                </p>
            </div>

            <ul class="max-h-56 overflow-y-auto border border-bo-border">
                <li
                    v-if="localZones.length === 0"
                    class="px-3 py-4 text-sm text-bo-muted"
                >
                    Зон пока нет. Нажми «Добавить зону».
                </li>
                <li
                    v-for="(zone, index) in localZones"
                    :key="zone.id"
                    class="cursor-pointer border-b border-bo-border px-3 py-2.5 last:border-b-0"
                    :class="index === selectedIndex
                        ? 'bg-bo-accent/15 shadow-[inset_3px_0_0_theme(colors.bo-accent)]'
                        : 'hover:bg-white/[0.03]'"
                    @click="selectZone(index)"
                >
                    <div class="flex items-center gap-2 text-sm font-medium text-bo-fg">
                        <span
                            class="inline-block h-2 w-2 shrink-0 rounded-full"
                            :style="{ background: zoneColor(index) }"
                        />
                        {{ zone.name }}
                    </div>
                    <div class="mt-0.5 text-xs text-bo-muted">
                        {{ feeRubles(zone) }} ₽
                        · {{ zone.is_remote ? "отдалённый" : "обычный" }}
                        · {{ hasRenderableGeometry(zone.geometry) ? "контур задан" : "нет контура" }}
                    </div>
                </li>
            </ul>

            <div class="grid grid-cols-2 gap-2">
                <UiButton class="!h-10" :disabled="disabled" @click="addZone">
                    + Добавить зону
                </UiButton>
                <UiButton
                    class="!h-10 !bg-white/10 !text-bo-fg"
                    :disabled="disabled || selectedIndex < 0"
                    @click="deleteSelectedZone"
                >
                    Удалить
                </UiButton>
            </div>

            <div class="space-y-3 border border-bo-border p-3">
                <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-bo-muted">
                    Параметры зоны
                </p>
                <UiFormField label="Название">
                    <UiInput
                        v-model="form.name"
                        :disabled="disabled || selectedIndex < 0"
                        @change="onFormFieldChange"
                        @blur="onFormFieldChange"
                    />
                </UiFormField>
                <UiFormField label="Цена доставки, ₽">
                    <UiInput
                        v-model="form.fee"
                        type="number"
                        min="0"
                        step="1"
                        :disabled="disabled || selectedIndex < 0"
                        @change="onFormFieldChange"
                        @blur="onFormFieldChange"
                    />
                </UiFormField>
                <label class="flex items-center gap-2 text-sm text-bo-fg">
                    <input
                        v-model="form.is_remote"
                        type="checkbox"
                        class="accent-bo-accent"
                        :disabled="disabled || selectedIndex < 0"
                        @change="onFormFieldChange"
                    >
                    Отдалённый район
                </label>
                <p class="text-xs text-bo-muted">
                    Если зона отдалённая — бесплатная доставка от порога на неё не действует.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <UiButton
                    class="!h-10 !bg-white/10 !text-bo-fg"
                    :disabled="disabled || selectedIndex < 0"
                    @click="startRedrawSelected"
                >
                    Нарисовать контур
                </UiButton>
                <UiButton
                    class="!h-10 !bg-white/10 !text-bo-fg"
                    :disabled="!mapHost"
                    @click="fitZonesOrCenter"
                >
                    Показать все
                </UiButton>
            </div>
        </aside>

        <div class="relative min-h-[32rem] overflow-hidden border border-bo-border bg-bo-field lg:min-h-[40rem]">
            <div ref="mapHost" class="absolute inset-0" />
            <p class="pointer-events-none absolute bottom-3 left-3 right-3 z-10 border border-bo-border bg-bo-card/95 px-3 py-2 text-xs text-bo-muted">
                {{ statusText }}
            </p>
        </div>
    </div>
</template>
