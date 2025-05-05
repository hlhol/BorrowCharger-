const map = L.map('map').setView([53.483710, -2.270110], 13);
const chargeIcon = L.icon({
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

let markers = L.layerGroup().addTo(map);
if (window.chargePoints && Array.isArray(window.chargePoints)) {
    window.chargePoints.forEach(cp => {
        L.marker([cp.latitude, cp.longitude], {icon: chargeIcon})
            .addTo(markers)
            .bindPopup(`
                <b>Address:</b> ${cp.address}<br>
                <b>Price:</b> £${parseFloat(cp.price).toFixed(2)}/kWh<br>
                <b>Status:</b> ${cp.availability}
            `);
    });
}

if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(pos => {
        map.setView([pos.coords.latitude, pos.coords.longitude], 13);
    });
}

function updateMarkers() {
    fetch('Models/chargepointsAjax.php')
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            if (!Array.isArray(data)) return;
            markers.clearLayers();
            data.forEach(cp => {
                if (cp.latitude && cp.longitude) {
                    L.marker([cp.latitude, cp.longitude], {icon: chargeIcon})
                        .addTo(markers)
                        .bindPopup(`
                            <b>Address:</b> ${cp.address}<br>
                            <b>Price:</b> £${parseFloat(cp.price).toFixed(2)}/kWh<br>
                            <b>Status:</b> ${cp.availability}
                        `);
                }
            });
        })
        .catch(console.error);
}

updateMarkers(); 
setInterval(updateMarkers, 30000);
