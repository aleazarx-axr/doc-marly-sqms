<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Ticket.php';
require_once __DIR__ . '/../../includes/models/Counter.php';

Session::requireLogin();
$role = Session::get('role');
$userId = Session::get('user_id');

$db = new Database();
$conn = $db->getConnection();
$ticketModel = new Ticket($conn);
$counterModel = new Counter($conn);

$records = [];
if ($role !== 'information_staff') {
    header("Location: /");
    exit();
}

$stmtRecords = $ticketModel->readAllRecords();
$records = $stmtRecords->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Manage Records - Staff Portal';
$activeMenu = 'records';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_user.php';
?>
<link rel="stylesheet" href="/assets/css/information_staff.css">
<style>
/* Enhance the simple-datatables look to match service staff table */
.dataTable-wrapper { font-family: sans-serif; }
.dataTable-table { border-collapse: collapse !important; width: 100% !important; }
.dataTable-top { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; }
.dataTable-search { margin-bottom: 0; }
.dataTable-input { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 14px; transition: border-color 0.2s; min-width: 250px; }
.dataTable-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.dataTable-dropdown label { font-size: 14px; color: #475569; display: flex; align-items: center; gap: 8px; }
.dataTable-selector { padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; font-size: 14px; color: #1e293b; cursor: pointer; }
.dataTable-bottom { display: flex; justify-content: space-between; align-items: center; padding-top: 15px; margin-top: 15px; border-top: 1px solid #e2e8f0; }
.dataTable-info { color: #64748b; font-size: 14px; }
.dataTable-pagination ul { display: flex; list-style: none; padding: 0; margin: 0; gap: 4px; }
.dataTable-pagination li a { padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; color: #475569; text-decoration: none; font-size: 14px; transition: all 0.2s; cursor: pointer; }
.dataTable-pagination li a:hover { background: #f1f5f9; border-color: #cbd5e1; }
.dataTable-pagination li.active a { background: #242364; color: #fff; border-color: #242364; }
.dataTable-table > thead > tr > th { background-color: #f8fafc !important; border-bottom: 2px solid #e2e8f0 !important; color: #475569 !important; font-weight: 600 !important; font-size: 13px !important; text-transform: uppercase !important; letter-spacing: 0.05em !important; padding: 14px 16px !important; text-align: left !important; }
.dataTable-table > tbody > tr > td { padding: 14px 16px !important; vertical-align: middle !important; border-bottom: 1px solid #f1f5f9 !important; color: #334155 !important; font-size: 14px !important; transition: background-color 0.2s; }
.dataTable-table > tbody > tr:hover > td { background-color: #f8fafc !important; }
.record-btn { background: #242364; color: #fff; border: none; padding: 6px 12px; border-radius: 8px; font-size: 13px; cursor: pointer; transition: opacity 0.2s; }
.record-btn:hover { opacity: 0.8; }
</style>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; color: #333;"><i class="bi bi-journal-text me-2"></i> Manage Records</h2>
    </div>
    <?php
        $totalWaitTime = 0;
        $waitCount = 0;
        foreach ($records as $row) {
            if (!empty($row['called_at']) && !empty($row['issued_at'])) {
                $issued = strtotime($row['issued_at']);
                $called = strtotime($row['called_at']);
                if ($called >= $issued) {
                    $totalWaitTime += ($called - $issued);
                    $waitCount++;
                }
            }
        }
        $avgWaitSec = $waitCount > 0 ? floor($totalWaitTime / $waitCount) : 0;
        $avgWaitMins = floor($avgWaitSec / 60);
        $avgWaitRem = $avgWaitSec % 60;
        $avgWaitStr = sprintf("%02d:%02d", $avgWaitMins, $avgWaitRem);
    ?>

    <!-- Service Staff Card Style for Stats -->
    <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap; margin-bottom: 30px;">
        <div style="flex: 1; min-width: 250px; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            <h3 style="margin-top: 0; color: #242364; font-size: 16px;">Avg Waiting Time</h3>
            <div style="font-size: 32px; font-weight: bold; color: #333; margin-top: 10px;">
                <?= $avgWaitStr ?> <span style="font-size: 16px; font-weight: normal; color: #666;">(min:sec)</span>
            </div>
        </div>
        
        <div style="flex: 1; min-width: 250px; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            <h3 style="margin-top: 0; color: #242364; font-size: 16px;">Total Tickets Issued</h3>
            <div style="font-size: 32px; font-weight: bold; color: #333; margin-top: 10px;">
                <?= count($records) ?>
            </div>
        </div>
    </div>

    <!-- Service Staff Card Style for Table -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: #242364;">
            <i class="bi bi-table me-2"></i> Ticket History
        </h3>
        
        <table id="recordsTable" style="width:100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Ticket No</th>
                    <th>Service Availed</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name'] ?? $row['citizen_category'] ?? 'N/A') ?></td>
                        <td><strong><?= htmlspecialchars($row['ticket_number'] ?? '') ?></strong></td>
                        <td><?= htmlspecialchars($row['service_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars(!empty($row['issued_at']) ? date('M d, Y h:i A', strtotime($row['issued_at'])) : 'N/A') ?></td>
                        <td>
                            <button class="record-btn" onclick='viewRecord(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8") ?>)'>
                                View
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- VIEW MODAL (Native HTML) -->
<div id="viewRecordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="background: #fff; width: 400px; margin: 100px auto; padding: 20px; border-radius: 8px;">
        <h3 style="margin-top:0;">Record Details</h3>
        <hr>
        <p><strong>ID:</strong> <span id="v_id"></span></p>
        <p><strong>Name:</strong> <span id="v_name"></span></p>
        <p><strong>Ticket No:</strong> <span id="v_ticket"></span></p>
        <p><strong>Citizen Category:</strong> <span id="v_category"></span></p>
        <p><strong>Service Availed:</strong> <span id="v_service"></span></p>
        <p><strong>Counter:</strong> <span id="v_counter"></span></p>
        <p><strong>Status:</strong> <span id="v_status"></span></p>
        <p><strong>Issued At:</strong> <span id="v_issued"></span></p>
        <p><strong>Called At:</strong> <span id="v_called"></span></p>
        <p><strong>Served At:</strong> <span id="v_served"></span></p>
        <div style="text-align:right; margin-top:15px;">
            <button onclick="document.getElementById('viewRecordModal').style.display='none'">Close</button>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    if (document.getElementById("recordsTable")) {
        new simpleDatatables.DataTable("#recordsTable", {
            searchable: true,
            perPage: 10,
            labels: {
                placeholder: "Search records...",
                perPage: "records per page",
                noRows: "No records found",
                info: "Showing {start} to {end} of {rows} records",
            }
        });
    }
});

function viewRecord(record) {
    document.getElementById('v_id').innerText = record.id;
    document.getElementById('v_name').innerText = record.name || 'N/A';
    document.getElementById('v_ticket').innerText = record.ticket_number || 'N/A';
    document.getElementById('v_category').innerText = record.citizen_category || 'N/A';
    document.getElementById('v_service').innerText = record.service_name || 'N/A';
    document.getElementById('v_counter').innerText = record.counter_name || 'N/A';
    document.getElementById('v_status').innerText = record.status || 'N/A';
    document.getElementById('v_issued').innerText = record.issued_at ? new Date(record.issued_at).toLocaleString() : 'N/A';
    document.getElementById('v_called').innerText = record.called_at ? new Date(record.called_at).toLocaleString() : 'N/A';
    document.getElementById('v_served').innerText = record.served_at ? new Date(record.served_at).toLocaleString() : 'N/A';
    
    document.getElementById('viewRecordModal').style.display = 'block';
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
