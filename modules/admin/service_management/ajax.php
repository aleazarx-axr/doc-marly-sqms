<?php
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/models/Service.php';
require_once __DIR__ . '/../../../includes/models/Counter.php';
require_once __DIR__ . '/../../../includes/models/CounterService.php';
require_once __DIR__ . '/../../../includes/models/CounterCitizenCategory.php';

Session::requireRole('admin');

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$db = new Database();
$conn = $db->getConnection();

if ($action === 'save_counter') {
    $c = new Counter($conn);
    if (!empty($_POST['id'])) $c->id = $_POST['id'];
    $c->name = $_POST['name'];
    $c->counter_type = $_POST['counter_type'];
    $c->staff_id = !empty($_POST['staff_id']) ? $_POST['staff_id'] : null;
    $c->overflow_general = !empty($_POST['overflow_general']) ? 1 : 0;
    
    if ($c->id) {
        $success = $c->update();
    } else {
        $success = $c->create();
        $c->id = $conn->lastInsertId();
    }
    
    // Save categories if Priority
    if ($success && $c->counter_type === 'Priority') {
        $cats = $_POST['categories'] ?? [];
        $ccc = new CounterCitizenCategory($conn);
        $ccc->saveCategories($c->id, $cats);
    } elseif ($success && $c->counter_type !== 'Priority') {
        // Clear categories just in case
        $ccc = new CounterCitizenCategory($conn);
        $ccc->saveCategories($c->id, []);
    }
    // Save assigned services
    if ($success) {
        $assignedServices = $_POST['assigned_services'] ?? [];
        $cs = new CounterService($conn);
        $cs->saveAssignments($c->id, $assignedServices);
    }
    
    echo json_encode(['success' => $success]);
    exit;
}

if ($action === 'save_service') {
    $s = new Service($conn);
    if (!empty($_POST['id'])) $s->id = $_POST['id'];
    $s->code = $_POST['code'];
    $s->name = $_POST['name'];
    $s->description = $_POST['description'] ?? '';
    $s->requirements = $_POST['requirements'] ?? '';
    $s->prefix = $_POST['prefix'];
    $s->starting_number = $_POST['starting_number'] ?? 1;
    
    if ($s->id) {
        $success = $s->update();
    } else {
        $success = $s->create();
    }
    
    echo json_encode(['success' => $success]);
    exit;
}

if ($action === 'toggle_counter') {
    $c = new Counter($conn);
    $c->id = $_POST['id'];
    echo json_encode(['success' => $c->toggleStatus()]);
    exit;
}

if ($action === 'toggle_service') {
    $s = new Service($conn);
    $s->id = $_POST['id'];
    echo json_encode(['success' => $s->toggleStatus()]);
    exit;
}

if ($action === 'archive_service') {
    $s = new Service($conn);
    $s->id = $_POST['id'];
    echo json_encode(['success' => $s->archive()]);
    exit;
}



echo json_encode(['success' => false, 'error' => 'Invalid action']);
