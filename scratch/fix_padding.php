<?php
$target_file = 'c:\\Users\\Aleazaaar_\\Desktop\\doc-marly-sqms\\modules\\service_staff\\dashboard.php';
$content = file_get_contents($target_file);

// 1. Remove style="padding: 24px;" from card-body-custom
$content = str_replace('<div class="card-body-custom text-center pt-4" style="padding: 24px;">', '<div class="card-body-custom text-center pt-4">', $content);
$content = str_replace('<div class="card-body-custom" style="padding: 24px;">', '<div class="card-body-custom">', $content);

// 2. Change the table design to match information staff exactly.
// The information staff uses inline styles. Let's rewrite the Right Column table.

$table_old = <<<HTML
                            <div style="overflow-x: auto;">
                                <table class="queue-table-modern">
                                    <thead>
                                        <tr>
                                            <th>Ticket No.</th>
                                            <th>Name / Category</th>
                                            <th>Service</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="waiting-list-body">
                                        <?php foreach (\$waitingList as \$ticket): ?>
                                            <?php \$canCall = in_array(\$ticket['service_id'], \$serviceIds); ?>
                                            <tr style="<?= !\$canCall ? 'opacity: 0.5;' : '' ?>">
                                                <td>
                                                    <span style="background: <?= \$canCall ? '#242364' : '#94a3b8' ?>; color: #fff; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-family: monospace; font-size: 0.9rem;">
                                                        <?= htmlspecialchars(\$ticket['ticket_number']) ?>
                                                    </span>
                                                </td>
                                                <td style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars(\$ticket['name'] ?? \$ticket['citizen_category']) ?></td>
                                                <td style="color: #64748b;"><i class="bi bi-tag me-1"></i> <?= htmlspecialchars(\$ticket['service_name']) ?></td>
                                                <td>
                                                    <?php if (\$canCall): ?>
                                                        <span style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Matching</span>
                                                    <?php else: ?>
                                                        <span style="background: rgba(100, 116, 139, 0.1); color: #64748b; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Other Counter</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
HTML;

$table_new = <<<HTML
                            <div style="overflow-x: auto;">
                                <table class="queue-table" style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #e9ecef;">
                                            <th style="padding:10px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#6c757d; font-weight:700;">Ticket No.</th>
                                            <th style="padding:10px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#6c757d; font-weight:700;">Name / Category</th>
                                            <th style="padding:10px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#6c757d; font-weight:700;">Service</th>
                                            <th style="padding:10px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#6c757d; font-weight:700;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="waiting-list-body">
                                        <?php foreach (\$waitingList as \$ticket): ?>
                                            <?php \$canCall = in_array(\$ticket['service_id'], \$serviceIds); ?>
                                            <tr style="<?= !\$canCall ? 'opacity: 0.5;' : '' ?>">
                                                <td style="padding:12px 16px; vertical-align:middle;">
                                                    <span class="ticket-number" style="background: <?= \$canCall ? '#242364' : '#94a3b8' ?>; color: #fff; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-family: monospace; font-size: 0.9rem;">
                                                        <?= htmlspecialchars(\$ticket['ticket_number']) ?>
                                                    </span>
                                                </td>
                                                <td style="padding:12px 16px; vertical-align:middle; color:#495057; font-weight:700;">
                                                    <?= htmlspecialchars(\$ticket['name'] ?? \$ticket['citizen_category']) ?>
                                                </td>
                                                <td style="padding:12px 16px; vertical-align:middle; color:#495057;">
                                                    <?= htmlspecialchars(\$ticket['service_name']) ?>
                                                </td>
                                                <td style="padding:12px 16px; vertical-align:middle;">
                                                    <?php if (\$canCall): ?>
                                                        <span class="ticket-status-badge waiting" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Matching</span>
                                                    <?php else: ?>
                                                        <span class="ticket-status-badge" style="background: rgba(100, 116, 139, 0.1); color: #64748b; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Other Counter</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
HTML;

$content = str_replace($table_old, $table_new, $content);

// Save changes
file_put_contents($target_file, $content);
echo "Formatting applied.\n";

// Also we need to update api/service_staff/waiting_list.php to match the new format so that auto-refresh doesn't revert it to the old format!
$api_file = 'c:\\Users\\Aleazaaar_\\Desktop\\doc-marly-sqms\\api\\service_staff\\waiting_list.php';
$api_content = file_get_contents($api_file);

$api_old = <<<HTML
        <tr style="<?= !\$canCall ? 'opacity: 0.5;' : '' ?>">
            <td>
                <span style="background: <?= \$canCall ? '#242364' : '#94a3b8' ?>; color: #fff; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-family: monospace; font-size: 0.9rem;">
                    <?= htmlspecialchars(\$ticket['ticket_number']) ?>
                </span>
            </td>
            <td style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars(\$ticket['name'] ?? \$ticket['citizen_category']) ?></td>
            <td style="color: #64748b;"><i class="bi bi-tag me-1"></i> <?= htmlspecialchars(\$ticket['service_name']) ?></td>
            <td>
                <?php if (\$canCall): ?>
                    <span style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Matching</span>
                <?php else: ?>
                    <span style="background: rgba(100, 116, 139, 0.1); color: #64748b; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Other Counter</span>
                <?php endif; ?>
            </td>
        </tr>
HTML;

$api_new = <<<HTML
        <tr style="<?= !\$canCall ? 'opacity: 0.5;' : '' ?>">
            <td style="padding:12px 16px; vertical-align:middle;">
                <span class="ticket-number" style="background: <?= \$canCall ? '#242364' : '#94a3b8' ?>; color: #fff; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-family: monospace; font-size: 0.9rem;">
                    <?= htmlspecialchars(\$ticket['ticket_number']) ?>
                </span>
            </td>
            <td style="padding:12px 16px; vertical-align:middle; color:#495057; font-weight:700;">
                <?= htmlspecialchars(\$ticket['name'] ?? \$ticket['citizen_category']) ?>
            </td>
            <td style="padding:12px 16px; vertical-align:middle; color:#495057;">
                <?= htmlspecialchars(\$ticket['service_name']) ?>
            </td>
            <td style="padding:12px 16px; vertical-align:middle;">
                <?php if (\$canCall): ?>
                    <span class="ticket-status-badge waiting" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Matching</span>
                <?php else: ?>
                    <span class="ticket-status-badge" style="background: rgba(100, 116, 139, 0.1); color: #64748b; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Other Counter</span>
                <?php endif; ?>
            </td>
        </tr>
HTML;

$api_content = str_replace($api_old, $api_new, $api_content);
file_put_contents($api_file, $api_content);
echo "API formatting applied.\n";
