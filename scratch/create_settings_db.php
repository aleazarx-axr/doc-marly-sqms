<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    // Create settings table
    $query = "CREATE TABLE IF NOT EXISTS `settings` (
        `setting_key` VARCHAR(50) NOT NULL PRIMARY KEY,
        `setting_value` TEXT NOT NULL,
        `description` VARCHAR(255) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($query);
    echo "Successfully created settings table.\n";

    // Insert default SMTP settings if they don't exist
    $defaults = [
        'smtp_host' => ['smtp.gmail.com', 'SMTP Server Host'],
        'smtp_port' => ['587', 'SMTP Server Port'],
        'smtp_user' => ['', 'SMTP Username (Gmail Address)'],
        'smtp_pass' => ['', 'SMTP App Password'],
        'smtp_from_email' => ['no-reply@docmarly.com', 'Sender Email Address'],
        'smtp_from_name' => ['Doc Marly SQMS', 'Sender Name']
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES (:key, :val, :desc)");
    foreach ($defaults as $key => $data) {
        $stmt->execute([
            ':key' => $key,
            ':val' => $data[0],
            ':desc' => $data[1]
        ]);
    }
    
    echo "Successfully inserted default settings.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
