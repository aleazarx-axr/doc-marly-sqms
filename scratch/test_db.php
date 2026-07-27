<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
if ($conn) {
    echo "OK\n";
} else {
    echo "FAIL\n";
}
