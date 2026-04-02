// Get car data from URL
const urlParams = new URLSearchParams(window.location.search);
const carName = urlParams.get('car') || 'tesla-model-s';

// Update summary
document.getElementById('summary-car').textContent = carName.replace('-', ' ').toUpperCase();
document.getElementById('summary-amount').textContent = 'Rs. 1000';
document.getElementById('summary-pickup').textContent = localStorage.getItem('pickupDate') || '-';
document.getElementById('summary-return').textContent = localStorage.getItem('returnDate') || '-';