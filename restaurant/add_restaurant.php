<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = mysqli_connect("sql205.infinityfree.com", "if0_41628608", "26122003hmu", "if0_41628608_mywebsite");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
    exit;
}

$name      = trim($_POST["name"]     ?? "");
$food      = trim($_POST["food"]     ?? "");
$location  = trim($_POST["location"] ?? "");
$budget    = trim($_POST["budget"]   ?? "");
$rating    = trim($_POST["rating"]   ?? "");
$review    = trim($_POST["review"]   ?? "");
$imageData = trim($_POST["image"]    ?? "");

if (!$name || !$food || !$location || !$budget || !$rating) {
    echo json_encode(["success" => false, "error" => "Please fill in all required fields."]);
    exit;
}

$rating = floatval($rating);
if ($rating < 1 || $rating > 5) {
    echo json_encode(["success" => false, "error" => "Rating must be between 1.0 and 5.0."]);
    exit;
}
$rating = round($rating, 1);

// Store base64 data URL directly in DB (no file system needed)
$imagePath = "";
if (!empty($imageData) && strpos($imageData, "data:image/") === 0) {
    // Validate it's a real image type
    if (preg_match('/^data:image\/(jpeg|jpg|png|webp|gif);base64,/i', $imageData)) {
        // Limit to 5MB of base64 (~3.75MB actual image)
        if (strlen($imageData) > 7 * 1024 * 1024) {
            echo json_encode(["success" => false, "error" => "Image must be under 5 MB."]);
            exit;
        }
        $imagePath = $imageData; // store the full base64 data URL
    }
}

// Duplicate check
$check = $conn->prepare("SELECT id FROM restaurants WHERE LOWER(name) = LOWER(?) AND LOWER(location) = LOWER(?)");
$check->bind_param("ss", $name, $location);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->close(); $conn->close();
    echo json_encode(["success" => false, "error" => "This restaurant already exists in our database."]);
    exit;
}
$check->close();

// Insert — image column needs to be MEDIUMTEXT or LONGTEXT for base64
$stmt = $conn->prepare("INSERT INTO restaurants (name, food, location, budget, rating, review, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssdss", $name, $food, $location, $budget, $rating, $review, $imagePath);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Restaurant added successfully!", "id" => $conn->insert_id]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to save: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
