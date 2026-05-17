const params = new URLSearchParams(window.location.search);
const urlTrx = params.get('trx') || '';
const bookingId = localStorage.getItem('bookingId') || '00451';
const trxId = urlTrx || localStorage.getItem('trxId') || '#141mtslv5854d58';
const totalAmount = localStorage.getItem('totalAmount') || '1000';
const vehicleName = localStorage.getItem('vehicleName') || localStorage.getItem('bookingCar');
const carSlug = params.get('car') || 'tesla-model-s';
const carLabel = vehicleName || carSlug.replace(/-/g, ' ').toUpperCase();

const bookingIdEl = document.getElementById('summary-booking-id');
const trxIdEl = document.getElementById('summary-trx');
const totalAmountEl = document.getElementById('summary-amount');
const carEl = document.getElementById('summary-car');

if (bookingIdEl) bookingIdEl.textContent = '#' + bookingId;
if (trxIdEl) trxIdEl.textContent = trxId;
if (totalAmountEl) totalAmountEl.textContent = 'Rs. ' + Number(totalAmount).toLocaleString();
if (carEl) carEl.textContent = carLabel;

if (urlTrx && !localStorage.getItem('trxId')) {
    localStorage.setItem('trxId', urlTrx);
}
