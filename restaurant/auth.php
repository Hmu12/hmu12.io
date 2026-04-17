<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Direct connection — no require_once to avoid any file loading issues
$conn = mysqli_connect("sql205.infinityfree.com", "if0_41628608", "26122003hmu", "if0_41628608_mywebsite");
if (!$conn) {
    echo json_encode(["success" => false, "error" => "Connection failed: " . mysqli_connect_error()]);
    exit;
}
$conn->set_charset("utf8mb4");

$action = trim($_POST["action"] ?? "");

if ($action === "register") {
    $name  = trim($_POST["name"]  ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $pass  = $_POST["pass"] ?? "";

    if (!$name || !$email || !$pass) {
        echo json_encode(["success" => false, "error" => "Please fill in all fields."]); exit;
    }
    if (strlen($pass) < 6) {
        echo json_encode(["success" => false, "error" => "Password must be at least 6 characters."]); exit;
    }

    // Check duplicate
    $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $chk->bind_param("s", $email);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) {
        $chk->close();
        echo json_encode(["success" => false, "error" => "Email already registered."]); exit;
    }
    $chk->close();

    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "DB error: " . $conn->error]); exit;
    }
    $stmt->bind_param("sss", $name, $email, $hash);
    if ($stmt->execute()) {
        $uid = $conn->insert_id;
        $stmt->close();
        echo json_encode(["success" => true, "user_id" => $uid, "name" => $name, "email" => $email]);
    } else {
        $err = $stmt->error; $stmt->close();
        echo json_encode(["success" => false, "error" => "Insert failed: " . $err]);
    }

} elseif ($action === "login") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $pass  = $_POST["pass"] ?? "";

    if (!$email || !$pass) {
        echo json_encode(["success" => false, "error" => "Please fill in all fields."]); exit;
    }

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "DB error: " . $conn->error]); exit;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($uid, $uname, $hash);
    $found = $stmt->fetch();
    $stmt->close();

    if (!$found || !password_verify($pass, $hash)) {
        echo json_encode(["success" => false, "error" => "Incorrect email or password."]); exit;
    }
    echo json_encode(["success" => true, "user_id" => $uid, "name" => $uname, "email" => $email]);

} else {
    echo json_encode(["success" => false, "error" => "Unknown action: '$action'"]);
}
mysqli_close($conn);
?>