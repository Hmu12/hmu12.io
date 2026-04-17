<?php
// db.php — database connection
// IMPORTANT: Use 'localhost' not 'sql205.infinityfree.com'
// The sql205 hostname is only for external tools (phpMyAdmin on your PC).
// PHP files on the same server MUST use 'localhost'.
 
$conn = mysqli_connect("localhost", "if0_41628608", "26122003hmu", "if0_41628608_mywebsite");
 
if (!$conn) {
    // Return JSON error instead of plain text die()
    // so fetch().json() in the browser can read it
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "error"   => "DB connection failed: " . mysqli_connect_error()
    ]);
    exit;
}
 
$conn->set_charset("utf8mb4");
?>
 