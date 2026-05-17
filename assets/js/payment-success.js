document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const sessionId = urlParams.get('session_id');
    const carSlug = urlParams.get('car') || 'Vehicle';

    document.getElementById('summary-car').textContent = carSlug.replace(/-/g, ' ').toUpperCase();

    if (!sessionId) {
        document.querySelector('.payment-success-title').textContent = 'Payment Error';
        document.querySelector('.payment-success-title').style.color = 'red';
        document.querySelector('.payment-success-message').textContent = 'No session ID found.';
        return;
    }

    document.querySelector('.payment-success-message').textContent = 'Verifying your payment...';

    try {
        const res = await fetch(`../api/payments/verify-session.php?session_id=${sessionId}`);
        const data = await res.json();

        if (res.ok && data.status === 'success') {
            document.querySelector('.payment-success-title').textContent = 'Payment Successful!';
            document.querySelector('.payment-success-message').textContent = 'Your booking has been confirmed and paid.';
            
            document.getElementById('summary-booking-id').textContent = data.booking_id;
            document.getElementById('summary-trx').textContent = data.trx_id;
            document.getElementById('summary-amount').textContent = 'Rs. ' + data.amount;
            
            // Clean up localStorage
            localStorage.removeItem('booking_id');
            localStorage.removeItem('pickupDate');
            localStorage.removeItem('returnDate');
            localStorage.removeItem('totalPrice');
        } else {
            document.querySelector('.payment-success-title').textContent = 'Payment Failed';
            document.querySelector('.payment-success-title').style.color = 'red';
            document.querySelector('.payment-success-message').textContent = data.message || 'Payment could not be verified.';
        }
    } catch (error) {
        console.error('Error verifying payment:', error);
        document.querySelector('.payment-success-title').textContent = 'Error';
        document.querySelector('.payment-success-title').style.color = 'red';
        document.querySelector('.payment-success-message').textContent = 'An error occurred while verifying the payment.';
    }
});