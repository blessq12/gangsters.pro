<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Редактор зоны доставки</title>
    <style>
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            min-height: 500px;
            height: 100%;
            font-family: system-ui, sans-serif;
            background: #1a1a1a;
            color: #ececec;
            display: flex;
            flex-direction: column;
        }
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.5rem;
            background: #242424;
            border-bottom: 1px solid #333;
        }
        .toolbar button {
            padding: 0.4rem 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #444;
            background: #333;
            color: #ececec;
            cursor: pointer;
            font-size: 0.8125rem;
        }
        .toolbar button:hover:not(:disabled) { background: #444; }
        .toolbar button:disabled { opacity: 0.5; cursor: not-allowed; }
        .toolbar button.primary { background: #C62424; border-color: #a01e1e; }
        .toolbar button.primary:hover:not(:disabled) { background: #a01e1e; }
        #map {
            flex: 1 1 auto;
            width: 100%;
            min-height: 440px;
            height: 440px;
        }
        .status {
            flex: 1 1 100%;
            font-size: 0.75rem;
            color: #aaa;
            padding: 0.15rem 0.25rem 0;
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" id="btn-draw">Нарисовать зону</button>
        <button type="button" id="btn-edit" disabled>Редактировать</button>
        <button type="button" id="btn-clear" disabled>Очистить</button>
        <button type="button" id="btn-center">Центр по адресу</button>
        <button type="button" id="btn-apply" class="primary" disabled>Применить</button>
        <p class="status" id="status"></p>
    </div>
    <div id="map"></div>

    <script>
        window.__DELIVERY_ZONE_EDITOR__ = @json($editorConfig);
    </script>
    <script src="{{ asset('js/maps/yandex-coords.js') }}?v=2"></script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ urlencode($apiKey) }}&lang=ru_RU"></script>
    <script src="{{ asset('js/admin/delivery-zone-map-editor.js') }}?v=6"></script>
</body>
</html>
