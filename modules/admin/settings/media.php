<?php
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/models/Setting.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$settingModel = new Setting($conn);

// Handle Video Deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete_video' && !empty($_GET['file'])) {
    $file = basename($_GET['file']);
    $path = __DIR__ . '/../../../assets/videos/' . $file;
    if (file_exists($path) && is_file($path)) {
        unlink($path);
    }
    header('Location: /admin/media?status=success');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_videos') {
        // Handle Promo Video Uploads (Multiple)
        if (isset($_FILES['promo_videos'])) {
            $uploadDir = __DIR__ . '/../../../assets/videos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            foreach ($_FILES['promo_videos']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['promo_videos']['error'][$key] === UPLOAD_ERR_OK) {
                    $name = basename($_FILES['promo_videos']['name'][$key]);
                    $name = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $name);
                    $destPath = $uploadDir . time() . '_' . $name;
                    move_uploaded_file($tmpName, $destPath);
                }
            }
        }
        header('Location: /admin/media?status=success');
        exit();

    }
}

$settings = $settingModel->getAll();

$pageTitle = 'Media Management - Admin Portal';
$activeMenu = 'media';

require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>Media Management</h2>
    
    <?php if(isset($_GET['status'])): ?>
        <?php
            $status = $_GET['status'];
            if ($status == 'success') { $msg = "Settings updated successfully."; $color = "green"; }
            if ($status == 'error') { $msg = "An error occurred while updating settings."; $color = "red"; }
        ?>
        <p style="color: <?php echo $color; ?>; background: #f0f9f0; padding: 10px; border: 1px solid <?php echo $color; ?>; margin-bottom: 15px;">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <div style="background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 600px;">
        <form action="/admin/media" method="POST" enctype="multipart/form-data" style="margin-bottom: 40px;">
            <input type="hidden" name="action" value="upload_videos">
            
            <h3 style="margin-top: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Live Display Promotional Videos</h3>
            <p style="font-size: 14px; color: #666; margin-bottom: 20px;">Upload MP4 videos (16:9 ratio recommended) to play on the Live Display. They will play in a continuous loop.</p>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Upload Videos (Select multiple):</label>
                <input type="file" name="promo_videos[]" accept="video/mp4" multiple required style="width: 100%; padding: 8px;">
            </div>

            <button type="submit" style="padding: 10px 20px; background-color: #0ea5e9; color: white; border: none; cursor: pointer; border-radius: 4px; margin-bottom: 20px;">Upload Videos</button>

            <div style="margin-bottom: 10px;">
                <strong>Current Playlist:</strong>
                <ul style="list-style-type: none; padding-left: 0;">
                    <?php
                    $videoDir = __DIR__ . '/../../../assets/videos';
                    if (is_dir($videoDir)) {
                        $files = scandir($videoDir);
                        $hasVideos = false;
                        foreach ($files as $file) {
                            if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
                                $hasVideos = true;
                                echo '<li style="padding: 5px 0; border-bottom: 1px dashed #ccc; display: flex; justify-content: space-between; align-items: center;">';
                                echo htmlspecialchars($file);
                                echo '<a href="/admin/media?action=delete_video&file=' . urlencode($file) . '" style="color: red; text-decoration: none; font-size: 12px; border: 1px solid red; padding: 2px 5px; border-radius: 3px;">Delete</a>';
                                echo '</li>';
                            }
                        }
                        if (!$hasVideos) {
                            echo '<li style="color: #888; font-style: italic;">No videos uploaded yet.</li>';
                        }
                    } else {
                        echo '<li style="color: #888; font-style: italic;">No videos uploaded yet.</li>';
                    }
                    ?>
                </ul>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
