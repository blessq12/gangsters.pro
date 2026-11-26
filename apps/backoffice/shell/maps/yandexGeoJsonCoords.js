export const TOMSK_ZOOM = 12;

/** Ограничение pan примерно областью Томска. */
export const TOMSK_RESTRICT_AREA = [
    [55.8, 83.8],
    [57.0, 86.2],
];

/**
 * @param {number} a
 * @param {number} b
 */
function isFinitePair(a, b) {
    return Number.isFinite(a) && Number.isFinite(b);
}

/**
 * @param {number} value
 */
function looksLikeLatitude(value) {
    return value >= 41 && value <= 82;
}

/**
 * @param {number} value
 */
function looksLikeLongitude(value) {
    return value >= 19 && value <= 180;
}

/**
 * @param {unknown} coords
 * @returns {coords is [number, number]}
 */
function isValidYmapsCenter(coords) {
    if (!Array.isArray(coords) || coords.length < 2) {
        return false;
    }
    const lat = Number(coords[0]);
    const lng = Number(coords[1]);
    return isFinitePair(lat, lng) && looksLikeLatitude(lat) && looksLikeLongitude(lng);
}

/**
 * GeoJSON [lng, lat] → Yandex [lat, lng].
 * @param {unknown} position
 * @returns {[number, number]|null}
 */
function geoJsonPositionToYmaps(position) {
    if (!Array.isArray(position) || position.length < 2) {
        return null;
    }
    const a = Number(position[0]);
    const b = Number(position[1]);
    if (!isFinitePair(a, b)) {
        return null;
    }
    if (looksLikeLongitude(a) && looksLikeLatitude(b)) {
        const coords = /** @type {[number, number]} */ ([b, a]);
        return isValidYmapsCenter(coords) ? coords : null;
    }
    if (looksLikeLatitude(a) && looksLikeLongitude(b)) {
        const coords = /** @type {[number, number]} */ ([a, b]);
        return isValidYmapsCenter(coords) ? coords : null;
    }
    return null;
}

/**
 * Yandex [lat, lng] → GeoJSON [lng, lat].
 * @param {unknown} position
 * @returns {[number, number]|null}
 */
function ymapsPositionToGeoJson(position) {
    if (!Array.isArray(position) || position.length < 2) {
        return null;
    }
    const lat = Number(position[0]);
    const lng = Number(position[1]);
    if (!isFinitePair(lat, lng) || !looksLikeLatitude(lat) || !looksLikeLongitude(lng)) {
        return null;
    }
    return [lng, lat];
}

/**
 * @param {unknown} geometry
 * @returns {Array<Array<[number, number]>>}
 */
export function geometryToYmapsPolygonCoords(geometry) {
    if (!geometry || typeof geometry !== "object" || !("coordinates" in geometry)) {
        return [];
    }
    /** @type {{ type?: string, coordinates?: unknown }} */
    const geo = geometry;
    let ring = [];
    if (geo.type === "Polygon") {
        ring = Array.isArray(geo.coordinates?.[0]) ? geo.coordinates[0] : [];
    } else if (geo.type === "MultiPolygon") {
        ring = Array.isArray(geo.coordinates?.[0]?.[0]) ? geo.coordinates[0][0] : [];
    }
    const ymapsRing = ring
        .map((pos) => geoJsonPositionToYmaps(pos))
        .filter((coords) => coords !== null);
    return ymapsRing.length >= 3 ? [ymapsRing] : [];
}

/**
 * @param {Array<[number, number]|null>} ring
 * @returns {Array<[number, number]>|null}
 */
function closeGeoJsonRing(ring) {
    if (!ring || ring.length < 3) {
        return null;
    }
    const closed = ring.map((point) => /** @type {[number, number]} */ ([point[0], point[1]]));
    const first = closed[0];
    const last = closed[closed.length - 1];
    if (first[0] !== last[0] || first[1] !== last[1]) {
        closed.push([first[0], first[1]]);
    }
    return closed.length < 4 ? null : closed;
}

/**
 * @param {unknown} ymapsCoords
 * @returns {unknown[]}
 */
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

/**
 * @param {string} type
 * @param {unknown} ymapsCoords
 * @returns {{ type: 'Polygon', coordinates: Array<Array<[number, number]>> }|null}
 */
export function ymapsGeometryToGeoJson(type, ymapsCoords) {
    const ring = extractYmapsRing(ymapsCoords)
        .map((pos) => ymapsPositionToGeoJson(pos))
        .filter((point) => point !== null);
    const closed = closeGeoJsonRing(ring);
    if (!closed) {
        return null;
    }
    if (type === "MultiPolygon" || type === "Polygon") {
        return { type: "Polygon", coordinates: [closed] };
    }
    return null;
}

/**
 * @param {unknown} geometry
 */
export function hasRenderableGeometry(geometry) {
    const coords = geometryToYmapsPolygonCoords(geometry);
    return coords.length > 0 && coords[0].length >= 3;
}
