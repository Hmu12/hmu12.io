<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = mysqli_connect("sql205.infinityfree.com", "if0_41628608", "26122003hmu", "if0_41628608_mywebsite");
if (!$conn) {
    echo json_encode(["success" => false, "error" => "Connection failed: " . mysqli_connect_error()]); exit;
}
$conn->set_charset("utf8mb4");

$action  = trim($_POST["action"] ?? $_GET["action"] ?? "");
$user_id = intval($_POST["user_id"] ?? $_GET["user_id"] ?? 0);

if (!$user_id) {
    echo json_encode(["success" => false, "error" => "user_id required"]); exit;
}

if ($action === "get") {
    $stmt = $conn->prepare("
        SELECT r.id, r.name, r.food, r.budget, r.location, r.rating, r.image, r.review
        FROM user_favourites uf
        JOIN restaurants r ON uf.restaurant_id = r.id
        WHERE uf.user_id = ?
        ORDER BY uf.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $favs = [];
    while ($row = $result->fetch_assoc()) { $favs[] = $row; }
    $stmt->close();
    echo json_encode(["success" => true, "favourites" => $favs]);

} elseif ($action === "add") {
    $rid = intval($_POST["restaurant_id"] ?? 0);
    if (!$rid) { echo json_encode(["success" => false, "error" => "restaurant_id required"]); exit; }
    $stmt = $conn->prepare("INSERT IGNORE INTO user_favourites (user_id, restaurant_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $rid);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Added to favourites"]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    $stmt->close();

} elseif ($action === "remove") {
    $rid = intval($_POST["restaurant_id"] ?? 0);
    if (!$rid) { echo json_encode(["success" => false, "error" => "restaurant_id required"]); exit; }
    $stmt = $conn->prepare("DELETE FROM user_favourites WHERE user_id = ? AND restaurant_id = ?");
    $stmt->bind_param("ii", $user_id, $rid);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Removed from favourites"]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    $stmt->close();

} else {
    echo json_encode(["success" => false, "error" => "Unknown action: '$action'"]);
}
mysqli_close($conn);
?>