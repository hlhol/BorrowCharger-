function openBookingModal(chargePointId) {
    console.log("Opening booking modal for chargePointId:", chargePointId);
    var bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
    bookingModal.show();

    // Set the hidden point_id
    document.getElementById('point_id').value = chargePointId;

    // Setup the time dropdowns
    setupTimeDropdowns();
}


const bookedData = {
  "2025-05-08": ["08:00", "08:30", "10:00"],
  "2025-05-09": ["10:00", "10:30"]
};

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

function setupTimeDropdowns() {
    const dateInput = document.getElementById("bookingDate");
    const startTime = document.getElementById("startTime");
    const endTime = document.getElementById("endTime");

    console.log("Setting up time dropdowns");
    if (!dateInput || !startTime || !endTime) {
        console.warn("Dropdown elements not found!");
        return;
    }

    const allTimes = generateTimes();
    console.log("Generated times:", allTimes);

    dateInput.addEventListener("change", () => {
    const selectedDate = dateInput.value;
    console.log("Selected Date:", selectedDate);  // Debugging line
    const booked = bookedData[selectedDate] || [];
    // Filter available times
    const available = allTimes.filter(t => !booked.includes(t));

    // Fill dropdowns
    populateTimeDropdown(startTime, available);
    populateTimeDropdown(endTime, available);
});


    startTime.addEventListener("change", () => {
        const selectedStart = startTime.value;
        const options = Array.from(endTime.options);

        // Only show end times after selected start
        options.forEach(option => {
            if (option.value && option.value <= selectedStart) {
                option.disabled = true;
            } else {
                option.disabled = false;
            }
        });
    });
}



