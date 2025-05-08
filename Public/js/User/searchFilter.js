document.addEventListener('DOMContentLoaded', () => {
  // Update button text and hidden inputs
  window.setFilterValue = function(type, value) {
    const hiddenInput = document.getElementById(`filter_${type}`);
    const dropdownBtn = document.getElementById(`${type}Dropdown`);
    
    hiddenInput.value = value;
    dropdownBtn.textContent = value;
    console.log(`Filter set: ${type} = ${value}`);
    
    // Auto-apply filters when a selection is made
    filterChargePoints();
  };

  // Handle form submission properly
  const filterForm = document.querySelector('form[onsubmit="filterChargePoints(); return false;"]');
  if (filterForm) {
    filterForm.addEventListener('submit', function(e) {
      e.preventDefault(); // This is the key line to prevent page reload
      filterChargePoints();
    });
  }

  // Enhanced filter function with proper AJAX handling
  window.filterChargePoints = function() {
    const formData = new FormData(document.querySelector('form'));
    const params = new URLSearchParams();
    
    // Add all filter values
    formData.forEach((value, key) => {
      if (value) params.append(key, value);
    });

    console.log("Filtering with:", Object.fromEntries(params));

    // Show loading indicator
    const container = document.querySelector('.row-cols-xl-4');
    if (container) {
      container.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
    }

     fetch(`Booking.php?${params.toString()}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.text();  // Expect HTML response
    })
    .then(data => {
      console.log("Filter result received:", data);
      const container = document.querySelector('.row-cols-xl-4');
      if (container) {
        // Inject the filtered content into the container
        container.innerHTML = data;
      }
    })
    .catch(error => {
      console.error("Error during fetch:", error);
      const container = document.querySelector('.row-cols-xl-4');
      if (container) {
        container.innerHTML = '<div class="col-12 text-center text-danger py-5">Failed to load results.</div>';
      }
    });
  };
  // Add event listener for Enter key in search
  const searchInput = document.getElementById('searchText');
  if (searchInput) {
    searchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        filterChargePoints();
      }
    });
  }
});