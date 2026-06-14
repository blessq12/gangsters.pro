<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Редактор зоны доставки</title>
    <style>
        html, body {
            margin: 0;
            height: 100%;
            min-height: 480px;
        }
        body {
            position: relative;
        }
        #map {
            position: absolute;
            inset: 0;
        }
        .toolbar {
            position: absolute;
            z-index: 1000;
            top: 12px;
            left: 12px;
            right: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .toolbar input[type="text"] {
            min-width: 220px;
            flex: 1;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }
        .toolbar button {
            padding: 8px 12px;
            border: 0;
            border-radius: 8px;
            background: #c62424;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }
        .toolbar button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        #status {
            position: absolute;
            z-index: 1000;
            bottom: 12px;
            left: 12px;
            right: 12px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.92);
            border-radius: 8px;
            font-size: 13px;
            color: #333;
        }
    </style>
</head>
<body>
<div class="toolbar">
    <input type="text" id="addressInput" placeholder="Адрес кухни (геокодер)" value="">
    <button type="button" id="geocodeBtn">Найти</button>
    <button type="button" id="drawBtn">Нарисовать зону</button>
    <button type="button" id="applyBtn">Применить</button>
    <button type="button" id="clearBtn">Очистить</button>
</div>
<div id="map"></div>
<p id="status">Загрузка карты…</p>
<script src="{{ asset('js/maps/yandexGeoJsonCoords.js') }}"></script>
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
    const GEOCODER_KEY = @json($geocoderApiKey);
    const DEFAULT_CENTER = COORDS.TOMSK_CENTER;

    let map;
    let polygon;
    let kitchenPlacemark;
    let kitchenCoords = { lat: null, lng: null };
    let multiPolygonWarning = false;
    let autoSyncTimer = null;
    let suppressOutboundChange = false;
    let awaitingInit = true;
    let readyHeartbeatTimer = null;

    const statusEl = document.getElementById('status');
    const addressInput = document.getElementById('addressInput');

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

        if (readyHeartbeatTimer) {
            return;
        }

        readyHeartbeatTimer = window.setInterval(signalReady, 800);
    }

    function stopReadyHeartbeat() {
        awaitingInit = false;

        if (readyHeartbeatTimer) {
            window.clearInterval(readyHeartbeatTimer);
            readyHeartbeatTimer = null;
        }
    }

    function resolveMapCenter(payload) {
        const fromKitchen = COORDS.pairToYmapsCenter(
            payload.kitchenLatitude,
            payload.kitchenLongitude,
        );
        if (fromKitchen) {
            return fromKitchen;
        }

        return DEFAULT_CENTER;
    }

    function geometryPayload() {
        if (!polygon) {
            return {
                geometry: null,
                kitchenLatitude: kitchenCoords.lat,
                kitchenLongitude: kitchenCoords.lng,
            };
        }

        const ymapsCoords = polygon.geometry.getCoordinates();
        const type = polygon.geometry.getType();
        const geometry = COORDS.ymapsGeometryToGeoJson(type, ymapsCoords);

        return {
            geometry,
            kitchenLatitude: kitchenCoords.lat,
            kitchenLongitude: kitchenCoords.lng,
        };
    }

    function notifyChange(immediate = false) {
        if (suppressOutboundChange) {
            return;
        }

        const send = () => {
            const payload = geometryPayload();
            post(MSG.CHANGE, payload);
        };

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

    function attachPolygonEditorListeners() {
        if (!polygon?.editor?.events) {
            return;
        }

        polygon.editor.events.add('drawingstop', () => notifyChange(true));
        polygon.editor.events.add('geometrychange', () => notifyChange(false));
    }

    function removePolygon() {
        if (polygon && map) {
            map.geoObjects.remove(polygon);
        }
        polygon = null;
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

    function setGeometry(geoJsonGeometry) {
        removePolygon();

        if (!geoJsonGeometry || !geoJsonGeometry.type || !geoJsonGeometry.coordinates) {
            if (map) {
                setStatus('Зоны нет. Нажмите «Нарисовать зону» или найдите адрес кухни.');
            }
            return;
        }

        if (!map) {
            return;
        }

        if (geoJsonGeometry.type === 'MultiPolygon') {
            multiPolygonWarning = true;
            setStatus('В БД MultiPolygon: редактируется только первый контур. Сохранение запишет Polygon.');
        }

        const ymapsPolygonCoords = COORDS.geometryToYmapsPolygonCoords(geoJsonGeometry);
        if (!ymapsPolygonCoords.length || !ymapsPolygonCoords[0]?.length) {
            setStatus('Не удалось отобразить зону. Нарисуйте заново.');
            return;
        }

        polygon = new ymaps.Polygon(ymapsPolygonCoords, {}, {
            editorDrawingCursor: 'crosshair',
            editorMaxPoints: 50,
        });
        map.geoObjects.add(polygon);

        if (polygon.editor) {
            try {
                polygon.editor.startEditing();
                attachPolygonEditorListeners();
            } catch (error) {
                setStatus('Зона загружена, но редактор недоступен.');
            }
        }

        const bounds = polygon.geometry.getBounds();
        if (bounds) {
            map.setBounds(bounds, { checkZoomRange: true, zoomMargin: 40 });
        }

        if (!multiPolygonWarning) {
            setStatus('Зона загружена. Отредактируйте контур — изменения синхронизируются автоматически.');
        }
    }

    function startDrawing() {
        if (!map) {
            return;
        }

        removePolygon();
        multiPolygonWarning = false;

        polygon = new ymaps.Polygon([], {}, {
            editorDrawingCursor: 'crosshair',
            editorMaxPoints: 50,
        });
        map.geoObjects.add(polygon);
        polygon.editor.startDrawing();
        polygon.editor.events.add('drawingstop', () => {
            attachPolygonEditorListeners();
            notifyChange(true);
        });
        setStatus('Кликайте по карте, чтобы задать контур. После завершения рисования зона синхронизируется с формой.');
    }

    function initMap(center) {
        map = new ymaps.Map('map', {
            center,
            zoom: 11,
            controls: ['zoomControl'],
        });
        startReadyHeartbeat();
        setStatus('Ожидание данных формы…');
    }

    function applyInitPayload(payload) {
        suppressOutboundChange = true;

        try {
            const address = payload.address ?? '';
            if (address) {
                addressInput.value = address;
            }

            setKitchenCoords(
                payload.kitchenLatitude ?? null,
                payload.kitchenLongitude ?? null,
            );

            const center = resolveMapCenter(payload);
            if (map) {
                map.setCenter(center);
            }

            setGeometry(payload.geometry ?? null);

            if (!payload.geometry && map) {
                setStatus('Зоны нет. «Нарисовать зону» или укажите адрес и «Найти».');
            }
        } finally {
            suppressOutboundChange = false;
        }
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
            initMap(DEFAULT_CENTER);
            setStatus('Ожидание данных формы…');
        });
    }

    async function geocodeAddress() {
        const address = addressInput.value.trim();
        if (!address) {
            setStatus('Введите адрес для поиска.');
            return;
        }

        if (!GEOCODER_KEY) {
            setStatus('Ключ геокодера не настроен (YANDEX_GEOCODER_API_KEY).');
            return;
        }

        if (!map) {
            setStatus('Сначала дождитесь загрузки карты.');
            return;
        }

        setStatus('Поиск адреса…');

        try {
            const url = new URL('https://geocode-maps.yandex.ru/1.x/');
            url.searchParams.set('apikey', GEOCODER_KEY);
            url.searchParams.set('geocode', address);
            url.searchParams.set('format', 'json');
            url.searchParams.set('lang', 'ru_RU');
            url.searchParams.set('results', '1');

            const response = await fetch(url.toString());
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const data = await response.json();
            const member = data?.response?.GeoObjectCollection?.featureMember?.[0];
            const pos = member?.GeoObject?.Point?.pos;

            if (!pos || typeof pos !== 'string') {
                setStatus('Адрес не найден.');
                return;
            }

            const parts = pos.trim().split(/\s+/);
            const lng = parseFloat(parts[0]);
            const lat = parseFloat(parts[1]);

            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                setStatus('Некорректный ответ геокодера.');
                return;
            }

            setKitchenCoords(lat, lng);
            map.setCenter([lat, lng], 14, { duration: 300 });
            notifyChange(true);
            setStatus('Точка кухни найдена. Нарисуйте зону или сохраните форму.');
        } catch (error) {
            setStatus('Ошибка геокодера: ' + (error?.message || 'сеть'));
        }
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
            post(MSG.SNAPSHOT, geometryPayload());
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
                    initMap(resolveMapCenter(payload));
                }
                applyInitPayload(payload);
            });
            return;
        }

        applyInitPayload(payload);
    });

    document.getElementById('drawBtn').addEventListener('click', () => {
        if (!map) {
            return;
        }
        startDrawing();
    });

    document.getElementById('applyBtn').addEventListener('click', () => {
        const payload = geometryPayload();
        if (polygon && !payload.geometry) {
            setStatus('Контур слишком короткий: минимум 4 точки (замкнутый полигон).');
            return;
        }
        notifyChange(true);
        setStatus('Данные отправлены в форму. Нажмите «Сохранить» внизу страницы.');
    });

    document.getElementById('clearBtn').addEventListener('click', () => {
        removePolygon();
        multiPolygonWarning = false;
        notifyChange(true);
        setStatus('Зона очищена.');
    });

    document.getElementById('geocodeBtn').addEventListener('click', () => {
        geocodeAddress();
    });

    addressInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            geocodeAddress();
        }
    });

    bootstrapMap();
</script>
</body>
</html>
