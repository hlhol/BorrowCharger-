
let pricePerHour = 0;
let priceDisplay =0;
let priceInput = 0;
let durationHours = 0;

function openBookingModal(chargePointId,user_Id) {
    console.log("Opening booking modal for chargePointId:", chargePointId);
    console.log("Opening booking modal for user_Id:", user_Id);
    var bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
    bookingModal.show();
    
     // Setup the time dropdowns
    setupTimeDropdowns();

    pricePerHour = chargePointPrices[chargePointId];
    console.log("Price for this point:", pricePerHour);
    
    
      // Display the price per hour 
    document.getElementById('totalPriceDisplay').textContent = 
        `${priceDisplay.toFixed(2)} BHD/hour`;
    console.log("Price for dispaly:", priceDisplay);
    
    // Set the id
    document.getElementById('point_id').value = chargePointId;
    document.getElementById('user_id').value = user_Id;
    
    
    
   }

const bookedData = {
  "2025-05-08": ["08:00", "08:30", "10:00"],
  "2025-05-09": ["10:00", "10:30"]
};


// In JavaScript (optional refresh)
async function refreshPrice(chargePointId) {
    const response = await fetch(`/api/chargepoint/${chargePointId}/price`);
    const data = await response.json();
    pricePerHour = data.price;
    calculatePrice(); // Update displayed total
}

const generateTimes = () => {
  const times = [];
  for (let h = 0; h < 24; h++) {
    for (let m = 0; m < 60; m += 30) {
      let hour = h.toString().padStart(2, '0');
      let minute = m.toString().padStart(2, '0');
      times.push(`${hour}:${minute}`);
    }
  }
  console.log(times);  // Debugging line
  return times;
};


const populateTimeDropdown = (dropdown, availableTimes) => {
  dropdown.innerHTML = `<option value="">-- Select --</option>`;
  availableTimes.forEach(time => {
    const option = document.createElement("option");
    option.value = time;
    option.textContent = time;
    dropdown.appendChild(option);
  });
  console.log("Populated times:", availableTimes);  // Log populated times
};



function setupPriceCalculation() {
    startTime = document.getElementById('startTime');
    endTime = document.getElementById('endTime');
    priceDisplay = document.getElementById('totalPriceDisplay');
    priceInput = document.getElementById('totalPrice');
    
   function calculatePrice() {
    if (startTime.value && endTime.value && bookingDate.value) {
        const start = new Date(`${bookingDate.value}T${startTime.value}`);
        const end = new Date(`${bookingDate.value}T${endTime.value}`);
        
        // Handle overnight bookings
        if (end < start) end.setDate(end.getDate() + 1);
        
         durationHours = (end - start) / (1000 * 60 * 60);
        const totalPrice = durationHours * pricePerHour;
        
        console.log("duration:", durationHours);
        priceDisplay.textContent = totalPrice.toFixed(2);
        priceInput.value = totalPrice.toFixed(2);
        
        console.log("Price for this point:", pricePerHour);
        
        console.log("Price for dispaly:", priceDisplay);
        
        console.log("Price for dispaly:", totalPrice);
        
    }
}
    
    startTime.addEventListener('change', calculatePrice);
    endTime.addEventListener('change', calculatePrice);
}


function setupTimeDropdowns() {
    const dateInput = document.getElementById("bookingDate");
    const startTime = document.getElementById("startTime");
    const endTime = document.getElementById("endTime");


// Set minimum date to today (in case HTML attribute wasn't set)
    dateInput.min = new Date().toISOString().split('T')[0];

    dateInput.addEventListener("change", () => {
        const selectedDate = new Date(dateInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Compare dates only (ignore time)
        
        if (selectedDate < today) {
            alert("Please select a current or future date");
            dateInput.value = "";
            return;
        }
    console.log("Setting up time dropdowns");
    if (!dateInput || !startTime || !endTime) {
        console.warn
        ("Dropdown elements not found!");
        return;
    }

    const allTimes = generateTimes();
    console.log("Generated times:", allTimes);

    dateInput.addEventListener("change", () => {
        const selectedDate = dateInput.value;
        console.log("Selected Date:", selectedDate);
        const booked = bookedData[selectedDate] || [];
        const available = allTimes.filter(t => !booked.includes(t));

        // Fill dropdowns
        populateTimeDropdown(startTime, available);
        populateTimeDropdown(endTime, available);
    });

    startTime.addEventListener("change", () => {
        const selectedStart = startTime.value;
        console.log("Selected Start Time:", selectedStart);
        const options = Array.from(endTime.options);

        // Only show end times after selected start
        options.forEach(option => {
            if (option.value && option.value <= selectedStart) {
                option.disabled = true;
            } else {
                option.disabled = false;
            }
        });

        // Reset end time if it's now invalid
        if (endTime.value && endTime.value <= selectedStart) {
            endTime.value = "";
        }
        
        setupPriceCalculation()
    });
});


    // New event listener for endTime
    endTime.addEventListener("change", () => {
        const selectedEnd = endTime.value;
        const selectedStart = startTime.value;
        console.log("Selected End Time:", selectedEnd);

        if (selectedStart && selectedEnd && selectedEnd <= selectedStart) {
            alert("End time must be after start time");
            endTime.value = "";
        }
    });
}

// Form submission handler
document.getElementById('bookingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Get form data
    const userId = document.getElementById('user_id').value;
    const pointId = document.getElementById('point_id').value;
    const bookingDate = document.getElementById('bookingDate').value;
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    const totalPrice = document.getElementById('totalPrice').value;
    
    
    try {
        const response = await fetch('createBooking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                userId:user_id,
                    pointId: pointId,
                    startDateTime: `${bookingDate} ${startTime}:00`,
                    endDateTime: `${bookingDate} ${endTime}:00`,
                    durationHours: durationHours,
                    totalPrice: totalPrice
                })
            });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Booking created successfully!');
            // Close modal and refresh if needed
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while creating booking');
    }
});
