// Global variables
let pricePerHour = 0;
let priceDisplay = 0;
let priceInput = 0;
let durationHours = 0;
let currentPointId = 0;

// Time generate
const generateTimes = () => {
  const times = [];
  for (let h = 0; h < 24; h++) {
    for (let m = 0; m < 60; m += 30) {
      let hour = h.toString().padStart(2, '0');
      let minute = m.toString().padStart(2, '0');
      times.push(`${hour}:${minute}`);
    }
  }
  return times;
};

const populateTimeDropdown = (dropdown, availableTimes) => {
  dropdown.innerHTML = '<option value="">-- Select --</option>';
  availableTimes.forEach(time => {
    const option = document.createElement("option");
    option.value = time;
    option.textContent = time;
    dropdown.appendChild(option);
  });
};

// API functions
async function fetchBookedTimes(pointId, date) {
    try {
        // Clear any existing output buffer
        const url = new URL('../../Booking.php', window.location.origin);
        url.searchParams.append('action', 'getBookedTimes');
        url.searchParams.append('point_id', pointId);
        url.searchParams.append('date', date);
        
        console.log('Fetching from:', url.toString());
        
        const response = await fetch(url.toString(), {
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => null);
            throw new Error(errorData?.error || `HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Received data:', data);
        return Array.isArray(data) ? data : [];
    } catch (error) {
        console.error('Failed to fetch booked times:', {
            error: error.message,
            pointId,
            date
        });
        return [];
    }
}

async function refreshPrice(chargePointId) {
    const response = await fetch(`/api/chargepoint/${chargePointId}/price`);
    const data = await response.json();
    pricePerHour = data.price;
    calculatePrice();
}

// Price calculation
function setupPriceCalculation() {
    const startTime = document.getElementById('startTime');
    const endTime = document.getElementById('endTime');
    const bookingDate = document.getElementById('bookingDate');
    priceDisplay = document.getElementById('totalPriceDisplay');
    priceInput = document.getElementById('totalPrice');
    
    function calculatePrice() {
        if (startTime.value && endTime.value && bookingDate.value) {
            const start = new Date(`${bookingDate.value}T${startTime.value}`);
            const end = new Date(`${bookingDate.value}T${endTime.value}`);
            
            if (end < start) end.setDate(end.getDate() + 1);
            
            durationHours = (end - start) / (1000 * 60 * 60);
            const totalPrice = durationHours * pricePerHour;
            
            priceDisplay.textContent = `${totalPrice.toFixed(2)} BHD`;
            priceInput.value = totalPrice.toFixed(2);
        }
    }
    
    startTime.addEventListener('change', calculatePrice);
    endTime.addEventListener('change', calculatePrice);
}

// Time dropdown management
async function setupTimeDropdowns() {
    const dateInput = document.getElementById("bookingDate");
    const startTime = document.getElementById("startTime");
    const endTime = document.getElementById("endTime");
    const allTimes = generateTimes();

    if (!dateInput || !startTime || !endTime) {
        console.warn("Dropdown elements not found!");
        return;
    }

    dateInput.min = new Date().toISOString().split('T')[0];

    dateInput.addEventListener("change", async () => {
        const selectedDate = new Date(dateInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            alert("Please select a current or future date");
            dateInput.value = "";
            return;
        }

        const dateString = dateInput.value;
        
        // Show loading state
        startTime.innerHTML = '<option value="">Loading available times...</option>';
        endTime.innerHTML = '<option value="">Loading available times...</option>';
        
        try {
            // Fetch booked times from API
            const bookedTimes = await fetchBookedTimes(currentPointId, dateString);
            const availableTimes = allTimes.filter(t => !bookedTimes.includes(t));
            
            populateTimeDropdown(startTime, availableTimes);
            populateTimeDropdown(endTime, availableTimes);
            
            // Reset price display
            priceDisplay.textContent = '0.00 BHD';
            priceInput.value = '0.00';
        } catch (error) {
            console.error("Error loading time slots:", error);
            alert("Failed to load available times. Please try again.");
        }
    });

    startTime.addEventListener("change", () => {
        const selectedStart = startTime.value;
        const options = Array.from(endTime.options);

        options.forEach(option => {
            option.disabled = option.value && option.value <= selectedStart;
        });

        if (endTime.value && endTime.value <= selectedStart) {
            endTime.value = "";
        }
        
        setupPriceCalculation();
    });

    endTime.addEventListener("change", () => {
        const selectedEnd = endTime.value;
        const selectedStart = startTime.value;

        if (selectedStart && selectedEnd && selectedEnd <= selectedStart) {
            alert("End time must be after start time");
            endTime.value = "";
        }
    });
}

// Modal control
function openBookingModal(chargePointId, userId) {
    console.log("Opening booking modal for chargePointId:", chargePointId);
    currentPointId = chargePointId;
    
    var bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
    bookingModal.show();
    
    // Reset form
    document.getElementById('bookingDate').value = '';
    document.getElementById('startTime').innerHTML = '<option value="">-- Select --</option>';
    document.getElementById('endTime').innerHTML = '<option value="">-- Select --</option>';
    document.getElementById('totalPriceDisplay').textContent = '0.00 BHD';
    
    // Set price and ID
    pricePerHour = chargePointPrices[chargePointId];
    document.getElementById('point_id').value = chargePointId;
    
    // Initialize dropdowns
    setupTimeDropdowns();
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    setupTimeDropdowns();
    setupPriceCalculation();
});