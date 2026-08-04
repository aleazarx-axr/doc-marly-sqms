<?php
$source_file = 'c:\\Users\\Aleazaaar_\\Desktop\\doc-marly-sqms\\modules\\information_staff\\dashboard.php';
$target_file = 'c:\\Users\\Aleazaaar_\\Desktop\\doc-marly-sqms\\modules\\service_staff\\dashboard.php';

$source_content = file($source_file);
// Extract lines 281 to 659 (indices 280 to 659). PHP array is 0-indexed.
$widget_lines = array_slice($source_content, 280, 380); // 659 - 280 + 1 = 380
$widget = implode("", $widget_lines);

$target_content = file_get_contents($target_file);

// Replace '<link rel="stylesheet" href="/assets/css/information_staff.css">'
// with '<link rel="stylesheet" href="/assets/css/information_staff.css">'
// and '<link rel="stylesheet" href="/assets/css/clockwidget_ui.css">'
$target_content = str_replace(
    '<link rel="stylesheet" href="/assets/css/information_staff.css">',
    '<link rel="stylesheet" href="/assets/css/information_staff.css">' . "\n" . '<link rel="stylesheet" href="/assets/css/clockwidget_ui.css">',
    $target_content
);

// Inject widget before the script tag
$target_content = str_replace(
    '<script>',
    $widget . "\n<script>",
    $target_content
);

file_put_contents($target_file, $target_content);
echo "Successfully updated service_staff/dashboard.php\n";
