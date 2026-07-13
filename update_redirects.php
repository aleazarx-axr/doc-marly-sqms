<?php
$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('modules'));
foreach ($iterator as $file) {
    if ($file->isFile() && in_array($file->getFilename(), ['add.php', 'edit.php', 'archive.php', 'assign.php', 'steps.php'])) {
        $content = file_get_contents($file->getPathname());
        
        $newContent = preg_replace(
            '/header\(\'Location: \.\.\/service_management\/index\.php\?tab=([a-z_]+)(?:&|\?|&\?)+status=([a-z_]+)\'\);/',
            "\$return_to = \$_REQUEST['return_to'] ?? 'office_services';\n        header(\"Location: ../{\$return_to}/index.php?tab=$1&status=$2\");",
            $content
        );
        
        $newContent = preg_replace(
            '/header\(\'Location: \.\.\/service_management\/index\.php\?tab=([a-z_]+)\'\);/',
            "\$return_to = \$_REQUEST['return_to'] ?? 'office_services';\n        header(\"Location: ../{\$return_to}/index.php?tab=$1\");",
            $newContent
        );
        
        if ($content !== $newContent) {
            file_put_contents($file->getPathname(), $newContent);
            echo "Updated {$file->getPathname()}\n";
        }
    }
}
echo "Done.\n";
