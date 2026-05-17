// Get car data from URL
document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    let vehicleId = urlParams.get('vehicle_id');
    const carSlug = urlParams.get('car');

    const pickupDate = localStorage.getItem('pickupDate') || '19 Jan 2026 at 10:30 AM';
    const returnDate = localStorage.getItem('returnDate') || '22 Jan 2026 at 05:00 PM';
    const customerName = localStorage.getItem('customerName') || 'Guest User';
    const carLocation = localStorage.getItem('carLocation') || 'Dharan, Sunsari';
    const storedVehicleName = localStorage.getItem('vehicleName');
    const realBookingId = localStorage.getItem('booking_id');

    // Fallback logic
    if (!vehicleId && carSlug) {
        const slugMap = {
            'tesla-model-s': 12,
            'ferrari-laferrari': 13,
            'lamborghini': 1,
            'bmw-gts3-m2': 13,
            'ferrari-ff': 12,
            'kala-gadi': 1,
            'nilo-gadi': 2,
            'hariyo-gadi': 3
        };
        vehicleId = slugMap[carSlug] || 12;
    }
    if (!vehicleId) vehicleId = 12;

    let baseDailyRate = 1000;

    // Fetch dynamic car details
    try {
        const res = await fetch(`../api/vehicles/read_single.php?id=${vehicleId}`);
        if (res.ok) {
            const data = await res.json();
            document.getElementById('confirm-car-name').textContent = data.name.toUpperCase();
            document.getElementById('confirm-car-image').src = data.image_url;
            baseDailyRate = parseFloat(data.daily_rate);
            localStorage.setItem('vehicleName', data.name);
        }
    } catch (e) {
        console.error('API Error:', e);
    }

    if (storedVehicleName && document.getElementById('confirm-car-name')?.textContent === 'Tesla Model S') {
        document.getElementById('confirm-car-name').textContent = storedVehicleName.toUpperCase();
    }

    document.getElementById('booking-name').textContent = customerName;
    document.getElementById('booking-pickup').textContent = pickupDate;
    document.getElementById('booking-return').textContent = returnDate;
    document.getElementById('booking-location').textContent = '@' + carLocation;

    // Use real booking ID from server; fallback to random
    const bookingId = realBookingId ? '#' + realBookingId : '#' + (Math.floor(Math.random() * 90000) + 10000);
    const trxId = '#' + Math.random().toString(36).substring(2, 15);

    document.getElementById('booking-id').textContent = bookingId;
    document.getElementById('trx-id').textContent = trxId;

    // Correctly calculate price from what was stored during booking
    const storedPrice = localStorage.getItem('totalPrice');
    const amount = storedPrice && parseFloat(storedPrice) > 0
        ? parseFloat(storedPrice)
        : baseDailyRate;
    const serviceFee = Math.round(amount * 0.1);
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

    const confirmPaymentBtn = document.getElementById('confirm-payment-btn');
    if (confirmPaymentBtn) {
        confirmPaymentBtn.addEventListener('click', async () => {
            const cardName = document.getElementById('card-name').value.trim();
            const cardEmail = document.getElementById('card-email').value.trim();
            const cardNumber = document.getElementById('card-number').value.trim();
            const cardExpiry = document.getElementById('card-expiry').value.trim();
            const cardCvv = document.getElementById('card-cvv').value.trim();

            if (!cardName || !cardEmail || !cardNumber || !cardExpiry || !cardCvv) {
                alert('Please fill in all card information.');
                return;
            }

            if (cardNumber.replace(/\s/g, '').length !== 16) {
                alert('Please enter a valid 16-digit card number.');
                return;
            }

            if (!/^\d{2}\/\d{2}$/.test(cardExpiry)) {
                alert('Invalid expiry date format. Use MM/YY.');
                return;
            }

            if (cardCvv.length !== 3) {
                alert('Please enter a valid 3-digit CVV.');
                return;
            }

            if (!realBookingId) {
                alert('Booking was not created correctly. Please go back and try again.');
                return;
            }

            confirmPaymentBtn.disabled = true;
            confirmPaymentBtn.textContent = 'Processing...';

            try {
                const response = await fetch('../api/payments/process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        booking_id: parseInt(realBookingId, 10),
                        card_name: cardName,
                        card_number: cardNumber,
                        card_expiry: cardExpiry,
                        card_cvv: cardCvv,
                        amount: amount
                    })
                });

                const result = await response.json();
                if (response.ok && result.success) {
                    localStorage.setItem('bookingId', realBookingId);
                    localStorage.setItem('trxId', result.transaction_id);
                    localStorage.setItem('totalAmount', result.total_amount);
                    localStorage.setItem('bookingCar', document.getElementById('confirm-car-name').textContent);

                    window.location.href = `payment-success.php?vehicle_id=${vehicleId}&trx=${encodeURIComponent(result.transaction_id)}`;
                    return;
                }

                alert(result.message || 'Payment could not be completed.');
            } catch (error) {
                console.error('Payment request failed:', error);
                alert('Unable to complete payment at this time. Please try again.');
            } finally {
                confirmPaymentBtn.disabled = false;
                confirmPaymentBtn.textContent = 'CONFIRM PAYMENT';
            }
        });
    }
});
