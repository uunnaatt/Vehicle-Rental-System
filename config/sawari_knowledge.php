<?php

function load_sawari_knowledge() {
    return [
        [
            'id' => 'company-overview',
            'title' => 'Company Overview',
            'tags' => ['about', 'company', 'sawari', 'overview', 'platform', 'what is sawari'],
            'content' => 'SAWARI is a Nepal-based vehicle rental company and web platform. It helps customers browse rental vehicles, compare categories, book trips, upload verification collateral, pay, track active rentals, save favorites, review vehicles, and contact support.'
        ],
        [
            'id' => 'who-should-use-sawari',
            'title' => 'Who SAWARI Helps',
            'tags' => ['who is it for', 'customers', 'travelers', 'family trip', 'business travel', 'tourists'],
            'content' => 'SAWARI is suitable for customers who need rental vehicles for city travel, airport pickups, family trips, business travel, luggage-heavy journeys, and route-based travel across major cities and hubs in Nepal.'
        ],
        [
            'id' => 'booking-flow',
            'title' => 'Booking Flow',
            'tags' => ['booking', 'reserve', 'how to book', 'rental process', 'payment', 'steps'],
            'content' => 'To book a vehicle, the user opens a vehicle detail page, taps Book now, enters full name, email address, and 10-digit contact number, selects a pickup location, chooses pickup and return dates, uploads collateral, accepts the rental agreement, and continues to payment.'
        ],
        [
            'id' => 'booking-required-fields',
            'title' => 'Booking Requirements',
            'tags' => ['required', 'required fields', 'what do i need', 'need to book', 'documents needed'],
            'content' => 'The booking form requires full name, email address, 10-digit contact number, pickup and return dates, pickup location, collateral type, collateral image upload, and acceptance of the user agreement before the booking can proceed.'
        ],
        [
            'id' => 'booking-date-rules',
            'title' => 'Booking Date Rules',
            'tags' => ['date rules', 'pickup date', 'return date', 'same day', 'date validation'],
            'content' => 'SAWARI requires a pickup date and a return date. The booking form validates that the return date must be after the pickup date before continuing.'
        ],
        [
            'id' => 'contact-number-rule',
            'title' => 'Contact Number Requirement',
            'tags' => ['phone number', 'contact number', '10 digit', 'mobile number'],
            'content' => 'The current SAWARI booking flow expects a 10-digit contact number. Invalid contact numbers are rejected when the booking is submitted.'
        ],
        [
            'id' => 'collateral-policy',
            'title' => 'Collateral Policy',
            'tags' => ['collateral', 'id', 'passport', 'citizenship', 'driving license', 'verification', 'security document'],
            'content' => 'SAWARI requires one collateral document and image upload during booking. Supported collateral types are Citizenship Card, Passport, and Driving License. The uploaded ID is used for verification and collateral review.'
        ],
        [
            'id' => 'collateral-rejection',
            'title' => 'Collateral Review Outcome',
            'tags' => ['rejected booking', 'verification failed', 'collateral failed', 'unverified'],
            'content' => 'SAWARI may reject a booking if the submitted details or collateral cannot be verified. The assistant should describe this as a verification step rather than guaranteed approval.'
        ],
        [
            'id' => 'rental-agreement',
            'title' => 'Rental Agreement Rules',
            'tags' => ['agreement', 'policy', 'late return', 'damage', 'fines', 'rules', 'responsibility'],
            'content' => 'The renter must return the vehicle on time and in the same condition as received. Traffic fines, misuse, late return charges, and damage charges remain the renter responsibility. SAWARI may reject a booking if details or collateral cannot be verified.'
        ],
        [
            'id' => 'payment-step',
            'title' => 'Payment Flow',
            'tags' => ['payment', 'pay now', 'checkout', 'confirm payment', 'payment page'],
            'content' => 'After booking details are completed, SAWARI sends the customer to the payment confirmation flow. The current interface uses a Pay Now action after vehicle details, dates, and collateral have been provided.'
        ],
        [
            'id' => 'tracking-feature',
            'title' => 'Tracking Feature',
            'tags' => ['tracking', 'gps', 'track my rental', 'live location', 'active booking', 'map'],
            'content' => 'Logged-in users can open the Track My Rental page to view live location details for their current pending or confirmed rental whose return date has not passed.'
        ],
        [
            'id' => 'tracking-eligibility',
            'title' => 'Who Can Track a Rental',
            'tags' => ['who can track', 'tracking eligibility', 'need login', 'active rental only'],
            'content' => 'Rental tracking is meant for logged-in users who have a current pending or confirmed booking. If there is no active eligible booking, the system cannot show live rental tracking.'
        ],
        [
            'id' => 'support-info',
            'title' => 'Support Information',
            'tags' => ['support', 'contact', 'help', 'email', 'phone', 'location', 'customer support'],
            'content' => 'SAWARI support information shown in the system is: Kathmandu, Nepal, email support@sawari.com, and phone +977-XXXXXXXXXX. Users can also message support from the profile page support tab.'
        ],
        [
            'id' => 'support-chat',
            'title' => 'Support Chat',
            'tags' => ['chat with support', 'support tab', 'contact support', 'message support'],
            'content' => 'The profile page contains a support chat tab where signed-in users can start a conversation with SAWARI support directly inside the system.'
        ],
        [
            'id' => 'favorites-feature',
            'title' => 'Favorites Feature',
            'tags' => ['favorites', 'save vehicle', 'wishlist', 'heart button', 'favorite vehicles'],
            'content' => 'Signed-in users can save vehicles to favorites using the heart button. Favorite vehicles are shown on the profile page and can also appear in the dashboard favorites section.'
        ],
        [
            'id' => 'reviews-feature',
            'title' => 'Reviews Feature',
            'tags' => ['reviews', 'ratings', 'leave review', 'review vehicle', 'customer feedback'],
            'content' => 'SAWARI supports vehicle reviews and star ratings. Customers can read reviews on the vehicle detail page and submit their own review with a rating and comment.'
        ],
        [
            'id' => 'dashboard-features',
            'title' => 'Dashboard Features',
            'tags' => ['dashboard', 'browse', 'search', 'ai advisor', 'favorites section', 'vehicle listing'],
            'content' => 'The customer dashboard includes search, favorites, multiple vehicle sections, trip-based AI recommendations, and a quick link to active rental tracking.'
        ],
        [
            'id' => 'ai-advisor-feature',
            'title' => 'Trip Recommendation Feature',
            'tags' => ['ai advisor', 'recommend vehicle', 'trip recommendation', 'which car should i choose'],
            'content' => 'SAWARI includes a trip-based AI advisor on the dashboard. It uses trip type, route, travelers, days, budget per day, terrain, and notes to recommend suitable vehicles from the current inventory.'
        ],
        [
            'id' => 'fleet-categories',
            'title' => 'Vehicle Categories',
            'tags' => ['categories', 'vehicle types', 'suv', 'sedan', 'hatchback', 'pickup'],
            'content' => 'SAWARI organizes vehicles into four main categories: SUV, Sedan, Hatchback, and Pickup. These categories help customers choose vehicles based on trip style, space needs, and terrain.'
        ],
        [
            'id' => 'suv-guidance',
            'title' => 'When SUVs Fit Best',
            'tags' => ['when should i choose suv', 'best suv', 'mountain roads', 'family suv', 'rough roads'],
            'content' => 'SUVs are usually the better fit for mountain roads, family travel, longer routes, and situations where customers want more space, stronger road presence, and better comfort on mixed terrain.'
        ],
        [
            'id' => 'sedan-guidance',
            'title' => 'When Sedans Fit Best',
            'tags' => ['when should i choose sedan', 'business travel', 'city sedan', 'comfortable car'],
            'content' => 'Sedans are a strong fit for city travel, business trips, comfortable paved-road travel, and customers who want a balanced vehicle with passenger comfort and cleaner road handling.'
        ],
        [
            'id' => 'hatchback-guidance',
            'title' => 'When Hatchbacks Fit Best',
            'tags' => ['when should i choose hatchback', 'budget car', 'city commute', 'compact vehicle'],
            'content' => 'Hatchbacks are usually the most practical choice for budget-conscious travel, daily city use, easier parking, and customers who want a compact and efficient rental vehicle.'
        ],
        [
            'id' => 'pickup-guidance',
            'title' => 'When Pickups Fit Best',
            'tags' => ['when should i choose pickup', 'cargo', 'luggage', 'equipment', 'rugged travel'],
            'content' => 'Pickup vehicles are best suited for customers carrying equipment or larger loads, or for trips where cargo space, rugged capability, and utility matter more than compact city driving.'
        ],
        [
            'id' => 'vehicle-details-page',
            'title' => 'Vehicle Details Experience',
            'tags' => ['vehicle detail page', 'car details', 'vehicle description', 'specs', 'book now'],
            'content' => 'Each vehicle detail page shows the vehicle name, description, daily price, specifications, reviews, and a Book now action so the customer can move directly into the booking flow.'
        ],
        [
            'id' => 'profile-features',
            'title' => 'Profile Features',
            'tags' => ['profile', 'my bookings', 'account', 'my reviews', 'my favorites'],
            'content' => 'The SAWARI profile area lets signed-in users view their bookings, favorites, reviews, and support chat in one place.'
        ],
        [
            'id' => 'guest-vs-user',
            'title' => 'Guest and Logged-in Access',
            'tags' => ['guest', 'login required', 'signed in', 'without login', 'authentication'],
            'content' => 'Guests can browse the fleet and public pages, but personalized activity such as booking submission, favorites, profile history, and rental tracking requires login.'
        ],
        [
            'id' => 'locations-general',
            'title' => 'Location Coverage',
            'tags' => ['locations', 'cities', 'pickup', 'dropoff', 'branches', 'coverage', 'where does sawari operate'],
            'content' => 'SAWARI lists rental hubs across major Nepal locations. Customers can browse vehicles by location and choose pickup points during booking.'
        ],
        [
            'id' => 'availability-pricing-general',
            'title' => 'Availability and Pricing Behavior',
            'tags' => ['availability', 'price', 'daily price', 'rental cost', 'how pricing works'],
            'content' => 'Vehicle availability and price depend on the current inventory shown in the system. SAWARI presents daily rental pricing on vehicle cards and detail pages, and total price changes based on the selected booking dates.'
        ],
        [
            'id' => 'admin-capabilities',
            'title' => 'Admin Dashboard Capabilities',
            'tags' => ['admin', 'admin dashboard', 'manage vehicles', 'manage bookings', 'stats'],
            'content' => 'The SAWARI admin side includes dashboard statistics, booking management, vehicle management, review visibility, support chat handling, and status updates for vehicles and bookings.'
        ],
        [
            'id' => 'booking-statuses',
            'title' => 'Booking Status Meanings',
            'tags' => ['booking status', 'pending', 'confirmed', 'completed', 'cancelled', 'status meaning'],
            'content' => 'SAWARI bookings can have statuses such as pending, confirmed, cancelled, and completed. Pending usually means the booking exists but is not fully finalized, while confirmed and completed indicate later stages of the rental lifecycle.'
        ],
        [
            'id' => 'support-boundary',
            'title' => 'Assistant Scope Boundary',
            'tags' => ['scope', 'out of scope', 'unrelated', 'what can you answer'],
            'content' => 'The SAWARI assistant is meant to answer questions about SAWARI, vehicle rental flows, company information shown in the system, bookings, pricing, support, tracking, categories, and account activity. It should refuse unrelated general questions.'
        ]
    ];
}
