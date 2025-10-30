<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Use your actual credentials from db.php
$db_host = 'localhost';
$db_name = 'cyberros_waste';
$db_user = 'cyberros_wasteman';
$db_pass = 'w[p}yn7)rSB0jSQV';

try {
    echo "Attempting to connect to database with your credentials...<br>";
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully!<br><br>";
    
    // Get all users from the database
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
    
    if ($users) {
        echo "✅ Found " . count($users) . " users in database:<br><br>";
        
        foreach ($users as $user) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>";
            echo "<strong>ID:</strong> " . $user['id'] . "<br>";
            echo "<strong>Username:</strong> " . $user['username'] . "<br>";
            echo "<strong>Role:</strong> " . $user['role'] . "<br>";
            echo "<strong>Subsidiary:</strong> " . $user['subsidiary'] . "<br>";
            echo "<strong>Password Hash:</strong> " . $user['password'] . "<br>";
            
            // Test common passwords
            $test_passwords = ['admin123', 'itecsol', 'kongi', 'operator123', 'password', 'admin', 'operator', '123456'];
            
            echo "<strong>Testing passwords:</strong><br>";
            $found = false;
            foreach ($test_passwords as $test_pw) {
                if (password_verify($test_pw, $user['password'])) {
                    echo "🎉 <strong style='color: green;'>SUCCESS! Password: $test_pw</strong><br>";
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                echo "❌ None of the test passwords worked<br>";
            }
            
            echo "</div>";
        }
    } else {
        echo "❌ No users found in database<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
    echo "Please check your database credentials!<br>";
}
?>