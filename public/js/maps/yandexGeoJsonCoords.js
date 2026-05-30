/**
 * GeoJSON [longitude, latitude] <-> Yandex Maps [latitude, longitude].
 * Keep in sync with resources/js/utils/maps/yandexCoords.js
 */
(function (global) {
    const TOMSK_CENTER = [56.49771, 84.97437];

    function isFinitePair(a, b) {
        return Number.isFinite(a) && Number.isFinite(b);
    }

    function looksLikeLatitude(value) {
        return value >= 41 && value <= 82;
    }

    function looksLikeLongitude(value) {
        return value >= 19 && value <= 180;
    }

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

    function ymapsPositionToGeoJson(position) {
        if (!position || position.length < 2) {
            return null;
        }
        const lat = Number(position[0]);
        const lng = Number(position[1]);
        if (!isFinitePair(lat, lng)) {
            return null;
        }
        if (!looksLikeLatitude(lat) || !looksLikeLongitude(lng)) {
            return null;
        }

        return [lng, lat];
    }

    function geometryToYmapsPolygonCoords(geometry) {
        if (!geometry || !geometry.coordinates) {
            return [];
        }

        let ring = [];
        if (geometry.type === "Polygon") {
            ring = geometry.coordinates[0] || [];
        } else if (geometry.type === "MultiPolygon") {
            ring = geometry.coordinates[0]?.[0] || [];
        }

        const ymapsRing = ring
            .map((pos) => geoJsonPositionToYmaps(pos))
            .filter((c) => c !== null);

        return ymapsRing.length >= 3 ? [ymapsRing] : [];
    }

    function closeGeoJsonRing(ring) {
        if (!ring || ring.length < 3) {
            return null;
        }

        const closed = ring.map((p) => [p[0], p[1]]);
        const first = closed[0];
        const last = closed[closed.length - 1];

        if (first[0] !== last[0] || first[1] !== last[1]) {
            closed.push([first[0], first[1]]);
        }

        if (closed.length < 4) {
            return null;
        }

        return closed;
    }

    function extractYmapsRing(ymapsCoords) {
        if (!Array.isArray(ymapsCoords) || ymapsCoords.length === 0) {
            return [];
        }

        const first = ymapsCoords[0];
        if (Array.isArray(first) && Array.isArray(first[0])) {
            return first;
        }

        if (Array.isArray(first) && typeof first[0] === "number") {
            return ymapsCoords;
        }

        return [];
    }

    function ymapsGeometryToGeoJson(type, ymapsCoords) {
        const ring = extractYmapsRing(ymapsCoords)
            .map((pos) => ymapsPositionToGeoJson(pos))
            .filter((p) => p !== null);

        const closed = closeGeoJsonRing(ring);
        if (!closed) {
            return null;
        }

        if (type === "MultiPolygon") {
            return { type: "Polygon", coordinates: [closed] };
        }

        if (type === "Polygon") {
            return { type: "Polygon", coordinates: [closed] };
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

    global.GangstersMapsCoords = {
        TOMSK_CENTER,
        geoJsonPositionToYmaps,
        ymapsPositionToGeoJson,
        geometryToYmapsPolygonCoords,
        ymapsGeometryToGeoJson,
        pairToYmapsCenter,
        isValidYmapsCenter,
    };
})(typeof window !== "undefined" ? window : global);
