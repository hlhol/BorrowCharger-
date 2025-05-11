function createBarChart() {
    const statusData = window.dashboardData.chargerStatusOverview;
    const container = document.getElementById('myBarChart');
    
    container.innerHTML = '';//remove previous content if there

    
    const barWrapper = document.createElement('div');
    barWrapper.style.display = 'flex';
    barWrapper.style.gap = '20px';
    barWrapper.style.height = '300px';
    barWrapper.style.alignItems = 'flex-end';

    //create the bar chars for avaklible and unavlible 
    const availableBar = document.createElement('div');
    availableBar.style.width = '100px';
    availableBar.style.backgroundColor = '#4CAF50';
    availableBar.style.transition = 'height 1s ease';
    availableBar.style.borderRadius = '4px 4px 0 0';
    availableBar.style.position = 'relative';
    
    const unavailableBar = document.createElement('div');
    unavailableBar.style.width = '100px';
    unavailableBar.style.backgroundColor = '#FF5252';
    unavailableBar.style.transition = 'height 1s ease';
    unavailableBar.style.borderRadius = '4px 4px 0 0';
    unavailableBar.style.position = 'relative';

    setTimeout(() => {
        availableBar.style.height = `${statusData.Available}%`;
        unavailableBar.style.height = `${statusData.Unavailable}%`;
    }, 100);

    // add labels
    const availableLabel = document.createElement('div');
    availableLabel.textContent = `${statusData.Available}% Available`;
    availableLabel.style.position = 'absolute';
    availableLabel.style.bottom = '-30px';
    availableLabel.style.width = '100%';
    availableLabel.style.textAlign = 'center';

    const unavailableLabel = document.createElement('div');
    unavailableLabel.textContent = `${statusData.Unavailable}% Unavailable`;
    unavailableLabel.style.position = 'absolute';
    unavailableLabel.style.bottom = '-30px';
    unavailableLabel.style.width = '100%';
    unavailableLabel.style.textAlign = 'center';

    availableBar.appendChild(availableLabel);
    unavailableBar.appendChild(unavailableLabel);
    
    barWrapper.appendChild(availableBar);
    barWrapper.appendChild(unavailableBar);
    container.appendChild(barWrapper);
}

function createTopChargePointsChart() {
    const topChargePointsData = window.dashboardData.usageOverTime;
    const container = document.getElementById('myAreaChart');
    

    container.innerHTML = ''; //remove previous content if there
    
    //  container of charts
    const chartWrapper = document.createElement('div');
    chartWrapper.style.width = '100%';
    chartWrapper.style.height = '300px';
    chartWrapper.style.padding = '20px 0';

    const maxBookings = Math.max(...topChargePointsData.map(d => d.booking_count));

//for all the three chargepont create the bars
    topChargePointsData.forEach((cp, index) => {
        const barContainer = document.createElement('div');
        barContainer.style.marginBottom = '20px';

       
        const addressLabel = document.createElement('div');  // address label
        addressLabel.textContent = `${index + 1}. ${cp.address}`;
        addressLabel.style.marginBottom = '5px';
        addressLabel.style.fontSize = '14px';
        addressLabel.style.color = '#666';

        //  bar background
        const barBackground = document.createElement('div');
        barBackground.style.width = '100%';
        barBackground.style.height = '30px';
        barBackground.style.backgroundColor = '#eee';
        barBackground.style.borderRadius = '4px';
        barBackground.style.position = 'relative';

        const progressBar = document.createElement('div');
        progressBar.style.height = '100%';
        progressBar.style.backgroundColor = '#4CAF50';
        progressBar.style.borderRadius = '4px';
        progressBar.style.width = '0';
        progressBar.style.transition = 'width 1s ease';

        const countLabel = document.createElement('div');
        countLabel.textContent = `${cp.booking_count} Bookings`;
        countLabel.style.position = 'absolute';
        countLabel.style.right = '10px';
        countLabel.style.top = '50%';
        countLabel.style.transform = 'translateY(-50%)';
        countLabel.style.color = '#fff';
        countLabel.style.fontWeight = 'bold';
        countLabel.style.fontSize = '12px';

        // Animate bar width
        setTimeout(() => {
            const widthPercentage = (cp.booking_count / maxBookings) * 100;
            progressBar.style.width = `${widthPercentage}%`;
        }, 100);

        // put components
        barBackground.appendChild(progressBar);
        barBackground.appendChild(countLabel);
        barContainer.appendChild(addressLabel);
        barContainer.appendChild(barBackground);
        chartWrapper.appendChild(barContainer);
    });

    container.appendChild(chartWrapper);
}


//call teh function to create he charts 
document.addEventListener('DOMContentLoaded', () => {
    createBarChart(); 
    createTopChargePointsChart(
});