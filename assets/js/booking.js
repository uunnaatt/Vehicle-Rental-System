// assets/js/booking.js – enhanced to integrate API and price calculation

document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const vehicleId = urlParams.get('vehicle_id');

    const payBtn = document.getElementById('pay-btn');
    const errorMessage = document.getElementById('error-message');
    const pickupDate = document.getElementById('pickup-date');
    const returnDate = document.getElementById('return-date');
    const priceDisplay = document.querySelector('.pay-amount');

    // UI visual toggles for gender and rental duration buttons
    const genderLabels = document.querySelectorAll('.gender-option');
    genderLabels.forEach(label => {
        label.addEventListener('click', () => {
            genderLabels.forEach(l => l.classList.remove('active'));
            label.classList.add('active');
        });
    });

    const rentalBtns = document.querySelectorAll('.rental-btn');
    rentalBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            rentalBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // Set min date to today
    const today = new Date().toISOString().split('T')[0];
    if (pickupDate) pickupDate.min = today;
    if (returnDate) returnDate.min = today;

    let vehicleData = null;
    let dailyRate = 0;

    // Load vehicle data from API
    if (vehicleId) {
        try {
            const res = await fetch(`../api/vehicles/read_single.php?id=${vehicleId}`);
            if (res.ok) {
                vehicleData = await res.json();
                dailyRate = parseFloat(vehicleData.daily_rate);
                document.getElementById('booking-car-name').textContent = vehicleData.name;
                const img = document.getElementById('booking-car-image');
                if (img) img.src = vehicleData.image_url;
                if (priceDisplay) priceDisplay.textContent = `Rs. ${dailyRate.toLocaleString()}/Day`;
            }
        } catch (e) {
            console.warn('Could not load vehicle details:', e);
        }
    }

    // Load locations into dropdown if exists
    try {
        const locRes = await fetch('../api/locations/read.php');
        if (locRes.ok) {
            const locData = await locRes.json();
            const locationInput = document.getElementById('location');
            if (locationInput && locData.records) {
                // Replace the text input with a select for better UX
                const select = document.createElement('select');
                select.id = 'location-select';
                select.className = 'form-input';
                select.innerHTML = '<option value="">Select Pickup Location</option>';
                locData.records.forEach(loc => {
                    select.innerHTML += `<option value="${loc.id}">${loc.name}</option>`;
                });
                locationInput.replaceWith(select);
            }
        }
    } catch (e) {}

    // Recalculate price when dates change
    function updatePrice() {
        if (!pickupDate?.value || !returnDate?.value || !dailyRate) return;
        const start = new Date(pickupDate.value);
        const end = new Date(returnDate.value);
        const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
        if (days > 0 && priceDisplay) {
            priceDisplay.textContent = `Rs. ${(days * dailyRate).toLocaleString()} (${days}d)`;
        }
    }

    if (pickupDate) pickupDate.addEventListener('change', updatePrice);
    if (returnDate) returnDate.addEventListener('change', updatePrice);

    // Pay button
    if (payBtn) {
        payBtn.addEventListener('click', async (e) => {
            e.preventDefault();

            const fullNameEl = document.getElementById('full-name');
            const emailEl = document.getElementById('email');
            const contactEl = document.getElementById('contact');
            const locationSelect = document.getElementById('location-select');
            
            const colTypeEl = document.getElementById('collateral-type');
            const colImageEl = document.getElementById('collateral-image');
            const agreeEl = document.getElementById('agreement-checkbox');

            const errors = [];
            if (!fullNameEl?.value.trim()) errors.push('Full Name');
            if (!emailEl?.value.trim()) errors.push('Email');
            if (!contactEl?.value.trim()) errors.push('Contact');
            if (!pickupDate?.value) errors.push('Pickup Date');
            if (!returnDate?.value) errors.push('Return Date');
            if (!colTypeEl?.value) errors.push('Collateral Type');
            if (!colImageEl?.files?.length) errors.push('Collateral Image');
            if (!agreeEl?.checked) errors.push('User Agreement');

            if (errors.length > 0) {
                if (errorMessage) {
                    errorMessage.textContent = `⚠️ Please fill: ${errors.join(', ')}`;
                    errorMessage.style.display = 'block';
                    setTimeout(() => errorMessage.style.display = 'none', 3500);
                }
                return;
            }

            // Check date validity
            const start = new Date(pickupDate.value);
            const end = new Date(returnDate.value);
            if (end <= start) {
                if (errorMessage) {
                    errorMessage.textContent = '⚠️ Return date must be after pickup date.';
                    errorMessage.style.display = 'block';
                    setTimeout(() => errorMessage.style.display = 'none', 3000);
                }
                return;
            }

            const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
            const totalPrice = days * dailyRate;
            const userId = localStorage.getItem('user_id') || 1;
            const locationId = locationSelect?.value || 1;

            // If logged in, attempt to create booking via API
            if (vehicleId) {
                try {
                    const formData = new FormData();
                    formData.append('user_id', parseInt(userId));
                    formData.append('vehicle_id', parseInt(vehicleId));
                    formData.append('pickup_location_id', parseInt(locationId));
                    formData.append('dropoff_location_id', parseInt(locationId));
                    formData.append('start_date', pickupDate.value);
                    formData.append('end_date', returnDate.value);
                    formData.append('total_price', totalPrice);
                    
                    formData.append('collateral_type', colTypeEl.value);
                    if (colImageEl.files[0]) {
                        formData.append('collateral_image', colImageEl.files[0]);
                    }
                    formData.append('agreement_accepted', agreeEl.checked ? 'true' : 'false');

                    const bookingRes = await fetch('../api/bookings/create.php', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-Token': typeof csrfToken !== 'undefined' ? csrfToken : ''
                        },
                        body: formData
                    });

                    const bookingData = await bookingRes.json();

                    if (bookingRes.status === 409) {
                        if (errorMessage) {
                            errorMessage.textContent = '⚠️ Vehicle is already booked for those dates!';
                            errorMessage.style.display = 'block';
                        }
                        return;
                    }
                } catch (err) {
                    console.warn('Booking API not connected, continuing to payment page...');
                }
            }

            // Save to localStorage and redirect
            localStorage.setItem('pickupDate', pickupDate.value);
            localStorage.setItem('returnDate', returnDate.value);
            localStorage.setItem('customerName', fullNameEl.value.trim());
            localStorage.setItem('customerEmail', emailEl.value.trim());
            localStorage.setItem('customerContact', contactEl.value.trim());
            localStorage.setItem('totalPrice', totalPrice);
            localStorage.setItem('vehicleName', vehicleData?.name || 'Vehicle');
            localStorage.setItem('vehicleId', vehicleId || '');

            if (errorMessage) {
                errorMessage.textContent = '✅ Processing your booking...';
                errorMessage.style.background = 'rgba(34, 197, 94, 0.2)';
                errorMessage.style.color = '#16a34a';
                errorMessage.style.display = 'block';
            }

            setTimeout(() => {
                window.location.href = `payment-confirm.php?vehicle_id=${vehicleId}`;
            }, 800);
        });
    }
});