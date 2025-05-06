const map = L.map('map').setView([53.483710, -2.270110], 13);

const availableIcon = L.icon({
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});

const unavailableIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});

const userIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

let markers = L.layerGroup().addTo(map);
let markerObjects = {};
let userLocation = null;
let userMarker = null;


function createOrUpdateMarker(cp) {
    const icon = cp.availability === 'Available' ? availableIcon : unavailableIcon;
    const markerId = String(cp.id);
    
    let popupContent = `
        <b>Address:</b> ${cp.address}<br>
        <b>Price:</b> £${parseFloat(cp.price).toFixed(2)}/kWh<br>
        <b>Status:</b> ${cp.availability}
    `;

    if (markerObjects[markerId]) {
        markerObjects[markerId]
            .setLatLng([cp.latitude, cp.longitude])
            .setIcon(icon)
            .setPopupContent(popupContent);
    } else {
        const newMarker = L.marker([cp.latitude, cp.longitude], { icon })
            .addTo(markers)
            .bindPopup(popupContent);
        markerObjects[markerId] = newMarker;
    }
}

function updateUserPosition(position) {
    userLocation = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
    };

    if (userMarker) {
        userMarker.setLatLng([userLocation.lat, userLocation.lng]);
    } else {
        userMarker = L.marker([userLocation.lat, userLocation.lng], {
            icon: userIcon,
            zIndexOffset: 1000
        }).addTo(map).bindPopup("Your Location");
    }
    
    map.setView([userLocation.lat, userLocation.lng], 13);
    updateMarkers(); 
}
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        updateUserPosition,
        (error) => console.error('Geolocation error:', error),
        { enableHighAccuracy: true }
    );
}

function updateMarkers() {
    fetch('Models/chargepointsAjax.php')
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            if (!Array.isArray(data)) return;

            const newMarkerIds = new Set();

            data.forEach(cp => {
                if (cp?.id && cp?.latitude && cp?.longitude) {
                    const markerId = String(cp.id);
                    newMarkerIds.add(markerId);
                    createOrUpdateMarker(cp);
                }
            });
            Object.keys(markerObjects).forEach(markerId => {
                if (!newMarkerIds.has(markerId)) {
                    markers.removeLayer(markerObjects[markerId]);
                    delete markerObjects[markerId];
                }
            });
        })
        .catch(error => console.error('error:', error));
}

if (window.chargePoints && Array.isArray(window.chargePoints)) {
    window.chargePoints.forEach(cp => {
        if (cp?.id && cp?.latitude && cp?.longitude) {
            createOrUpdateMarker(cp);
        }
    });
}
updateMarkers();
setInterval(updateMarkers, 20000);