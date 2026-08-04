<?php
/**
 * Quick ALTER to add 'cancelled' to transaksi.status ENUM.
 * Run: php run_alter.php
 * Delete after use.
 */
$conn = new mysqli('localhost', 'root', '', 'cpns_tryout', 3307);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error . "\n");
}

$sql = "ALTER TABLE `transaksi` MODIFY COLUMN `status` ENUM('pending','success','failed','expired','cancelled') NOT NULL DEFAULT 'pending'";
if ($conn->query($sql)) {
    echo "Done! Column `status` updated to include 'cancelled'.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
