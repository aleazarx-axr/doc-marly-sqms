<?php
$content = file_get_contents('modules/information_staff/index.php');
$lines = explode("\n", $content);
foreach($lines as $i => $l) {
    if (stripos($l, '<form') !== false) {
        echo ($i+1) . ': ' . trim($l) . "\n";
    }
}
