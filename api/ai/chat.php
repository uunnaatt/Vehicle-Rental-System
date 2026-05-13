<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include_once '../../config/database.php';
include_once '../../config/ai_config.php';
include_once '../../config/sawari_knowledge.php';

session_start();

function send_json($status, $payload) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function clean_text($value, $maxLength = 800) {
    $value = trim(preg_replace('/\s+/', ' ', strip_tags((string)$value)));
    if ($value === '') {
        return '';
    }
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength);
    }
    return substr($value, 0, $maxLength);
}

function lower_text($value) {
    return function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
}

function contains_any($text, $needles) {
    foreach ($needles as $needle) {
        if ($needle !== '' && strpos($text, $needle) !== false) {
            return true;
        }
    }
    return false;
}

function tokenize($text) {
    $tokens = preg_split('/[^a-z0-9]+/i', lower_text((string)$text));
    return array_values(array_filter($tokens, function($token) {
        return strlen($token) >= 2;
    }));
}

function unique_tokens($text) {
    return array_values(array_unique(tokenize($text)));
}

function detect_category($message) {
    $map = [
        'SUV' => ['suv', '4x4', 'off road', 'off-road'],
        'Sedan' => ['sedan'],
        'Hatchback' => ['hatchback', 'compact car'],
        'Pickup' => ['pickup', 'truck', 'cargo']
    ];

    foreach ($map as $category => $keywords) {
        if (contains_any($message, $keywords)) {
            return $category;
        }
    }
    return null;
}

function detect_location($message) {
    $map = [
        'Kathmandu – TIA Airport' => ['kathmandu', 'airport', 'tia'],
        'Pokhara – Lakeside' => ['pokhara', 'lakeside'],
        'Chitwan – Sauraha' => ['chitwan', 'sauraha'],
        'Bhaktapur – Durbar Square' => ['bhaktapur', 'durbar square'],
        'Lalitpur – Patan' => ['lalitpur', 'patan'],
        'Biratnagar Hub' => ['biratnagar']
    ];

    foreach ($map as $location => $keywords) {
        if (contains_any($message, $keywords)) {
            return $location;
        }
    }
    return null;
}

function format_rupees($amount) {
    return 'Rs. ' . number_format((float)$amount);
}

function fetch_system_snapshot($db) {
    $snapshot = [
        'vehicle_count' => 0,
        'available_count' => 0,
        'category_counts' => [],
        'location_counts' => [],
        'price_min' => 0,
        'price_max' => 0,
        'popular_vehicles' => [],
        'categories' => [],
        'locations' => []
    ];

    $fleetStmt = $db->prepare(
        "SELECT COUNT(*) AS vehicle_count,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available_count,
                MIN(daily_rate) AS price_min,
                MAX(daily_rate) AS price_max
         FROM vehicles"
    );
    $fleetStmt->execute();
    $fleet = $fleetStmt->fetch(PDO::FETCH_ASSOC);
    if ($fleet) {
        $snapshot['vehicle_count'] = (int)($fleet['vehicle_count'] ?? 0);
        $snapshot['available_count'] = (int)($fleet['available_count'] ?? 0);
        $snapshot['price_min'] = (float)($fleet['price_min'] ?? 0);
        $snapshot['price_max'] = (float)($fleet['price_max'] ?? 0);
    }

    $categoryStmt = $db->prepare(
        "SELECT c.name, COUNT(v.id) AS total,
                SUM(CASE WHEN v.status = 'available' THEN 1 ELSE 0 END) AS available_total
         FROM categories c
         LEFT JOIN vehicles v ON v.category_id = c.id
         GROUP BY c.id, c.name
         ORDER BY c.name ASC"
    );
    $categoryStmt->execute();
    $snapshot['category_counts'] = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
    $snapshot['categories'] = array_map(function($row) {
        return $row['name'];
    }, $snapshot['category_counts']);

    $locationStmt = $db->prepare(
        "SELECT l.name, COUNT(v.id) AS total,
                SUM(CASE WHEN v.status = 'available' THEN 1 ELSE 0 END) AS available_total
         FROM locations l
         LEFT JOIN vehicles v ON v.location_id = l.id
         GROUP BY l.id, l.name
         ORDER BY l.name ASC"
    );
    $locationStmt->execute();
    $snapshot['location_counts'] = $locationStmt->fetchAll(PDO::FETCH_ASSOC);
    $snapshot['locations'] = array_map(function($row) {
        return $row['name'];
    }, $snapshot['location_counts']);

    $popularStmt = $db->prepare(
        "SELECT v.name, c.name AS category_name, l.name AS location_name, v.daily_rate,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COUNT(r.id) AS review_count
         FROM vehicles v
         LEFT JOIN categories c ON c.id = v.category_id
         LEFT JOIN locations l ON l.id = v.location_id
         LEFT JOIN reviews r ON r.vehicle_id = v.id
         GROUP BY v.id, v.name, c.name, l.name, v.daily_rate
         ORDER BY avg_rating DESC, review_count DESC, v.daily_rate ASC
         LIMIT 5"
    );
    $popularStmt->execute();
    $snapshot['popular_vehicles'] = $popularStmt->fetchAll(PDO::FETCH_ASSOC);

    return $snapshot;
}

function fetch_user_snapshot($db, $userId) {
    $snapshot = [
        'bookings_total' => 0,
        'confirmed_bookings' => 0,
        'pending_bookings' => 0,
        'favorites_total' => 0,
        'active_booking' => null
    ];

    $bookingStmt = $db->prepare(
        "SELECT COUNT(*) AS bookings_total,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_bookings,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_bookings
         FROM bookings
         WHERE user_id = :user_id"
    );
    $bookingStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $bookingStmt->execute();
    $bookingData = $bookingStmt->fetch(PDO::FETCH_ASSOC);
    if ($bookingData) {
        $snapshot['bookings_total'] = (int)($bookingData['bookings_total'] ?? 0);
        $snapshot['confirmed_bookings'] = (int)($bookingData['confirmed_bookings'] ?? 0);
        $snapshot['pending_bookings'] = (int)($bookingData['pending_bookings'] ?? 0);
    }

    $favoriteStmt = $db->prepare("SELECT COUNT(*) AS favorites_total FROM favorites WHERE user_id = :user_id");
    $favoriteStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $favoriteStmt->execute();
    $snapshot['favorites_total'] = (int)($favoriteStmt->fetchColumn() ?: 0);

    $activeStmt = $db->prepare(
        "SELECT b.id, b.start_date, b.end_date, b.status, b.total_price,
                v.name AS vehicle_name, p.name AS pickup_location, d.name AS dropoff_location
         FROM bookings b
         LEFT JOIN vehicles v ON v.id = b.vehicle_id
         LEFT JOIN locations p ON p.id = b.pickup_location_id
         LEFT JOIN locations d ON d.id = b.dropoff_location_id
         WHERE b.user_id = :user_id
           AND b.status IN ('pending', 'confirmed')
           AND b.end_date >= CURDATE()
         ORDER BY b.created_at DESC
         LIMIT 1"
    );
    $activeStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $activeStmt->execute();
    $activeBooking = $activeStmt->fetch(PDO::FETCH_ASSOC);
    if ($activeBooking) {
        $snapshot['active_booking'] = $activeBooking;
    }

    return $snapshot;
}

function build_rag_corpus($snapshot, $userSnapshot) {
    $docs = load_sawari_knowledge();

    $categorySummary = [];
    foreach ($snapshot['category_counts'] as $row) {
        $categorySummary[] = $row['name'] . ': ' . (int)$row['available_total'] . ' available';
    }

    $locationSummary = [];
    foreach ($snapshot['location_counts'] as $row) {
        $locationSummary[] = $row['name'] . ': ' . (int)$row['available_total'] . ' available';
    }

    $popularVehicles = [];
    foreach ($snapshot['popular_vehicles'] as $vehicle) {
        $popularVehicles[] = $vehicle['name'] . ' (' . $vehicle['category_name'] . ', ' . $vehicle['location_name'] . ', ' . format_rupees($vehicle['daily_rate']) . '/day)';
    }

    $dynamicDocs = [
        [
            'id' => 'fleet-overview',
            'title' => 'Fleet Overview',
            'tags' => ['fleet', 'vehicles', 'cars', 'available', 'price', 'categories'],
            'content' => 'The current fleet contains ' . $snapshot['vehicle_count'] . ' vehicles, with ' . $snapshot['available_count'] . ' currently available. Supported categories are ' . implode(', ', $snapshot['categories']) . '. Daily price range is from ' . format_rupees($snapshot['price_min']) . ' to ' . format_rupees($snapshot['price_max']) . '. Availability by category: ' . implode('; ', $categorySummary) . '.'
        ],
        [
            'id' => 'locations-overview',
            'title' => 'Location Coverage',
            'tags' => ['locations', 'cities', 'pickup', 'dropoff', 'branches', 'availability'],
            'content' => 'SAWARI rental hubs currently listed in the system are ' . implode(', ', $snapshot['locations']) . '. Availability by location: ' . implode('; ', $locationSummary) . '.'
        ],
        [
            'id' => 'dashboard-features',
            'title' => 'Dashboard Features',
            'tags' => ['dashboard', 'browse', 'favorites', 'ai advisor', 'search'],
            'content' => 'The customer dashboard includes search, favorites, vehicle carousels, trip-based AI recommendations, and a quick link to active rental tracking.'
        ],
        [
            'id' => 'popular-vehicles',
            'title' => 'Popular Vehicles',
            'tags' => ['popular', 'top rated', 'reviews', 'best vehicles'],
            'content' => 'Some of the strongest-rated vehicles in the current system data are ' . implode(', ', $popularVehicles) . '.'
        ]
    ];

    if ($userSnapshot) {
        $dynamicDocs[] = [
            'id' => 'user-account',
            'title' => 'Current User Account Summary',
            'tags' => ['my bookings', 'my account', 'my favorites', 'profile', 'personalized'],
            'content' => 'The current logged-in user has ' . $userSnapshot['bookings_total'] . ' total bookings, ' . $userSnapshot['confirmed_bookings'] . ' confirmed bookings, ' . $userSnapshot['pending_bookings'] . ' pending bookings, and ' . $userSnapshot['favorites_total'] . ' saved favorite vehicles.'
        ];

        if (!empty($userSnapshot['active_booking'])) {
            $active = $userSnapshot['active_booking'];
            $dynamicDocs[] = [
                'id' => 'user-active-booking',
                'title' => 'Current Active Booking',
                'tags' => ['active booking', 'my rental', 'track my rental', 'current booking'],
                'content' => 'The current logged-in user has an active booking for ' . $active['vehicle_name'] . ' from ' . $active['start_date'] . ' to ' . $active['end_date'] . ' with status ' . $active['status'] . '. Pickup location is ' . $active['pickup_location'] . ' and dropoff location is ' . $active['dropoff_location'] . '.'
            ];
        }
    } else {
        $dynamicDocs[] = [
            'id' => 'guest-account',
            'title' => 'Guest Account Behavior',
            'tags' => ['login', 'account', 'guest', 'profile'],
            'content' => 'Guests can browse the fleet, but personalized booking history, favorites, and live rental tracking require login.'
        ];
    }

    $categoryGuideDocs = [];
    foreach ($snapshot['category_counts'] as $row) {
        $categoryGuideDocs[] = [
            'id' => 'category-' . strtolower($row['name']),
            'title' => $row['name'] . ' Availability Snapshot',
            'tags' => [lower_text($row['name']), strtolower($row['name']) . ' vehicles', 'available ' . strtolower($row['name'])],
            'content' => $row['name'] . ' currently has ' . (int)$row['available_total'] . ' available vehicles in SAWARI out of ' . (int)$row['total'] . ' total listed in that category.'
        ];
    }

    return array_merge($docs, $dynamicDocs, $categoryGuideDocs);
}

function score_document($message, $document) {
    $score = 0;
    $keywordHits = 0;
    $messageTokens = unique_tokens($message);
    $tagText = lower_text(implode(' ', $document['tags']));
    $bodyText = lower_text($document['title'] . ' ' . $document['content']);
    $bodyTokens = array_flip(unique_tokens($bodyText));

    foreach ($document['tags'] as $tag) {
        $tagLower = lower_text($tag);
        if (strpos($message, $tagLower) !== false) {
            $score += strlen($tagLower) > 8 ? 9 : 7;
            $keywordHits++;
        }
    }

    foreach ($messageTokens as $token) {
        if (strlen($token) < 3) {
            continue;
        }
        if (strpos($tagText, $token) !== false) {
            $score += 4;
        }
        if (isset($bodyTokens[$token])) {
            $score += 1;
        }
    }

    return [
        'score' => $score,
        'keyword_hits' => $keywordHits
    ];
}

function retrieve_documents($message, $corpus, $limit = 4) {
    $scored = [];
    foreach ($corpus as $document) {
        $score = score_document($message, $document);
        $document['_score'] = $score['score'];
        $document['_keyword_hits'] = $score['keyword_hits'];
        $scored[] = $document;
    }

    usort($scored, function($a, $b) {
        return $b['_score'] <=> $a['_score'];
    });

    return array_values(array_filter(array_slice($scored, 0, $limit), function($document) {
        return ($document['_score'] ?? 0) > 0;
    }));
}

function is_greeting($message) {
    return preg_match('/\b(hi|hello|hey|namaste)\b/i', $message) === 1;
}

function is_out_of_scope($message, $documents) {
    if (is_greeting($message)) {
        return false;
    }

    foreach ($documents as $document) {
        if (($document['_keyword_hits'] ?? 0) > 0 || ($document['_score'] ?? 0) >= 8) {
            return false;
        }
    }

    if (contains_any($message, ['my booking', 'my bookings', 'my favorite', 'track my rental', 'payment', 'dashboard'])) {
        return false;
    }

    $systemWords = ['sawari', 'vehicle', 'car', 'book', 'booking', 'rental', 'rent', 'payment', 'tracking', 'support', 'favorite', 'review', 'location', 'pickup', 'dropoff', 'profile', 'dashboard', 'collateral'];
    return !contains_any($message, $systemWords);
}

function answer_precise_query($db, $message, $snapshot, $userSnapshot) {
    $category = detect_category($message);
    $location = detect_location($message);

    if ($userSnapshot) {
        if (contains_any($message, ['my bookings', 'my booking'])) {
            $reply = "You currently have {$userSnapshot['bookings_total']} bookings in SAWARI, with {$userSnapshot['confirmed_bookings']} confirmed and {$userSnapshot['pending_bookings']} pending.";
            if (!empty($userSnapshot['active_booking'])) {
                $active = $userSnapshot['active_booking'];
                $reply .= " Your latest active booking is {$active['vehicle_name']} from {$active['start_date']} to {$active['end_date']}.";
            }
            return $reply;
        }

        if (contains_any($message, ['my favorites', 'favorite vehicles'])) {
            return "You currently have {$userSnapshot['favorites_total']} favorite vehicles saved in your SAWARI account.";
        }
    } elseif (contains_any($message, ['my bookings', 'my booking', 'my favorites', 'my rental'])) {
        return "I can answer personal SAWARI account questions after login. Once signed in, I can help with your bookings, favorites, and active rental tracking.";
    }

    if (contains_any($message, ['which locations', 'what locations', 'where'])) {
        if ($category) {
            $stmt = $db->prepare(
                "SELECT l.name, COUNT(*) AS total
                 FROM vehicles v
                 LEFT JOIN categories c ON c.id = v.category_id
                 LEFT JOIN locations l ON l.id = v.location_id
                 WHERE v.status = 'available' AND c.name = :category
                 GROUP BY l.id, l.name
                 HAVING total > 0
                 ORDER BY total DESC, l.name ASC"
            );
            $stmt->bindValue(':category', $category);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $parts = array_map(function($row) {
                    return $row['name'] . ' (' . (int)$row['total'] . ')';
                }, $rows);
                return "Available {$category} vehicles are currently listed in " . implode(', ', $parts) . ".";
            }
        }
    }

    if (contains_any($message, ['cheapest', 'lowest price', 'budget car', 'least expensive'])) {
        $conditions = ["v.status = 'available'"];
        $params = [];

        if ($category) {
            $conditions[] = "c.name = :category";
            $params[':category'] = $category;
        }
        if ($location) {
            $conditions[] = "l.name = :location";
            $params[':location'] = $location;
        }

        $sql = "SELECT v.name, v.daily_rate, c.name AS category_name, l.name AS location_name
                FROM vehicles v
                LEFT JOIN categories c ON c.id = v.category_id
                LEFT JOIN locations l ON l.id = v.location_id
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY v.daily_rate ASC
                LIMIT 1";
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vehicle) {
            return "The cheapest available " . ($category ? strtolower($category) . ' ' : '') . "vehicle" .
                ($location ? " in {$location}" : '') . " is {$vehicle['name']} at " . format_rupees($vehicle['daily_rate']) .
                " per day.";
        }
    }

    if (contains_any($message, ['how many', 'available', 'count'])) {
        if ($category || $location) {
            $conditions = ["v.status = 'available'"];
            $params = [];
            $parts = [];

            if ($category) {
                $conditions[] = "c.name = :category";
                $params[':category'] = $category;
                $parts[] = $category;
            }
            if ($location) {
                $conditions[] = "l.name = :location";
                $params[':location'] = $location;
                $parts[] = $location;
            }

            $sql = "SELECT COUNT(*) AS total
                    FROM vehicles v
                    LEFT JOIN categories c ON c.id = v.category_id
                    LEFT JOIN locations l ON l.id = v.location_id
                    WHERE " . implode(' AND ', $conditions);
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $count = (int)$stmt->fetchColumn();

            return "SAWARI currently has {$count} available vehicles" .
                (!empty($parts) ? ' for ' . implode(' in ', $parts) : '') . ".";
        }

        return "SAWARI currently has {$snapshot['available_count']} available vehicles out of {$snapshot['vehicle_count']} total fleet vehicles.";
    }

    return null;
}

function build_local_answer($message, $documents, $userSnapshot) {
    if (is_greeting($message)) {
        return "Hi, I’m the SAWARI assistant. Ask me anything about bookings, vehicles, pricing, locations, tracking, favorites, support, or other features in this rental system.";
    }

    if (empty($documents)) {
        if ($userSnapshot) {
            return "I can help with SAWARI questions about booking, fleet availability, pricing, tracking, favorites, support, and your account activity.";
        }
        return "I can help with SAWARI questions about booking, fleet availability, pricing, tracking, support, and how this rental system works.";
    }

    $summary = array_map(function($document) {
        return $document['content'];
    }, array_slice($documents, 0, 2));

    return implode(' ', $summary);
}

function build_conversation_messages($history) {
    $messages = [];
    foreach (array_slice($history, -6) as $item) {
        $role = $item['role'] ?? 'user';
        if (!in_array($role, ['user', 'assistant'], true)) {
            continue;
        }
        $content = clean_text($item['content'] ?? '', 300);
        if ($content !== '') {
            $messages[] = [
                'role' => $role,
                'content' => $content
            ];
        }
    }
    return $messages;
}

function parse_mistral_content($content) {
    if (is_string($content)) {
        return $content;
    }
    if (is_array($content)) {
        $text = '';
        foreach ($content as $chunk) {
            if (isset($chunk['text'])) {
                $text .= $chunk['text'];
            }
        }
        return $text;
    }
    return '';
}

function mistral_rag_answer($message, $history, $documents, $preciseAnswer) {
    $apiKey = ai_config('mistral_api_key', '');
    if ($apiKey === '') {
        return null;
    }

    $retrievedContext = array_map(function($document) {
        return [
            'title' => $document['title'],
            'content' => $document['content']
        ];
    }, $documents);

    $messages = [
        [
            'role' => 'system',
            'content' => 'You are SAWARI Assistant, a personalized chatbot for a Nepal vehicle rental company. Answer only from the provided SAWARI context. If the user asks something outside SAWARI, company operations, rental policies, bookings, vehicles, locations, support, tracking, or their SAWARI account, return exactly a JSON object with scope "out_of_scope" and a short refusal. For in-scope questions, return a JSON object with scope "in_scope" and a concise helpful answer. Never invent facts beyond the supplied context. Output JSON only using this shape: {"scope":"in_scope|out_of_scope","answer":"..."}'
        ],
        [
            'role' => 'user',
            'content' => "SAWARI retrieved context:\n" . json_encode($retrievedContext, JSON_UNESCAPED_SLASHES) .
                ($preciseAnswer ? "\n\nDeterministic fact answer:\n" . $preciseAnswer : '') .
                "\n\nCurrent question:\n" . $message
        ]
    ];

    foreach (build_conversation_messages($history) as $historyMessage) {
        array_splice($messages, -1, 0, [$historyMessage]);
    }

    $payload = [
        'model' => ai_config('mistral_model', 'mistral-large-latest'),
        'messages' => $messages,
        'response_format' => ['type' => 'json_object'],
        'temperature' => 0.2,
        'max_tokens' => 280
    ];

    $ch = curl_init('https://api.mistral.ai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 20
    ]);

    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($raw === false || $httpCode < 200 || $httpCode >= 300) {
        return [
            'error' => $curlError ?: ('Mistral request failed with HTTP ' . $httpCode)
        ];
    }

    $decoded = json_decode($raw, true);
    $content = parse_mistral_content($decoded['choices'][0]['message']['content'] ?? '');
    $parsed = json_decode($content, true);

    if (!is_array($parsed) || empty($parsed['answer']) || empty($parsed['scope'])) {
        return [
            'error' => 'Mistral returned an unreadable response.'
        ];
    }

    return [
        'scope' => $parsed['scope'] === 'out_of_scope' ? 'out_of_scope' : 'in_scope',
        'answer' => clean_text($parsed['answer'], 900)
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(405, ['message' => 'Use POST for SAWARI assistant chat.']);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = [];
}

$message = clean_text($input['message'] ?? '', 700);
$history = is_array($input['history'] ?? null) ? $input['history'] : [];

if ($message === '') {
    send_json(422, ['message' => 'Please send a question for the SAWARI assistant.']);
}

$database = new Database();
$db = $database->getConnection();
$snapshot = fetch_system_snapshot($db);
$userSnapshot = isset($_SESSION['user_id']) ? fetch_user_snapshot($db, (int)$_SESSION['user_id']) : null;
$corpus = build_rag_corpus($snapshot, $userSnapshot);

$normalizedMessage = lower_text($message);
$retrievedDocuments = retrieve_documents($normalizedMessage, $corpus, 4);
$preciseAnswer = answer_precise_query($db, $normalizedMessage, $snapshot, $userSnapshot);
$outOfScope = is_out_of_scope($normalizedMessage, $retrievedDocuments);

if ($outOfScope) {
    send_json(200, [
        'assistant_name' => 'SAWARI Assistant',
        'engine' => 'local-guardrail',
        'scope' => 'out_of_scope',
        'message' => 'That is outside my scope. I can help only with SAWARI questions like vehicles, bookings, pricing, locations, tracking, support, favorites, reviews, and account activity.',
        'suggestions' => [
            'How do I book a vehicle?',
            'What collateral documents are accepted?',
            'Which locations have available SUVs?',
            'Can I track my active rental?'
        ]
    ]);
}

$mistralResponse = mistral_rag_answer($message, $history, $retrievedDocuments, $preciseAnswer);
$fallbackAnswer = $preciseAnswer ?: build_local_answer($normalizedMessage, $retrievedDocuments, $userSnapshot);

$responseScope = 'in_scope';
$responseMessage = $fallbackAnswer;
$responseEngine = 'local-rag';
$debugNote = null;

if (is_array($mistralResponse) && empty($mistralResponse['error'])) {
    $responseScope = $mistralResponse['scope'];
    $responseMessage = $mistralResponse['answer'];
    $responseEngine = 'mistral-rag';
} elseif (is_array($mistralResponse) && !empty($mistralResponse['error'])) {
    $debugNote = $mistralResponse['error'];
}

if ($responseScope === 'out_of_scope') {
    $responseMessage = 'That is outside my scope. I can help only with SAWARI questions like vehicles, bookings, pricing, locations, tracking, support, favorites, reviews, and account activity.';
}

send_json(200, [
    'assistant_name' => 'SAWARI Assistant',
    'engine' => $responseEngine,
    'scope' => $responseScope,
    'message' => $responseMessage,
    'sources' => array_values(array_map(function($document) {
        return $document['title'];
    }, $retrievedDocuments)),
    'suggestions' => [
        'How do I book a vehicle?',
        'What collateral documents are accepted?',
        'Which locations have available SUVs?',
        'Can I track my active rental?'
    ],
    'fallback_reason' => $debugNote
]);
?>
