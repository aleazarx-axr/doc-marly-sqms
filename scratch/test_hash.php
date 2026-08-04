<?php
$hash = '$2y$12$dhcGzRnzZY9qki7ZFeLwN.97rlV1pNDEWoP0XpL6fCA9D09.Bhaw6';
$common_passwords = ['password', 'admin', '123456', 'password123', 'docmarly', '12345678', 'admin123', 'root'];
foreach ($common_passwords as $p) {
    if (password_verify($p, $hash)) {
        echo "Found! Plaintext is: $p\n";
        exit;
    }
}
echo "Not found in common passwords.\n";
