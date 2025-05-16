document.addEventListener('DOMContentLoaded', function () {
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    const searchText = document.getElementById('searchText');
    const availabilityButton = document.getElementById('availabilityButton');

    // Show price initially
    priceValue.textContent = `0 - ${priceRange.value} BHD`;

    priceRange.addEventListener('input', () => {
        priceValue.textContent = `0 - ${priceRange.value} BHD`;
        filterChargePoints();
    });

    searchText.addEventListener('input', filterChargePoints);

    availabilityButton.addEventListener('click', () => {
        toggleAvailability();
        filterChargePoints();
    });
});

let isAvailableOnly = false;

function setLocationFilter(km) {
    document.getElementById('locationFilter').value = km;
    filterChargePoints();
}


function toggleAvailability() {
    isAvailableOnly = !isAvailableOnly;

    const availabilityInput = document.getElementById('availabilityInput');
    const availabilityButton = document.getElementById('availabilityButton');

    if (isAvailableOnly) {
        availabilityInput.value = 'Available'; // must match your DB value
        availabilityButton.classList.add('active');
        availabilityButton.textContent = 'Showing: Available Only';
    } else {
        availabilityInput.value = '';
        availabilityButton.classList.remove('active');
        availabilityButton.textContent = 'Available Only';
    }

    filterChargePoints(); // Trigger the filter update
}


function filterChargePoints() {
    const search = document.getElementById('searchText').value;
    const maxPrice = document.getElementById('priceRange').value;
    const availability = document.getElementById('availabilityInput').value;

    const params = new URLSearchParams({
        ajax: 'filter',
        search: search,
        maxPrice: maxPrice,
        availability: availability,
    });

    fetch(`Booking.php?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {
        const container = document.querySelector('.row-cols-xl-4');
        container.innerHTML = html;
    })
    .catch(error => console.error('Filter error:', error));
}
