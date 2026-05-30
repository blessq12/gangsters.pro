<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Редактор зоны доставки</title>
    <style>
        html, body, #map { margin: 0; height: 100%; width: 100%; }
        .toolbar {
            position: absolute;
            z-index: 1000;
            top: 12px;
            left: 12px;
            display: flex;
            gap: 8px;
        }
        .toolbar button {
            padding: 8px 12px;
            border: 0;
            border-radius: 8px;
            background: #c62424;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="toolbar">
    <button type="button" id="applyBtn">Применить</button>
    <button type="button" id="clearBtn">Очистить</button>
</div>
<div id="map"></div>
<script>
    const MSG = {
        READY: 'delivery-zone:ready',
        INIT: 'delivery-zone:init',
        CHANGE: 'delivery-zone:change',
    };

    let map;
    let polygon;
    let kitchenCoords = { lat: null, lng: null };

    function post(type, payload = {}) {
        window.parent.postMessage({ type, payload }, window.location.origin);
    }

    function geometryPayload() {
        if (!polygon) {
            return {
                geometry: null,
                kitchenLatitude: kitchenCoords.lat,
                kitchenLongitude: kitchenCoords.lng,
            };
        }

        const coordinates = polygon.geometry.getCoordinates();
        const type = polygon.geometry.getType();

        return {
            geometry: { type, coordinates },
            kitchenLatitude: kitchenCoords.lat,
            kitchenLongitude: kitchenCoords.lng,
        };
    }

    function setGeometry(geometry) {
        if (polygon) {
            map.geoObjects.remove(polygon);
            polygon = null;
        }

        if (!geometry || !geometry.type || !geometry.coordinates) {
            return;
        }

        polygon = new ymaps.Polygon(geometry.coordinates, {}, {
            editorDrawingCursor: 'crosshair',
            editorMaxPoints: 50,
        });
        map.geoObjects.add(polygon);
        polygon.editor.startEditing();
        const bounds = polygon.geometry.getBounds();
        if (bounds) {
            map.setBounds(bounds, { checkZoomRange: true, zoomMargin: 40 });
        }
    }

    function initMap(center) {
        map = new ymaps.Map('map', {
            center,
            zoom: 11,
            controls: ['zoomControl'],
        });
        post(MSG.READY);
    }

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) {
            return;
        }
        const data = event.data;
        if (!data || typeof data.type !== 'string') {
            return;
        }
        if (data.type !== MSG.INIT) {
            return;
        }

        const payload = data.payload || {};
        kitchenCoords.lat = payload.kitchenLatitude ?? null;
        kitchenCoords.lng = payload.kitchenLongitude ?? null;

        const center = kitchenCoords.lat && kitchenCoords.lng
            ? [kitchenCoords.lat, kitchenCoords.lng]
            : [55.751244, 37.618423];

        if (!map) {
            ymaps.ready(() => initMap(center));
            ymaps.ready(() => setGeometry(payload.geometry));
            return;
        }

        map.setCenter(center);
        setGeometry(payload.geometry);
    });

    document.getElementById('applyBtn').addEventListener('click', () => {
        post(MSG.CHANGE, geometryPayload());
    });

    document.getElementById('clearBtn').addEventListener('click', () => {
        if (polygon) {
            map.geoObjects.remove(polygon);
            polygon = null;
        }
        post(MSG.CHANGE, geometryPayload());
    });
</script>
<script src="https://api-maps.yandex.ru/2.1/?apikey={{ urlencode($apiKey) }}&lang=ru_RU"></script>
</body>
</html>
