// Get car data from URL
const urlParams = new URLSearchParams(window.location.search);
const carSlug = urlParams.get('car') || 'tesla-model-s';
const pickupDate = localStorage.getItem('pickupDate') || '19 Jan 2024 at 10:30 AM';
const returnDate = localStorage.getItem('returnDate') || '22 Jan 2024 at 05:00 PM';
const customerName = localStorage.getItem('customerName') || 'Binayak Ghising';
const carLocation = localStorage.getItem('carLocation') || 'Dharan, Sunsari'; // ✅ GET LOCATION

// Car data
const carsData = {
    'tesla-model-s': { name: 'Tesla Model S', image: '../assets/images/tesla-black.png', price: 1000 },
    'ferrari-laferrari': { name: 'Ferrari LaFerrari', image: '../assets/images/ferrari-red.png', price: 2500 },
    'lamborghini': { name: 'Lamborghini', image: '../assets/images/lamborghini.png', price: 3000 },
    'lamborghini-aventador': { name: 'Lamborghini Aventador', image: '../assets/images/lamborghini.png', price: 3000 },
    'bmw-gts3-m2': { name: 'BMW GTS3 M2', image: '../assets/images/bmw-white.png', price: 2000 },
    'ferrari-ff': { name: 'Ferrari-FF', image: '../assets/images/ferrari-ff.png', price: 2500 },
    'kala-gadi': { name: 'Kalo Gadi', image: '../assets/images/car1.png', price: 1000 },
    'nilo-gadi': { name: 'Nilo Gadi', image: '../assets/images/mazda-blue.png', price: 1000 },
    'hariyo-gadi': { name: 'Hariyo Gaadi', image: '../assets/images/car3.png', price: 1000 }
};

const carData = carsData[carSlug] || carsData['tesla-model-s'];

// Update page with car data
document.getElementById('confirm-car-name').textContent = carData.name;
document.getElementById('confirm-car-image').src = carData.image;
document.getElementById('booking-name').textContent = customerName;
document.getElementById('booking-pickup').textContent = pickupDate;
document.getElementById('booking-return').textContent = returnDate;
document.getElementById('booking-location').textContent = '@' + carLocation; // ✅ DISPLAY LOCATION

// Generate random booking ID and Trx ID
const bookingId = Math.floor(Math.random() * 90000) + 10000;
const trxId = '#' + Math.random().toString(36).substring(2, 15);

document.getElementById('booking-id').textContent = bookingId;
document.getElementById('trx-id').textContent = trxId;

// Calculate payment amounts
const amount = carData.price;
const serviceFee = Math.round(amount * 0.1); // 10% service fee
const totalAmount = amount + serviceFee;

document.getElementById('payment-amount').textContent = 'Rs. ' + amount;
document.getElementById('service-fee').textContent = 'Rs. ' + serviceFee;
document.getElementById('total-amount').textContent = 'Rs. ' + totalAmount;

// Format card number with spaces
const cardNumberInput = document.getElementById('card-number');
if (cardNumberInput) {
    cardNumberInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formattedValue;
    });
}

// Format expiry date
const cardExpiryInput = document.getElementById('card-expiry');
if (cardExpiryInput) {
    cardExpiryInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    });
}

// CVV - only numbers
const cardCvvInput = document.getElementById('card-cvv');
if (cardCvvInput) {
    cardCvvInput.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/\D/g, '');
    });
}

// Confirm Payment Button
const confirmPaymentBtn = document.getElementById('confirm-payment-btn');
if (confirmPaymentBtn) {
    confirmPaymentBtn.addEventListener('click', () => {
        // Validate form
        const cardName = document.getElementById('card-name').value;
        const cardEmail = document.getElementById('card-email').value;
        const cardNumber = document.getElementById('card-number').value;
        const cardExpiry = document.getElementById('card-expiry').value;
        const cardCvv = document.getElementById('card-cvv').value;

        if (!cardName || !cardEmail || !cardNumber || !cardExpiry || !cardCvv) {
            alert('⚠️ Please fill in all card information!');
            return;
        }

        // Validate card number length (16 digits + 3 spaces = 19)
        if (cardNumber.replace(/\s/g, '').length !== 16) {
            alert('⚠️ Please enter a valid 16-digit card number!');
            return;
        }

        // Validate CVV length
        if (cardCvv.length !== 3) {
            alert('⚠️ Please enter a valid 3-digit CVV!');
            return;
        }

        // Save booking data for success page
        localStorage.setItem('bookingId', bookingId);
        localStorage.setItem('trxId', trxId);
        localStorage.setItem('totalAmount', totalAmount);

        // Redirect to success page
        alert('✅ Payment Confirmed! Redirecting...');
        window.location.href = 'payment-success.php?car=' + carSlug;
    });
}