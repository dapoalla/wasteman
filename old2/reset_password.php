<?php
include 'db.php';

// Reset a specific user's password
$username = 'itecsol_admin';
$new_password = 'Inioluwa@2025$#';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashed_password, $username);

if ($stmt->execute()) {
    echo "Password reset for $username to: $new_password";
} else {
    echo "Error: " . $conn->error;
}
?>