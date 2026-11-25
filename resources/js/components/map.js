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

function actionLink(action) {
    const link = document.createElement('a');
    link.href = action.url;
    link.textContent = action.label;
    link.className = 'gs-map-action';

    return link;
}

function popupContent(marker) {
    const container = document.createElement('div');
    const title = document.createElement(marker.url ? 'a' : 'span');
    title.textContent = marker.label;
    title.className = 'gs-map-title';

    if (marker.url) {
        title.href = marker.url;
    }

    container.append(title);
    (marker.actions ?? []).forEach((action) => container.append(actionLink(action)));

    return container;
}

function addMarkers(map, markers) {
    return markers.map((marker) => {
        const layer = marker.square
            ? L.marker([marker.latitude, marker.longitude], {
                icon: L.divIcon({ className: 'gs-map-square', html: `<span style="background:${marker.color}"></span>`, iconSize: [14, 14] }),
            })
            : L.circleMarker([marker.latitude, marker.longitude], {
                radius: 6,
                color: '#fff',
                weight: 1,
                fillColor: marker.color ?? '#01a9ac',
                fillOpacity: 0.9,
            });

        return layer.bindPopup(popupContent(marker)).addTo(map);
    });
}

function enableCreation(map, create) {
    map.on('click', (event) => {
        const url = new URL(create.url, window.location.href);
        url.searchParams.set('latitude', event.latlng.lat.toFixed(6));
        url.searchParams.set('longitude', event.latlng.lng.toFixed(6));

        const container = document.createElement('div');
        const coordinates = document.createElement('small');
        coordinates.className = 'gs-map-title';
        coordinates.textContent = `${event.latlng.lat.toFixed(4)}, ${event.latlng.lng.toFixed(4)}`;
        container.append(coordinates, actionLink({ label: create.label, url: url.toString() }));

        L.popup().setLatLng(event.latlng).setContent(container).openOn(map);
    });
}

function renderMap(element) {
    const { markers, zones, create } = JSON.parse(element.dataset.map);
    const map = L.map(element).setView(COAST_OF_IVORY_COAST, DEFAULT_ZOOM);

    L.tileLayer(OPEN_STREET_MAP_TILES, { attribution: OPEN_STREET_MAP_ATTRIBUTION, maxZoom: 18 }).addTo(map);
    addZones(map, zones);

    const markerLayers = addMarkers(map, markers);

    if (markerLayers.length) {
        map.fitBounds(L.featureGroup(markerLayers).getBounds(), { padding: [30, 30], maxZoom: 10 });
    }

    if (create) {
        enableCreation(map, create);
    }
}

export function initMaps(elements) {
    elements.forEach(renderMap);
}
