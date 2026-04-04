// Get car data from URL
document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    let vehicleId = urlParams.get('vehicle_id');
    const carSlug = urlParams.get('car');

    const pickupDate = localStorage.getItem('pickupDate') || '19 Jan 2026 at 10:30 AM';
    const returnDate = localStorage.getItem('returnDate') || '22 Jan 2026 at 05:00 PM';
    const customerName = localStorage.getItem('customerName') || 'Binayak Ghising';
    const carLocation = localStorage.getItem('carLocation') || 'Dharan, Sunsari';
    const calcTotalPrice = localStorage.getItem('totalPrice'); // Get dynamic price!

    // Fallback logic
    if (!vehicleId && carSlug) {
        const slugMap = { 'tesla-model-s': 12, 'ferrari-laferrari': 13, 'lamborghini': 1, 'bmw-gts3-m2': 13, 'ferrari-ff': 12, 'kala-gadi': 1, 'nilo-gadi': 2, 'hariyo-gadi': 3 };
        vehicleId = slugMap[carSlug] || 12;
    }
    if (!vehicleId) vehicleId = 12;

    let baseDailyRate = 1000;

    // Fetch dynamic car details
    try {
        const res = await fetch(`../api/vehicles/read_single.php?id=${vehicleId}`);
        if(res.ok) {
            const data = await res.json();
            document.getElementById('confirm-car-name').textContent = data.name.toUpperCase();
            document.getElementById('confirm-car-image').src = data.image_url;
            baseDailyRate = parseFloat(data.daily_rate);
        }
    } catch(e) { console.error('API Error:', e); }

    document.getElementById('booking-name').textContent = customerName;
    document.getElementById('booking-pickup').textContent = pickupDate;
    document.getElementById('booking-return').textContent = returnDate;
    document.getElementById('booking-location').textContent = '@' + carLocation;

    // Generate random booking ID and Trx ID
    const bookingId = Math.floor(Math.random() * 90000) + 10000;
    const trxId = '#' + Math.random().toString(36).substring(2, 15);

    document.getElementById('booking-id').textContent = bookingId;
    document.getElementById('trx-id').textContent = trxId;

    // Calculate final payment amounts (favor dynamic calculation first)
    const amount = calcTotalPrice ? parseFloat(calcTotalPrice) : baseDailyRate;
    const serviceFee = Math.round(amount * 0.1); // 10% service fee
    const totalAmount = amount + serviceFee;

    document.getElementById('payment-amount').textContent = 'Rs. ' + amount.toLocaleString();
    document.getElementById('service-fee').textContent = 'Rs. ' + serviceFee.toLocaleString();
    document.getElementById('total-amount').textContent = 'Rs. ' + totalAmount.toLocaleString();


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
});