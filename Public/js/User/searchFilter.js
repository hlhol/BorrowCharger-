document.addEventListener('DOMContentLoaded', () => {
  // Update button text and hidden inputs
  window.setFilterValue = function(type, value) {
    const hiddenInput = document.getElementById(`filter_${type}`);
    const dropdownBtn = document.getElementById(`${type}Dropdown`);
    
    if (hiddenInput && dropdownBtn) {
      hiddenInput.value = value;
      dropdownBtn.textContent = value;
      console.log(`Filter set: ${type} = ${value}`);
      filterChargePoints();
    }
  };

  const priceRange = document.getElementById('priceRange');
  const priceValue = document.getElementById('priceValue');
  const filterInput = document.getElementById('filter_price_range'); // Add this hidden input to your HTML

  if (priceRange && priceValue && filterInput) {
    // Initialize with default values (0-500) or from URL
    const urlParams = new URLSearchParams(window.location.search);
    const priceParam = urlParams.get('price_range'); // Changed to match hidden input name
    
    if (priceParam) {
      const maxPrice = priceParam.split('-')[1];
      priceRange.value = maxPrice;
      priceValue.textContent = `0-${maxPrice} BHD`;
      filterInput.value = `0-${maxPrice}`;
    } else {
      priceRange.value = 500; // Set slider to max position
      priceValue.textContent = '0-500 BHD';
      filterInput.value = '0-500';
    }
    
    // Update display when slider changes
    priceRange.addEventListener('input', function() {
      const value = this.value;
      priceValue.textContent = `0-${value} BHD`;
    });
    
    // Update filter when slider is released
    priceRange.addEventListener('change', function() {
      const maxPrice = this.value;
      filterInput.value = `0-${maxPrice}`;
      filterChargePoints();
    });
  } else {
    console.error('Missing required elements for price filtering');
  }

  // Handle form submission
  const filterForm = document.querySelector('form[onsubmit="filterChargePoints(); return false;"]');
  if (filterForm) {
    filterForm.addEventListener('submit', function(e) {
      e.preventDefault();
      filterChargePoints();
    });
  }
  
  // Enhanced filter function
  window.filterChargePoints = function() {
    const form = document.querySelector('form');
    if (!form) return;
    
    const formData = new FormData(form);
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
      if (!response.ok) throw new Error('Network response was not ok');
      return response.text();
    })
    .then(data => {
      console.log("Filter result received:", data);
      const container = document.querySelector('.row-cols-xl-4');
      if (container) {
        container.innerHTML = data;
        
        // Show message if no results
        if (data.trim() === '') {
          container.innerHTML = `
            <div class="col-12 text-center py-5">
              No charging stations found within the selected price range.
              <button onclick="resetPriceFilter()" class="btn btn-link">
                Reset price filter
              </button>
            </div>
          `;
        }
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

  // Reset price filter function
  window.resetPriceFilter = function() {
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    const filterInput = document.getElementById('filter_price_range');
    
    if (priceRange && priceValue && filterInput) {
      priceRange.value = 500;
      priceValue.textContent = '0-500 BHD';
      filterInput.value = '0-500';
      filterChargePoints();
    }
  }; // Price range slider functionality
 

  // Enter key in search
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