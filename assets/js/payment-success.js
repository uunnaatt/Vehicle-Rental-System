// Get data from localStorage
const bookingId = localStorage.getItem('bookingId') || '00451';
const trxId = localStorage.getItem('trxId') || '#141mtslv5854d58';
const totalAmount = localStorage.getItem('totalAmount') || '1000';
const carSlug = new URLSearchParams(window.location.search).get('car') || 'tesla-model-s';

document.getElementById('summary-booking-id').textContent = bookingId;
document.getElementById('summary-trx').textContent = trxId;
document.getElementById('summary-amount').textContent = 'Rs. ' + totalAmount;
document.getElementById('summary-car').textContent = carSlug.replace(/-/g, ' ').toUpperCase();

// Clear localStorage after showing
// localStorage.clear();