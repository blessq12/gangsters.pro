/**
 * Порт App\Shared\Geo\PointInGeoJsonZone + DeliveryZoneMatcher (координаты GeoJSON — [lng, lat]).
 */

function pointInRing(longitude, latitude, ring) {
    const vertexCount = Array.isArray(ring) ? ring.length : 0;
    if (vertexCount < 3) {
        return false;
    }

    let inside = false;
    for (let index = 0, previous = vertexCount - 1; index < vertexCount; previous = index++) {
        const xi = Number(ring[index]?.[0] ?? 0);
        const yi = Number(ring[index]?.[1] ?? 0);
        const xj = Number(ring[previous]?.[0] ?? 0);
        const yj = Number(ring[previous]?.[1] ?? 0);

        const intersects =
            yi > latitude !== yj > latitude &&
            longitude < ((xj - xi) * (latitude - yi)) / (yj - yi || 1e-12) + xi;

        if (intersects) {
            inside = !inside;
        }
    }

    return inside;
}

function pointInPolygonCoordinates(longitude, latitude, polygonCoordinates) {
    if (!Array.isArray(polygonCoordinates) || polygonCoordinates.length === 0) {
        return false;
    }

    if (!pointInRing(longitude, latitude, polygonCoordinates[0] ?? [])) {
        return false;
    }

    for (let index = 1; index < polygonCoordinates.length; index++) {
        if (pointInRing(longitude, latitude, polygonCoordinates[index])) {
            return false;
        }
    }

    return true;
}

/**
 * @returns {boolean|null} null — геометрия не поддерживается
 */
function geoJsonContains(geoJson, latitude, longitude) {
    if (!geoJson || latitude == null || longitude == null) {
        return null;
    }

    if (geoJson.type === "Polygon") {
        return pointInPolygonCoordinates(longitude, latitude, geoJson.coordinates ?? []);
    }

    if (geoJson.type === "MultiPolygon") {
        const polygons = geoJson.coordinates ?? [];
        for (const polygonCoordinates of polygons) {
            if (pointInPolygonCoordinates(longitude, latitude, polygonCoordinates)) {
                return true;
            }
        }
        return polygons.length > 0 ? false : null;
    }

    return null;
}

/**
 * Первая зона, в которую попадает точка (зоны не пересекаются).
 *
 * @param {Array<{geometry: object}>} zones
 * @param {number|null} latitude
 * @param {number|null} longitude
 */
export function matchDeliveryZone(zones, latitude, longitude) {
    if (latitude == null || longitude == null || !Array.isArray(zones) || zones.length === 0) {
        return null;
    }

    return (
        zones.find((zone) => geoJsonContains(zone.geometry, latitude, longitude) === true) ?? null
    );
}
