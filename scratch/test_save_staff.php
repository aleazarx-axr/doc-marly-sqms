<?php
require 'config/database.php';
require 'includes/models/Counter.php';
$db = new Database();
$conn = $db->getConnection();
$c = new Counter($conn);

$c->saveStaffAssignments(1, [2, 5, 7]);

$stmt = $conn->query("SELECT * FROM counter_staff WHERE counter_id = 1");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
