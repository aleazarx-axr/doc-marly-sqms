<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    // 1. Update `services` table
    $sql = "ALTER TABLE services
            ADD COLUMN code VARCHAR(255) NULL AFTER id,
            ADD COLUMN description TEXT NULL AFTER name,
            ADD COLUMN queue_type ENUM('office', 'field', 'both') DEFAULT 'both' AFTER requirements,
            ADD COLUMN prefix VARCHAR(10) NULL AFTER queue_type,
            ADD COLUMN starting_number INT DEFAULT 1 AFTER prefix,
            ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            ADD COLUMN is_active TINYINT(1) DEFAULT 1;";
    $conn->exec($sql);
    
    // migrate is_archived to is_active
    $conn->exec("UPDATE services SET is_active = IF(is_archived = 1, 0, 1)");
    $conn->exec("ALTER TABLE services DROP COLUMN is_archived");
    $conn->exec("ALTER TABLE services ADD UNIQUE (code), ADD UNIQUE (prefix)");

    // 2. Update `counters` table
    $sql = "ALTER TABLE counters
            ADD COLUMN counter_type ENUM('General', 'Dedicated', 'Priority') DEFAULT 'General' AFTER name,
            ADD COLUMN location_type ENUM('office', 'field') DEFAULT 'office' AFTER counter_type,
            ADD COLUMN staff_id INT NULL AFTER location_type,
            ADD COLUMN overflow_general TINYINT(1) DEFAULT 0 AFTER staff_id,
            ADD COLUMN is_active TINYINT(1) DEFAULT 1,
            ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;";
    $conn->exec($sql);

    // migrate status to is_active
    $conn->exec("UPDATE counters SET is_active = IF(status = 'active', 1, 0)");
    $conn->exec("ALTER TABLE counters DROP COLUMN status");

    // 3. Update `counter_services` table
    $sql = "ALTER TABLE counter_services
            ADD COLUMN is_active TINYINT(1) DEFAULT 1,
            ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;";
    $conn->exec($sql);

    // 4. Create `counter_citizen_categories`
    $sql = "CREATE TABLE IF NOT EXISTS counter_citizen_categories (
              id INT NOT NULL AUTO_INCREMENT,
              counter_id INT NOT NULL,
              citizen_category VARCHAR(50) NOT NULL,
              created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (id),
              CONSTRAINT fk_ccc_counter FOREIGN KEY (counter_id) REFERENCES counters(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);

    // 5. Create `tickets` table
    $sql = "CREATE TABLE IF NOT EXISTS tickets (
              id INT NOT NULL AUTO_INCREMENT,
              ticket_number VARCHAR(50) NOT NULL,
              service_id INT NOT NULL,
              counter_id INT NULL,
              site_id INT NOT NULL,
              queue_type ENUM('office', 'field') NOT NULL,
              field_schedule_id INT NULL,
              citizen_category VARCHAR(50) NULL,
              status ENUM('waiting', 'called', 'serving', 'done', 'no-show', 'transferred') DEFAULT 'waiting',
              issued_at TIMESTAMP NULL,
              called_at TIMESTAMP NULL,
              served_at TIMESTAMP NULL,
              created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (id),
              CONSTRAINT fk_t_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
              CONSTRAINT fk_t_counter FOREIGN KEY (counter_id) REFERENCES counters(id) ON DELETE SET NULL,
              CONSTRAINT fk_t_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
