<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Requirement.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $req_name = $_POST['req_name'] ?? '';

    if (!empty($req_name)) {
        $db = new Database();
        $conn = $db->getConnection();
        $reqModel = new Requirement($conn);
        
        $reqModel->name = $req_name;

        if ($reqModel->create()) {
            header('Location: index.php?status=added');
            exit();
        }
    }
    header('Location: index.php?status=error');
    exit();
}

$pageTitle = 'Add Requirement - Admin Portal';
$activeMenu = 'requirements';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>Add Requirement</h2>
    <form action="add.php" method="POST" style="max-width: 400px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Requirement Name:</label>
            <input type="text" name="req_name" required style="width: 100%; padding: 8px;">
        </div>
        <div style="margin-top: 25px;">
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save</button>
            <a href="index.php" style="margin-left: 15px; color: gray; text-decoration: underline;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
