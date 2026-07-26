<?php require 'config/database.php'; $db = new Database(); $conn = $db->getConnection(); print_r($conn->query('SHOW CREATE TABLE users')->fetch(PDO::FETCH_ASSOC));
