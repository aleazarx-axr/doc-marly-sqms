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
if ($role !== 'admin') {
    header("Location: /");
    exit();
}

$stmtRecords = $ticketModel->readAllRecords();
$records = $stmtRecords->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Manage Records - Admin Portal';
$activeMenu = 'records';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2>Records</h2>
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
    <div style="display: flex; gap: 20px; margin-bottom: 20px;">
        <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; width: 250px;">
            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #64748b;">Avg Waiting Time</h3>
            <div style="font-size: 28px; font-weight: bold; color: #0f172a;"><?= $avgWaitStr ?> <span style="font-size: 14px; font-weight: normal; color: #64748b;">(min:sec)</span></div>
        </div>
        <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; width: 250px;">
            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #64748b;">Total Tickets Issued</h3>
            <div style="font-size: 28px; font-weight: bold; color: #0f172a;"><?= count($records) ?></div>
        </div>
    </div>

    <div class="mb-4">
        <table id="recordsTable">
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
                    <tr class="record-row" data-name="<?= htmlspecialchars($row['name'] ?? '') ?>" data-ticket="<?= htmlspecialchars($row['ticket_number'] ?? '') ?>">
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name'] ?? $row['citizen_category'] ?? 'N/A') ?></td>
                        <td><strong><?= htmlspecialchars($row['ticket_number'] ?? '') ?></strong></td>
                        <td><?= htmlspecialchars($row['service_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($row['issued_at']))) ?></td>
                        <td>
                            <button onclick="viewRecord(<?= htmlspecialchars(json_encode($row)) ?>)">View</button>
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
