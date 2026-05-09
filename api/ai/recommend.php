<?php
// api/ai/recommend.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include_once '../../config/database.php';

function send_json($status, $payload) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function clean_text($value, $maxLength = 500) {
    $value = trim(strip_tags((string)$value));
    if (strlen($value) > $maxLength) {
        return substr($value, 0, $maxLength);
    }
    return $value;
}

function clean_preferences($input) {
    return [
        "trip_type" => clean_text($input["trip_type"] ?? "general", 80),
        "destination" => clean_text($input["destination"] ?? "", 120),
        "terrain" => clean_text($input["terrain"] ?? "mixed", 80),
        "travelers" => max(1, min(12, (int)($input["travelers"] ?? 4))),
        "days" => max(1, min(60, (int)($input["days"] ?? 2))),
        "budget_per_day" => max(0, (float)($input["budget_per_day"] ?? 0)),
        "fuel_preference" => clean_text($input["fuel_preference"] ?? "any", 80),
        "notes" => clean_text($input["notes"] ?? "", 500)
    ];
}

function fetch_available_vehicles($db) {
    $query = "SELECT v.id, v.name, v.brand, v.model_year, v.seats, v.transmission,
                     v.fuel_type, v.daily_rate, v.image_url, v.status, v.description,
                     c.name AS category_name, l.name AS location_name,
                     COALESCE(r.avg_rating, 4.6) AS avg_rating,
                     COALESCE(r.review_count, 0) AS review_count
              FROM vehicles v
              LEFT JOIN categories c ON v.category_id = c.id
              LEFT JOIN locations l ON v.location_id = l.id
              LEFT JOIN (
                  SELECT vehicle_id, AVG(rating) AS avg_rating, COUNT(*) AS review_count
                  FROM reviews
                  GROUP BY vehicle_id
              ) r ON r.vehicle_id = v.id
              WHERE v.status = 'available'
              ORDER BY v.daily_rate ASC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function contains_any($haystack, $needles) {
    foreach ($needles as $needle) {
        if ($needle !== "" && strpos($haystack, $needle) !== false) {
            return true;
        }
    }
    return false;
}

function local_score_vehicle($vehicle, $preferences) {
    $text = strtolower(implode(" ", [
        $preferences["trip_type"],
        $preferences["destination"],
        $preferences["terrain"],
        $preferences["fuel_preference"],
        $preferences["notes"]
    ]));
    $category = strtolower($vehicle["category_name"] ?? "");
    $fuel = strtolower($vehicle["fuel_type"] ?? "");
    $transmission = strtolower($vehicle["transmission"] ?? "");
    $location = strtolower($vehicle["location_name"] ?? "");
    $description = strtolower($vehicle["description"] ?? "");
    $rate = (float)$vehicle["daily_rate"];
    $score = 50;

    if ((int)$vehicle["seats"] >= $preferences["travelers"]) {
        $score += 20;
        if ((int)$vehicle["seats"] - $preferences["travelers"] <= 2) {
            $score += 5;
        }
    } else {
        $score -= 40;
    }

    if ($preferences["budget_per_day"] > 0) {
        if ($rate <= $preferences["budget_per_day"]) {
            $score += 25;
            $score += max(0, 10 - (($preferences["budget_per_day"] - $rate) / max($preferences["budget_per_day"], 1)) * 10);
        } else {
            $overBy = ($rate - $preferences["budget_per_day"]) / max($preferences["budget_per_day"], 1);
            $score -= min(35, $overBy * 45);
        }
    }

    if ($preferences["destination"] && contains_any($location, [strtolower($preferences["destination"])])) {
        $score += 18;
    }

    if (contains_any($text, ["mountain", "offroad", "off-road", "himalaya", "mustang", "rough", "hill", "trek", "adventure", "jungle", "chitwan"])) {
        if (in_array($category, ["suv", "pickup"])) $score += 24;
        if ($fuel === "diesel") $score += 8;
        if (contains_any($description, ["terrain", "4wd", "off-road", "mountain"])) $score += 8;
    }

    if (contains_any($text, ["city", "airport", "business", "couple", "solo", "commute", "short"])) {
        if (in_array($category, ["sedan", "hatchback"])) $score += 20;
        if ($transmission === "automatic") $score += 6;
    }

    if (contains_any($text, ["family", "group", "friends", "children", "kids"])) {
        if ((int)$vehicle["seats"] >= 5) $score += 12;
        if (in_array($category, ["suv", "sedan"])) $score += 8;
    }

    if (contains_any($text, ["luxury", "premium", "wedding", "vip", "date"])) {
        if ($rate >= 12000) $score += 14;
        if (in_array($category, ["suv", "sedan"])) $score += 8;
    }

    if (contains_any($text, ["eco", "electric", "hybrid", "fuel", "mileage"])) {
        if (in_array($fuel, ["electric", "hybrid"])) $score += 24;
        if ($category === "hatchback") $score += 6;
    }

    if (contains_any($text, ["cargo", "equipment", "bags", "luggage", "moving"])) {
        if ($category === "pickup") $score += 24;
        if ($category === "suv") $score += 12;
    }

    $score += min(8, ((float)$vehicle["avg_rating"] - 4.0) * 8);
    return (int)round($score);
}

function local_recommendations($vehicles, $preferences, $limit = 5) {
    foreach ($vehicles as &$vehicle) {
        $vehicle["_match_score"] = local_score_vehicle($vehicle, $preferences);
    }
    unset($vehicle);

    usort($vehicles, function($a, $b) {
        if ($a["_match_score"] === $b["_match_score"]) {
            return (float)$a["daily_rate"] <=> (float)$b["daily_rate"];
        }
        return $b["_match_score"] <=> $a["_match_score"];
    });

    $recommendations = [];
    $topVehicles = array_slice($vehicles, 0, $limit);
    $rawScores = array_column($topVehicles, "_match_score");
    $minScore = min($rawScores);
    $maxScore = max($rawScores);

    foreach ($topVehicles as $vehicle) {
        $displayScore = $maxScore === $minScore
            ? max(1, min(99, (int)$vehicle["_match_score"]))
            : (int)round(70 + (($vehicle["_match_score"] - $minScore) / ($maxScore - $minScore)) * 29);
        $reasons = [];
        if ((int)$vehicle["seats"] >= $preferences["travelers"]) {
            $reasons[] = $vehicle["seats"] . " seats fit your group";
        }
        if ($preferences["budget_per_day"] > 0 && (float)$vehicle["daily_rate"] <= $preferences["budget_per_day"]) {
            $reasons[] = "within your daily budget";
        }
        if (in_array(strtolower($vehicle["fuel_type"]), ["electric", "hybrid"])) {
            $reasons[] = "efficient " . strtolower($vehicle["fuel_type"]) . " option";
        }
        if (empty($reasons)) {
            $reasons[] = "strong match for the trip details";
        }

        $recommendations[] = [
            "vehicle_id" => (int)$vehicle["id"],
            "match_score" => $displayScore,
            "headline" => "Best fit for " . ($preferences["trip_type"] ?: "your trip"),
            "reason" => ucfirst(implode(", ", array_slice($reasons, 0, 2))) . ".",
            "vehicle" => public_vehicle($vehicle)
        ];
    }

    return [
        "summary" => "These vehicles are ranked from SAWARI's live inventory using your trip details.",
        "recommendations" => $recommendations
    ];
}

function public_vehicle($vehicle) {
    return [
        "id" => (int)$vehicle["id"],
        "name" => $vehicle["name"],
        "brand" => $vehicle["brand"],
        "model_year" => (int)$vehicle["model_year"],
        "category_name" => $vehicle["category_name"],
        "location_name" => $vehicle["location_name"],
        "seats" => (int)$vehicle["seats"],
        "transmission" => $vehicle["transmission"],
        "fuel_type" => $vehicle["fuel_type"],
        "daily_rate" => (float)$vehicle["daily_rate"],
        "image_url" => $vehicle["image_url"],
        "status" => $vehicle["status"],
        "description" => $vehicle["description"],
        "avg_rating" => round((float)$vehicle["avg_rating"], 1),
        "review_count" => (int)$vehicle["review_count"]
    ];
}

function extract_mistral_text($response) {
    $content = $response["choices"][0]["message"]["content"] ?? "";
    if (is_string($content)) {
        return $content;
    }
    if (is_array($content)) {
        $text = "";
        foreach ($content as $chunk) {
            if (isset($chunk["text"])) {
                $text .= $chunk["text"];
            }
        }
        return $text;
    }
    return "";
}

function mistral_recommendations($vehicles, $preferences) {
    $apiKey = getenv("MISTRAL_API_KEY");
    if (!$apiKey) {
        return null;
    }

    $inventory = array_map(function($vehicle) {
        return [
            "id" => (int)$vehicle["id"],
            "name" => $vehicle["name"],
            "brand" => $vehicle["brand"],
            "year" => (int)$vehicle["model_year"],
            "category" => $vehicle["category_name"],
            "location" => $vehicle["location_name"],
            "seats" => (int)$vehicle["seats"],
            "transmission" => $vehicle["transmission"],
            "fuel" => $vehicle["fuel_type"],
            "daily_rate" => (float)$vehicle["daily_rate"],
            "rating" => round((float)$vehicle["avg_rating"], 1),
            "description" => $vehicle["description"]
        ];
    }, $vehicles);

    $prompt = "Rank exactly 5 vehicles from the inventory. Return only a JSON object with this shape: {\"summary\":\"short summary\",\"recommendations\":[{\"vehicle_id\":1,\"match_score\":95,\"headline\":\"short headline\",\"reason\":\"one concise customer-facing reason\"}]}. Use only vehicle ids from the inventory. Scores must be integers from 1 to 99.\n\nPreferences:\n" .
        json_encode($preferences, JSON_UNESCAPED_SLASHES) .
        "\n\nAvailable inventory:\n" .
        json_encode($inventory, JSON_UNESCAPED_SLASHES);

    $payload = [
        "model" => getenv("MISTRAL_MODEL") ?: "mistral-large-latest",
        "messages" => [
            [
                "role" => "system",
                "content" => "You are SAWARI's vehicle rental advisor for Nepal. Rank live fleet vehicles by trip fit, budget realism, passenger comfort, terrain, fuel, location, and rental practicality. Return trustworthy, concise JSON only."
            ],
            [
                "role" => "user",
                "content" => $prompt
            ]
        ],
        "response_format" => ["type" => "json_object"],
        "max_tokens" => 900
    ];

    $ch = curl_init("https://api.mistral.ai/v1/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 20
    ]);

    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($raw === false || $httpCode < 200 || $httpCode >= 300) {
        return ["error" => $curlError ?: "Mistral request failed with HTTP " . $httpCode];
    }

    $response = json_decode($raw, true);
    $text = extract_mistral_text($response ?? []);
    $decoded = json_decode($text, true);

    if (!is_array($decoded) || empty($decoded["recommendations"])) {
        return ["error" => "Mistral returned an unreadable recommendation payload."];
    }

    return $decoded;
}

function attach_vehicle_records($aiResult, $vehicles, $fallbackResult) {
    $byId = [];
    foreach ($vehicles as $vehicle) {
        $byId[(int)$vehicle["id"]] = $vehicle;
    }

    $recommendations = [];
    $seen = [];
    foreach (($aiResult["recommendations"] ?? []) as $recommendation) {
        $id = (int)($recommendation["vehicle_id"] ?? 0);
        if (!$id || isset($seen[$id]) || !isset($byId[$id])) {
            continue;
        }
        $seen[$id] = true;
        $recommendations[] = [
            "vehicle_id" => $id,
            "match_score" => max(1, min(99, (int)($recommendation["match_score"] ?? 80))),
            "headline" => clean_text($recommendation["headline"] ?? "Recommended option", 120),
            "reason" => clean_text($recommendation["reason"] ?? "Strong match for your trip.", 300),
            "vehicle" => public_vehicle($byId[$id])
        ];
    }

    foreach (($fallbackResult["recommendations"] ?? []) as $recommendation) {
        $id = (int)$recommendation["vehicle_id"];
        if (count($recommendations) >= 5 || isset($seen[$id])) {
            continue;
        }
        $seen[$id] = true;
        $recommendations[] = $recommendation;
    }

    return [
        "summary" => clean_text($aiResult["summary"] ?? $fallbackResult["summary"], 240),
        "recommendations" => $recommendations
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(405, ["message" => "Use POST to request AI recommendations."]);
}

$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    $input = [];
}

$preferences = clean_preferences($input);
$database = new Database();
$db = $database->getConnection();
$vehicles = fetch_available_vehicles($db);

if (empty($vehicles)) {
    send_json(404, ["message" => "No available vehicles found."]);
}

$fallback = local_recommendations($vehicles, $preferences);
$ai = mistral_recommendations($vehicles, $preferences);

if ($ai && empty($ai["error"])) {
    $result = attach_vehicle_records($ai, $vehicles, $fallback);
    send_json(200, [
        "engine" => "mistral",
        "preferences" => $preferences,
        "summary" => $result["summary"],
        "recommendations" => $result["recommendations"]
    ]);
}

send_json(200, [
    "engine" => "local",
    "preferences" => $preferences,
    "summary" => $fallback["summary"],
    "recommendations" => $fallback["recommendations"],
    "setup_hint" => "Set MISTRAL_API_KEY on the server to enable Mistral-powered recommendations.",
    "fallback_reason" => $ai["error"] ?? "MISTRAL_API_KEY is not configured."
]);
?>
