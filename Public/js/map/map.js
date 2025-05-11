const map = L.map('map').setView([53.483710, -2.270110], 13);
const availableIcon = L.icon({
    iconUrl: 'lib/leaflet/images/marker-icon.png',
    shadowUrl: 'lib/leaflet/images/marker-icon.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});
const unavailableIcon = L.icon({
    iconUrl: 'lib/leaflet/images/marker-icon-red.png',
    shadowUrl: 'lib/leaflet/images/marker-icon-red.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});
const userIcon = L.icon({
    iconUrl: 'lib/leaflet/images/marker-icon-green.png',
    shadowUrl: 'lib/leaflet/images/marker-icon-green.png',
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
    
    const popupContent = `
        <b>Address:</b> ${cp.address}<br>
        <b>Price:</b> ${parseFloat(cp.price).toFixed(3)} BHD/kWh<br>
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

document.addEventListener('DOMContentLoaded', function() {
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    
    priceValue.textContent = `0.000 - ${(priceRange.value / 1000).toFixed(3)} BHD`;
    
    priceRange.addEventListener('input', function() {
        priceValue.textContent = `0.000 - ${(this.value / 1000).toFixed(3)} BHD`;
        updateMarkers();
    });
        document.getElementById('searchInput').addEventListener('input', updateMarkers);
    document.getElementById('availabilitySelect').addEventListener('change', updateMarkers);
});

function updateMarkers() {
    const searchTerm = document.getElementById('searchInput').value;
    const availability = document.getElementById('availabilitySelect').value;
    const maxPrice = (document.getElementById('priceRange').value / 1000).toFixed(3); 

    const params = new URLSearchParams({
        search: searchTerm,
        availability: availability,
        maxPrice: maxPrice
    });

    fetch(`Models/chargepointsAjax.php?${params}`)
        .then(response => {
            if (!response.ok) throw new Error('network error');
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
        .catch(error => console.error('Error:', error));
}

updateMarkers();
setInterval(updateMarkers, 2000); 