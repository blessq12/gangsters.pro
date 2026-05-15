/**
 * Координаты для Яндекс.Карт: API — [широта, долгота], GeoJSON — [долгота, широта].
 */
(function () {
    /** Томск [широта, долгота] */
    const TOMSK_CENTER = [56.49771, 84.97437];

    const TOMSK_AREA = {
        latMin: 55.0,
        latMax: 58.0,
        lonMin: 82.0,
        lonMax: 87.0,
    };

    function isFinitePair(a, b) {
        return Number.isFinite(a) && Number.isFinite(b);
    }

    function looksLikeLatitude(value) {
        return value >= 41 && value <= 82;
    }

    function looksLikeLongitude(value) {
        return value >= 19 && value <= 180;
    }

    function isValidYmapsCenter(coords) {
        if (!coords || coords.length < 2) {
            return false;
        }
        const lat = Number(coords[0]);
        const lng = Number(coords[1]);
        return (
            isFinitePair(lat, lng) &&
            looksLikeLatitude(lat) &&
            looksLikeLongitude(lng)
        );
    }

    function isNearTomskArea(coords) {
        if (!isValidYmapsCenter(coords)) {
            return false;
        }
        const lat = Number(coords[0]);
        const lng = Number(coords[1]);
        return (
            lat >= TOMSK_AREA.latMin &&
            lat <= TOMSK_AREA.latMax &&
            lng >= TOMSK_AREA.lonMin &&
            lng <= TOMSK_AREA.lonMax
        );
    }

    /**
     * GeoJSON position [долгота, широта] → [широта, долгота] для ymaps.
     */
    function geoJsonPositionToYmaps(position) {
        if (!position || position.length < 2) {
            return null;
        }
        const a = Number(position[0]);
        const b = Number(position[1]);
        if (!isFinitePair(a, b)) {
            return null;
        }

        if (looksLikeLongitude(a) && looksLikeLatitude(b)) {
            const coords = [b, a];
            return isValidYmapsCenter(coords) ? coords : null;
        }

        if (looksLikeLatitude(a) && looksLikeLongitude(b)) {
            const coords = [a, b];
            return isValidYmapsCenter(coords) ? coords : null;
        }

        return null;
    }

    function pairToYmapsCenter(first, second) {
        if (first == null || second == null) {
            return null;
        }
        const lat = Number(first);
        const lng = Number(second);
        if (!isFinitePair(lat, lng)) {
            return null;
        }

        if (looksLikeLatitude(lat) && looksLikeLongitude(lng)) {
            const coords = [lat, lng];
            return isValidYmapsCenter(coords) ? coords : null;
        }

        if (looksLikeLatitude(lng) && looksLikeLongitude(lat)) {
            const coords = [lng, lat];
            return isValidYmapsCenter(coords) ? coords : null;
        }

        return null;
    }

    function geometryToYmapsRing(geometry) {
        if (!geometry?.coordinates) {
            return [];
        }
        let ring = [];
        if (geometry.type === "Polygon") {
            ring = geometry.coordinates[0] || [];
        } else if (geometry.type === "MultiPolygon") {
            ring = geometry.coordinates[0]?.[0] || [];
        }
        return ring
            .map((pos) => geoJsonPositionToYmaps(pos))
            .filter((c) => c !== null);
    }

    function ringIsNearTomsk(ring) {
        if (!ring || ring.length < 3) {
            return false;
        }
        return ring.every((coords) => isNearTomskArea(coords));
    }

    window.GangstersYandexCoords = {
        TOMSK_CENTER,
        TOMSK_AREA,
        isValidYmapsCenter,
        isNearTomskArea,
        geoJsonPositionToYmaps,
        pairToYmapsCenter,
        geometryToYmapsRing,
        ringIsNearTomsk,
    };
})();
