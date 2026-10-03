<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Редактор зон доставки</title>
    <style>
        :root {
            --bg: #f4f4f5;
            --panel: #ffffff;
            --line: #e4e4e7;
            --text: #18181b;
            --muted: #71717a;
            --brand: #c62424;
            --brand-dark: #9f1c1c;
            --secondary: #3f3f46;
            --ok: #15803d;
            --selected: #fff1f1;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            height: 100%;
            min-height: 640px;
            font-family: Inter, system-ui, -apple-system, sans-serif;
            color: var(--text);
            background: var(--bg);
        }
        .layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            height: 100%;
            min-height: 640px;
        }
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 12px;
            background: var(--panel);
            border-right: 1px solid var(--line);
            overflow: auto;
        }
        .sidebar h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
        }
        .sidebar .hint {
            margin: 0;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.4;
        }
        .zone-list {
            list-style: none;
            margin: 0;
            padding: 0;
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            min-height: 120px;
            max-height: 220px;
            overflow-y: auto;
        }
        .zone-list li {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 10px 12px;
            border-bottom: 1px solid var(--line);
            cursor: pointer;
            background: #fff;
        }
        .zone-list li:last-child { border-bottom: 0; }
        .zone-list li.active {
            background: var(--selected);
            box-shadow: inset 3px 0 0 var(--brand);
        }
        .zone-list li .title {
            font-size: 14px;
            font-weight: 600;
        }
        .zone-list li .meta {
            font-size: 12px;
            color: var(--muted);
        }
        .zone-list .empty {
            padding: 16px 12px;
            font-size: 13px;
            color: var(--muted);
            cursor: default;
        }
        .panel {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .panel h2 {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted);
        }
        .field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .field label {
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
        }
        .field input[type="text"],
        .field input[type="number"] {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            font-size: 14px;
        }
        .field input:disabled {
            background: #f4f4f5;
            color: var(--muted);
        }
        .checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            padding: 4px 0;
        }
        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .actions.single { grid-template-columns: 1fr; }
        button {
            padding: 10px 12px;
            border: 0;
            border-radius: 8px;
            background: var(--brand);
            color: #fff;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
        }
        button:hover { background: var(--brand-dark); }
        button.secondary { background: var(--secondary); }
        button.secondary:hover { background: #27272a; }
        button.ghost {
            background: #fff;
            color: var(--text);
            border: 1px solid var(--line);
        }
        button.ghost:hover { background: #f4f4f5; }
        button:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }
        button.save {
            background: var(--ok);
        }
        button.save:hover { background: #166534; }
        .map-wrap {
            position: relative;
            min-width: 0;
            min-height: 640px;
        }
        #map {
            position: absolute;
            inset: 0;
        }
        #status {
            position: absolute;
            z-index: 1000;
            left: 12px;
            right: 12px;
            bottom: 12px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid var(--line);
            border-radius: 8px;
            font-size: 13px;
            color: #333;
        }
        .dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div>
            <h1>Зоны доставки</h1>
            <p class="hint">Томск. Выберите зону в списке, задайте параметры и контур на карте, затем нажмите «Сохранить».</p>
        </div>

        <ul id="zoneList" class="zone-list"></ul>

        <div class="actions">
            <button type="button" id="addZoneBtn" class="secondary">+ Добавить зону</button>
            <button type="button" id="deleteZoneBtn" class="ghost">Удалить</button>
        </div>

        <section class="panel">
            <h2>Параметры зоны</h2>
            <div class="field">
                <label for="zoneName">Название</label>
                <input type="text" id="zoneName" placeholder="Например: Центр" disabled>
            </div>
            <div class="field">
                <label for="zoneFee">Цена доставки, ₽</label>
                <input type="number" id="zoneFee" min="0" step="1" placeholder="0" disabled>
            </div>
            <label class="checkbox">
                <input type="checkbox" id="zoneRemote" disabled>
                Отдалённый район
            </label>
            <p class="hint">Если зона отдалённая — бесплатная доставка от порога на неё не действует.</p>
        </section>

        <div class="actions">
            <button type="button" id="redrawBtn" class="ghost">Нарисовать контур</button>
            <button type="button" id="fitBtn" class="ghost">Показать все</button>
        </div>
        <div class="actions single">
            <button type="button" id="saveBtn" class="save">Сохранить</button>
        </div>
        <button type="button" id="clearBtn" class="ghost">Очистить все зоны</button>
    </aside>

    <div class="map-wrap">
        <div id="map"></div>
        <p id="status">Загрузка карты…</p>
    </div>
</div>

<script src="/js/maps/yandexGeoJsonCoords.js"></script>
@if(filled($mapsApiKey))
<script src="https://api-maps.yandex.ru/2.1/?apikey={{ urlencode($mapsApiKey) }}&lang=ru_RU&load=package.full"></script>
@endif
<script>
    const MSG = {
        READY: 'delivery-zone:ready',
        INIT: 'delivery-zone:init',
        CHANGE: 'delivery-zone:change',
        REQUEST_SNAPSHOT: 'delivery-zone:request-snapshot',
        SNAPSHOT: 'delivery-zone:snapshot',
    };

    const COORDS = window.GangstersMapsCoords;
    const MAPS_API_KEY = @json($mapsApiKey);
    const TOMSK_CENTER = COORDS.TOMSK_CENTER;
    const TOMSK_ZOOM = 12;
    const ZONE_COLORS = ['#C62424', '#2563EB', '#059669', '#D97706', '#7C3AED', '#DB2777'];

    let map;
    let kitchenPlacemark;
    let kitchenCoords = { lat: null, lng: null };
    let zones = [];
    let selectedIndex = -1;
    /** @type {Map<string, any>} */
    const polygonById = new Map();
    /** @type {Map<string, Function[]>} */
    const polygonListenerDisposers = new Map();
    let autoSyncTimer = null;
    let suppressOutboundChange = false;
    let awaitingInit = true;
    let readyHeartbeatTimer = null;
    let initialized = false;
    let drawing = false;
    let dirty = false;

    const statusEl = document.getElementById('status');
    const zoneListEl = document.getElementById('zoneList');
    const zoneName = document.getElementById('zoneName');
    const zoneFee = document.getElementById('zoneFee');
    const zoneRemote = document.getElementById('zoneRemote');
    const deleteZoneBtn = document.getElementById('deleteZoneBtn');
    const redrawBtn = document.getElementById('redrawBtn');
    const saveBtn = document.getElementById('saveBtn');

    function setStatus(text) {
        statusEl.textContent = text;
    }

    function post(type, payload = {}) {
        window.parent.postMessage({ type, payload }, window.location.origin);
    }

    function signalReady() {
        if (!awaitingInit) {
            return;
        }
        post(MSG.READY);
    }

    function startReadyHeartbeat() {
        signalReady();
        if (!readyHeartbeatTimer) {
            readyHeartbeatTimer = window.setInterval(signalReady, 800);
        }
    }

    function stopReadyHeartbeat() {
        awaitingInit = false;
        if (readyHeartbeatTimer) {
            window.clearInterval(readyHeartbeatTimer);
            readyHeartbeatTimer = null;
        }
    }

    function createZoneId() {
        if (typeof crypto !== 'undefined' && crypto.randomUUID) {
            return crypto.randomUUID();
        }
        return 'zone-' + Date.now() + '-' + Math.floor(Math.random() * 1e6);
    }

    function hasRenderableGeometry(geometry) {
        if (!geometry || typeof geometry !== 'object') {
            return false;
        }
        const coords = COORDS.geometryToYmapsPolygonCoords(geometry);
        return coords.length > 0 && coords[0].length >= 3;
    }

    function isNearTomskCenter(center) {
        if (!center || center.length < 2) {
            return false;
        }
        const lat = Number(center[0]);
        const lng = Number(center[1]);
        return lat >= 55 && lat <= 58 && lng >= 82 && lng <= 87;
    }

    function normalizeIncomingZones(rawZones) {
        if (!Array.isArray(rawZones)) {
            return [];
        }

        return rawZones
            .filter((z) => z && typeof z === 'object' && z.geometry && z.geometry.type)
            .map((z, index) => ({
                id: String(z.id || createZoneId()),
                name: String(z.name || ('Зона ' + (index + 1))),
                delivery_fee_kopecks: Math.max(0, Number(z.delivery_fee_kopecks) || 0),
                is_remote: Boolean(z.is_remote),
                geometry: z.geometry,
            }));
    }

    function zonesPayload() {
        persistAllPolygonGeometries();
        applySelectedFieldsToZone();

        return {
            zones: zones.map((z) => ({
                id: z.id,
                name: z.name,
                delivery_fee_kopecks: z.delivery_fee_kopecks,
                is_remote: z.is_remote,
                geometry: z.geometry,
            })),
            kitchenLatitude: kitchenCoords.lat,
            kitchenLongitude: kitchenCoords.lng,
        };
    }

    function markDirty() {
        dirty = true;
        saveBtn.textContent = 'Сохранить*';
    }

    function clearDirty() {
        dirty = false;
        saveBtn.textContent = 'Сохранить';
    }

    function notifyChange(immediate = false) {
        if (suppressOutboundChange) {
            return;
        }

        markDirty();

        const send = () => post(MSG.CHANGE, zonesPayload());

        if (immediate) {
            if (autoSyncTimer) {
                clearTimeout(autoSyncTimer);
                autoSyncTimer = null;
            }
            send();
            return;
        }

        if (autoSyncTimer) {
            clearTimeout(autoSyncTimer);
        }
        autoSyncTimer = setTimeout(send, 400);
    }

    function setFormEnabled(enabled) {
        zoneName.disabled = !enabled;
        zoneFee.disabled = !enabled;
        zoneRemote.disabled = !enabled;
        deleteZoneBtn.disabled = !enabled;
        redrawBtn.disabled = !enabled;
    }

    function syncSelectedFieldsFromZone() {
        const zone = zones[selectedIndex];
        if (!zone) {
            zoneName.value = '';
            zoneFee.value = '';
            zoneRemote.checked = false;
            setFormEnabled(false);
            return;
        }

        setFormEnabled(true);
        zoneName.value = zone.name;
        zoneFee.value = String(Math.round(zone.delivery_fee_kopecks / 100));
        zoneRemote.checked = zone.is_remote;
    }

    function applySelectedFieldsToZone() {
        const zone = zones[selectedIndex];
        if (!zone) {
            return;
        }
        const name = zoneName.value.trim();
        zone.name = name !== '' ? name : zone.name;
        const feeRub = Number(zoneFee.value);
        zone.delivery_fee_kopecks = Number.isFinite(feeRub)
            ? Math.max(0, Math.round(feeRub * 100))
            : zone.delivery_fee_kopecks;
        zone.is_remote = zoneRemote.checked;
    }

    function zoneColor(index) {
        return ZONE_COLORS[index % ZONE_COLORS.length];
    }

    function renderZoneList() {
        zoneListEl.innerHTML = '';

        if (zones.length === 0) {
            const empty = document.createElement('li');
            empty.className = 'empty';
            empty.textContent = 'Зон пока нет. Нажмите «Добавить зону».';
            zoneListEl.appendChild(empty);
            return;
        }

        zones.forEach((zone, index) => {
            const item = document.createElement('li');
            if (index === selectedIndex) {
                item.classList.add('active');
            }

            const feeRub = Math.round(zone.delivery_fee_kopecks / 100);
            const hasContour = hasRenderableGeometry(zone.geometry);
            const title = document.createElement('div');
            title.className = 'title';
            title.innerHTML = `<span class="dot" style="background:${zoneColor(index)}"></span>${escapeHtml(zone.name)}`;

            const meta = document.createElement('div');
            meta.className = 'meta';
            meta.textContent = [
                `${feeRub} ₽`,
                zone.is_remote ? 'отдалённый' : 'обычный',
                hasContour ? 'контур задан' : 'нет контура',
            ].join(' · ');

            item.appendChild(title);
            item.appendChild(meta);
            item.addEventListener('click', () => selectZone(index));
            zoneListEl.appendChild(item);
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;');
    }

    function refreshSidebar() {
        renderZoneList();
        syncSelectedFieldsFromZone();
    }

    function detachPolygonListeners(zoneId) {
        const disposers = polygonListenerDisposers.get(zoneId) || [];
        disposers.forEach((dispose) => {
            try { dispose(); } catch (error) { /* ignore */ }
        });
        polygonListenerDisposers.delete(zoneId);
    }

    function stopAllEditing() {
        polygonById.forEach((polygon) => {
            if (polygon?.editor) {
                try {
                    polygon.editor.stopEditing();
                    polygon.editor.stopDrawing();
                } catch (error) {
                    // ignore
                }
            }
        });
        drawing = false;
    }

    function persistPolygonGeometry(zone, polygon) {
        if (!zone || !polygon) {
            return false;
        }
        const ymapsCoords = polygon.geometry.getCoordinates();
        const type = polygon.geometry.getType();
        const geometry = COORDS.ymapsGeometryToGeoJson(type, ymapsCoords);
        if (!geometry) {
            return false;
        }
        zone.geometry = geometry;
        return true;
    }

    function persistAllPolygonGeometries() {
        zones.forEach((zone) => {
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
            refreshSidebar();
            notifyChange(true);
            setStatus('Контур готов. Можно подвинуть вершины или нажать «Сохранить».');
        };
        const onGeometryChange = () => {
            persistPolygonGeometry(zone, polygon);
            notifyChange(false);
        };

        polygon.editor.events.add('drawingstop', onDrawingStop);
        polygon.editor.events.add('geometrychange', onGeometryChange);

        polygonListenerDisposers.set(zone.id, [
            () => polygon.editor.events.remove('drawingstop', onDrawingStop),
            () => polygon.editor.events.remove('geometrychange', onGeometryChange),
        ]);
    }

    function createPolygonForZone(zone, index, { editable = false } = {}) {
        const ymapsPolygonCoords = COORDS.geometryToYmapsPolygonCoords(zone.geometry);
        if (!ymapsPolygonCoords.length || !ymapsPolygonCoords[0]?.length) {
            return null;
        }

        const color = zoneColor(index);
        const polygon = new ymaps.Polygon(ymapsPolygonCoords, {
            hintContent: zone.name,
        }, {
            editorDrawingCursor: 'crosshair',
            editorMaxPoints: 80,
            fillColor: color + '44',
            strokeColor: color,
            strokeWidth: editable ? 3 : 2,
            draggable: false,
        });

        map.geoObjects.add(polygon);
        polygonById.set(zone.id, polygon);
        attachPolygonListeners(zone, polygon);

        if (editable && polygon.editor) {
            try {
                polygon.editor.startEditing();
            } catch (error) {
                setStatus('Не удалось включить редактор вершин. Нажмите «Нарисовать контур».');
            }
        }

        return polygon;
    }

    function focusTomsk() {
        if (!map) {
            return;
        }
        map.setCenter(TOMSK_CENTER, TOMSK_ZOOM, { duration: 0 });
    }

    function fitZonesOrTomsk() {
        if (!map) {
            return;
        }

        if (polygonById.size === 0) {
            focusTomsk();
            return;
        }

        const bounds = map.geoObjects.getBounds();
        if (!bounds) {
            focusTomsk();
            return;
        }

        const center = [
            (bounds[0][0] + bounds[1][0]) / 2,
            (bounds[0][1] + bounds[1][1]) / 2,
        ];

        if (!isNearTomskCenter(center)) {
            focusTomsk();
            return;
        }

        map.setBounds(bounds, { checkZoomRange: true, zoomMargin: 48 });
    }

    function rebuildPolygons({ fitBounds = false, preserveEditing = true } = {}) {
        if (!map) {
            return;
        }

        stopAllEditing();
        polygonById.forEach((_, zoneId) => detachPolygonListeners(zoneId));
        polygonById.forEach((polygon) => map.geoObjects.remove(polygon));
        polygonById.clear();

        zones.forEach((zone, index) => {
            createPolygonForZone(zone, index, {
                editable: preserveEditing && index === selectedIndex,
            });
        });

        if (fitBounds) {
            fitZonesOrTomsk();
        }
    }

    function activateSelectedEditing() {
        if (!map || selectedIndex < 0) {
            return;
        }

        const zone = zones[selectedIndex];
        if (!zone) {
            return;
        }

        stopAllEditing();

        polygonById.forEach((polygon, zoneId) => {
            const index = zones.findIndex((item) => item.id === zoneId);
            const color = zoneColor(index >= 0 ? index : 0);
            polygon.options.set('strokeWidth', zoneId === zone.id ? 3 : 2);
            polygon.options.set('strokeColor', color);
            polygon.options.set('fillColor', color + '44');
        });

        let polygon = polygonById.get(zone.id);
        if (!polygon && hasRenderableGeometry(zone.geometry)) {
            polygon = createPolygonForZone(zone, selectedIndex, { editable: false });
        }

        if (!polygon) {
            setStatus('У зоны нет контура. Нажмите «Нарисовать контур» и кликайте по карте.');
            return;
        }

        attachPolygonListeners(zone, polygon);

        try {
            polygon.editor.startEditing();
            setStatus('Выбрана зона «' + zone.name + '». Тяните вершины или измените параметры слева.');
        } catch (error) {
            setStatus('Редактор вершин недоступен. Нажмите «Нарисовать контур».');
        }
    }

    function removeKitchenPlacemark() {
        if (kitchenPlacemark && map) {
            map.geoObjects.remove(kitchenPlacemark);
        }
        kitchenPlacemark = null;
    }

    function updateKitchenPlacemark() {
        removeKitchenPlacemark();
        if (!map || kitchenCoords.lat == null || kitchenCoords.lng == null) {
            return;
        }
        if (!isNearTomskCenter([kitchenCoords.lat, kitchenCoords.lng])) {
            return;
        }

        kitchenPlacemark = new ymaps.Placemark(
            [kitchenCoords.lat, kitchenCoords.lng],
            { hintContent: 'Кухня' },
            { preset: 'islands#redDotIcon' },
        );
        map.geoObjects.add(kitchenPlacemark);
    }

    function setKitchenCoords(lat, lng) {
        kitchenCoords.lat = lat;
        kitchenCoords.lng = lng;
        updateKitchenPlacemark();
    }

    function selectZone(index) {
        if (!Number.isFinite(index) || index < 0 || index >= zones.length) {
            return;
        }

        persistAllPolygonGeometries();
        applySelectedFieldsToZone();
        selectedIndex = index;
        refreshSidebar();
        activateSelectedEditing();
    }

    function addZone() {
        persistAllPolygonGeometries();
        applySelectedFieldsToZone();

        const zone = {
            id: createZoneId(),
            name: 'Зона ' + (zones.length + 1),
            delivery_fee_kopecks: 40000,
            is_remote: false,
            geometry: {
                type: 'Polygon',
                coordinates: [],
            },
        };
        zones.push(zone);
        selectedIndex = zones.length - 1;
        refreshSidebar();
        focusTomsk();
        startRedrawSelected();
        notifyChange(true);
        setStatus('Новая зона создана. Кликайте по карте Томска, чтобы задать контур.');
    }

    function deleteSelectedZone() {
        if (selectedIndex < 0 || selectedIndex >= zones.length) {
            return;
        }

        const zone = zones[selectedIndex];
        const polygon = polygonById.get(zone.id);
        detachPolygonListeners(zone.id);
        if (polygon && map) {
            try { polygon.editor?.stopEditing(); } catch (error) { /* ignore */ }
            map.geoObjects.remove(polygon);
        }
        polygonById.delete(zone.id);

        zones.splice(selectedIndex, 1);
        selectedIndex = zones.length > 0 ? Math.min(selectedIndex, zones.length - 1) : -1;
        refreshSidebar();
        rebuildPolygons({ fitBounds: true, preserveEditing: true });
        notifyChange(true);
        setStatus(zones.length ? 'Зона удалена.' : 'Все зоны удалены.');
    }

    function startRedrawSelected() {
        if (!map || selectedIndex < 0) {
            setStatus('Сначала добавьте зону в списке слева.');
            return;
        }

        const zone = zones[selectedIndex];
        stopAllEditing();
        focusTomsk();

        const existing = polygonById.get(zone.id);
        if (existing) {
            detachPolygonListeners(zone.id);
            map.geoObjects.remove(existing);
            polygonById.delete(zone.id);
        }

        drawing = true;
        const color = zoneColor(selectedIndex);
        const polygon = new ymaps.Polygon([], {}, {
            editorDrawingCursor: 'crosshair',
            editorMaxPoints: 80,
            fillColor: color + '44',
            strokeColor: color,
            strokeWidth: 3,
        });
        map.geoObjects.add(polygon);
        polygonById.set(zone.id, polygon);

        const onDrawingStop = () => {
            drawing = false;
            persistPolygonGeometry(zone, polygon);
            attachPolygonListeners(zone, polygon);
            try { polygon.editor.startEditing(); } catch (error) { /* ignore */ }
            refreshSidebar();
            notifyChange(true);
            setStatus('Контур готов. Проверьте параметры слева и нажмите «Сохранить».');
        };

        polygon.editor.events.add('drawingstop', onDrawingStop);
        polygonListenerDisposers.set(zone.id, [
            () => polygon.editor.events.remove('drawingstop', onDrawingStop),
        ]);
        polygon.editor.startDrawing();
    }

    function saveZones() {
        const payload = zonesPayload();
        const incomplete = payload.zones.some((z) => !hasRenderableGeometry(z.geometry));
        if (incomplete) {
            setStatus('Нельзя сохранить: есть зоны без контура. Нарисуйте контур или удалите зону.');
            return;
        }

        post(MSG.CHANGE, payload);
        clearDirty();
        setStatus('Зоны сохранены в форму. Нажмите «Сохранить» внизу страницы Filament, чтобы записать в базу.');
    }

    function applyInitPayload(payload, { force = false } = {}) {
        if (initialized && !force) {
            return;
        }

        suppressOutboundChange = true;

        try {
            setKitchenCoords(
                payload.kitchenLatitude ?? null,
                payload.kitchenLongitude ?? null,
            );

            // Всегда стартуем с Томска — не «прыгаем» на мир.
            focusTomsk();

            zones = normalizeIncomingZones(payload.zones);
            selectedIndex = zones.length > 0 ? 0 : -1;
            refreshSidebar();
            rebuildPolygons({ fitBounds: zones.length > 0, preserveEditing: true });
            initialized = true;
            clearDirty();

            if (!zones.length) {
                focusTomsk();
                setStatus('Карта Томска. Добавьте зону слева и нарисуйте контур.');
            } else {
                setStatus('Зоны загружены. Выберите зону в списке слева для правки.');
            }
        } finally {
            suppressOutboundChange = false;
        }
    }

    function initMap() {
        map = new ymaps.Map('map', {
            center: TOMSK_CENTER,
            zoom: TOMSK_ZOOM,
            controls: ['zoomControl', 'typeSelector'],
        }, {
            // Ограничиваем pan примерно областью Томска.
            restrictMapArea: [
                [55.8, 83.8],
                [57.0, 86.2],
            ],
        });
        startReadyHeartbeat();
        setStatus('Карта Томска загружена. Ожидание данных формы…');
    }

    function bootstrapMap() {
        if (map) {
            return;
        }

        if (!MAPS_API_KEY) {
            setStatus('Не задан YANDEX_MAPS_API_KEY');
            return;
        }

        if (typeof ymaps === 'undefined') {
            setStatus('Не удалось загрузить API Яндекс.Карт');
            return;
        }

        ymaps.ready(() => {
            if (map) {
                return;
            }
            initMap();
        });
    }

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) {
            return;
        }
        const data = event.data;
        if (!data || typeof data.type !== 'string') {
            return;
        }

        if (data.type === MSG.REQUEST_SNAPSHOT) {
            post(MSG.SNAPSHOT, zonesPayload());
            return;
        }

        if (data.type !== MSG.INIT) {
            return;
        }

        stopReadyHeartbeat();
        const payload = data.payload || {};

        if (!map) {
            ymaps.ready(() => {
                if (!map) {
                    initMap();
                }
                applyInitPayload(payload, { force: !initialized });
            });
            return;
        }

        applyInitPayload(payload, { force: !initialized });
    });

    ['change', 'input'].forEach((eventName) => {
        zoneName.addEventListener(eventName, () => {
            applySelectedFieldsToZone();
            renderZoneList();
            notifyChange(false);
        });
        zoneFee.addEventListener(eventName, () => {
            applySelectedFieldsToZone();
            renderZoneList();
            notifyChange(false);
        });
    });

    zoneRemote.addEventListener('change', () => {
        applySelectedFieldsToZone();
        renderZoneList();
        notifyChange(true);
    });

    document.getElementById('addZoneBtn').addEventListener('click', () => {
        if (!map) {
            return;
        }
        addZone();
    });

    document.getElementById('deleteZoneBtn').addEventListener('click', () => {
        deleteSelectedZone();
    });

    document.getElementById('redrawBtn').addEventListener('click', () => {
        if (!map) {
            return;
        }
        if (selectedIndex < 0) {
            addZone();
            return;
        }
        startRedrawSelected();
        setStatus('Кликайте по карте Томска, чтобы задать контур.');
    });

    document.getElementById('fitBtn').addEventListener('click', () => {
        fitZonesOrTomsk();
    });

    document.getElementById('saveBtn').addEventListener('click', () => {
        saveZones();
    });

    document.getElementById('clearBtn').addEventListener('click', () => {
        if (!confirm('Удалить все зоны?')) {
            return;
        }
        stopAllEditing();
        polygonById.forEach((_, zoneId) => detachPolygonListeners(zoneId));
        polygonById.forEach((polygon) => map?.geoObjects.remove(polygon));
        polygonById.clear();
        zones = [];
        selectedIndex = -1;
        refreshSidebar();
        focusTomsk();
        notifyChange(true);
        setStatus('Все зоны очищены.');
    });

    bootstrapMap();
</script>
</body>
</html>
