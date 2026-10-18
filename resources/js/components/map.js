import L from 'leaflet';

const OPEN_STREET_MAP_TILES = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
const OPEN_STREET_MAP_ATTRIBUTION = '&copy; OpenStreetMap contributors';
const COAST_OF_IVORY_COAST = [5.0, -5.5];
const DEFAULT_ZOOM = 7;

function addZones(map, zones) {
    zones.forEach((zone) => {
        L.geoJSON(zone.geometry, {
            style: { color: zone.color ?? '#01a9ac', weight: 1, fillOpacity: 0.08 },
        })
            .bindTooltip(zone.label)
            .addTo(map);
    });
}

function popupContent(marker) {
    const element = document.createElement(marker.url ? 'a' : 'span');
    element.textContent = marker.label;

    if (marker.url) {
        element.href = marker.url;
    }

    return element;
}

function addMarkers(map, markers) {
    return markers.map((marker) =>
        L.circleMarker([marker.latitude, marker.longitude], {
            radius: 6,
            color: '#fff',
            weight: 1,
            fillColor: marker.color ?? '#01a9ac',
            fillOpacity: 0.9,
        })
            .bindPopup(popupContent(marker))
            .addTo(map),
    );
}

function renderMap(element) {
    const { markers, zones } = JSON.parse(element.dataset.map);
    const map = L.map(element).setView(COAST_OF_IVORY_COAST, DEFAULT_ZOOM);

    L.tileLayer(OPEN_STREET_MAP_TILES, { attribution: OPEN_STREET_MAP_ATTRIBUTION, maxZoom: 18 }).addTo(map);
    addZones(map, zones);

    const markerLayers = addMarkers(map, markers);

    if (markerLayers.length) {
        map.fitBounds(L.featureGroup(markerLayers).getBounds(), { padding: [30, 30], maxZoom: 10 });
    }
}

export function initMaps(elements) {
    elements.forEach(renderMap);
}
