<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
$conn->query('ALTER TABLE tickets ADD COLUMN requirements_checked TEXT NULL AFTER citizen_category');
echo 'Added column requirements_checked';
