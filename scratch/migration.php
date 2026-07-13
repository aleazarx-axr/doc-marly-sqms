<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
$conn->query("ALTER TABLE site_services ADD COLUMN counter_limit INT DEFAULT 1");
echo "Migration complete";
