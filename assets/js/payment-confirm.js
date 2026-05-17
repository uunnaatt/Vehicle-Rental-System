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


// Confirm Payment Button
const confirmPaymentBtn = document.getElementById('confirm-payment-btn');
if (confirmPaymentBtn) {
    confirmPaymentBtn.textContent = 'PAY WITH STRIPE';
    confirmPaymentBtn.addEventListener('click', async () => {
        const storedBookingId = localStorage.getItem('booking_id');
        if (!storedBookingId) {
            alert('⚠️ Booking ID not found. Please try booking again.');
            window.location.href = 'index.php';
            return;
        }

        confirmPaymentBtn.textContent = 'Redirecting to Stripe...';
        confirmPaymentBtn.disabled = true;

        try {
            const res = await fetch('../api/payments/create-checkout-session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    booking_id: storedBookingId,
                    car_slug: carSlug
                })
            });

            const data = await res.json();

            if (res.ok && data.checkout_url) {
                window.location.href = data.checkout_url;
            } else {
                alert('⚠️ Failed to initiate payment: ' + (data.message || 'Unknown error'));
                confirmPaymentBtn.textContent = 'PAY WITH STRIPE';
                confirmPaymentBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('⚠️ An error occurred while contacting the payment server.');
            confirmPaymentBtn.textContent = 'PAY WITH STRIPE';
            confirmPaymentBtn.disabled = false;
        }
    });
}
});