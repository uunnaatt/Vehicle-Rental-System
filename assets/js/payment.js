// Get car data from URL
const urlParams = new URLSearchParams(window.location.search);
const carName = urlParams.get('car') || 'tesla-model-s';
const vehicleName = localStorage.getItem('vehicleName');
const summaryCar = vehicleName || carName.replace(/-/g, ' ').toUpperCase();
const summaryAmount = localStorage.getItem('totalAmount') || localStorage.getItem('totalPrice') || '1000';

// Update summary
const summaryCarEl = document.getElementById('summary-car');
const summaryAmountEl = document.getElementById('summary-amount');
const summaryPickupEl = document.getElementById('summary-pickup');
const summaryReturnEl = document.getElementById('summary-return');

if (summaryCarEl) summaryCarEl.textContent = summaryCar;
if (summaryAmountEl) summaryAmountEl.textContent = 'Rs. ' + Number(summaryAmount).toLocaleString();
if (summaryPickupEl) summaryPickupEl.textContent = localStorage.getItem('pickupDate') || '-';
if (summaryReturnEl) summaryReturnEl.textContent = localStorage.getItem('returnDate') || '-';