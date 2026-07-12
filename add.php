<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>Staff Setup Script</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        die("Could not connect to the database.");
    }

    $username = "staff1";
    $plainPassword = "staff123";
    $role = "staff";
    
    // Hash the password for security
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    
    if ($stmt->rowCount() == 0) {
        // Insert the staff user
        $insertQuery = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);
        
        echo "<p>✅ Staff user created successfully!</p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> " . htmlspecialchars($username) . "</li>";
        echo "<li><strong>Password:</strong> " . htmlspecialchars($plainPassword) . "</li>";
        echo "<li><strong>Role:</strong> " . htmlspecialchars($role) . "</li>";
        echo "</ul>";
    } else {
        echo "<p>⚠️ Dummy user 'staff1' already exists in the database.</p>";
    }

} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Security Warning:</strong> Please delete this <code>add_staff.php</code> file after you have run it successfully!</p>";
echo "<a href='index.php'>Go to Login Page</a>";
?>
