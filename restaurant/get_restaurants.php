<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("sql205.infinityfree.com", "if0_41628608", "26122003hmu", "if0_41628608_mywebsite");

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$conn->set_charset("utf8mb4");

$food     = isset($_GET['food'])     ? trim($_GET['food'])     : "";
$budget   = isset($_GET['budget'])   ? trim($_GET['budget'])   : "";
$location = isset($_GET['location']) ? trim($_GET['location']) : "";
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : "";

$conditions = [];
$params     = [];
$types      = "";

if ($search !== "") {
    $conditions[] = "name LIKE ?";
    $params[]     = "%" . $search . "%";
    $types       .= "s";
}
if ($food !== "") {
    $conditions[] = "food LIKE ?";
    $params[]     = "%" . $food . "%";
    $types       .= "s";
}
if ($budget !== "") {
    $conditions[] = "budget = ?";
    $params[]     = $budget;
    $types       .= "s";
}
if ($location !== "") {
    $conditions[] = "location LIKE ?";
    $params[]     = "%" . $location . "%";
    $types       .= "s";
}

$sql = "SELECT * FROM restaurants";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY rating DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "Prepare failed: " . $conn->error]);
    exit;
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
?>