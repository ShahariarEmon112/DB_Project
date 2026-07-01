<?php
// Generate correct bcrypt hash for "password123"
$hash = password_hash('password123', PASSWORD_DEFAULT);
echo "Hash: $hash\n";
echo "Verify: " . (password_verify('password123', $hash) ? 'PASS' : 'FAIL') . "\n";

// Update all passwords in database
$conn = @oci_connect('gamearena', 'gamearena123', 'localhost:1521/XEPDB1', 'AL32UTF8');
if (!$conn) {
    die('Connection failed');
}

$stmt = oci_parse($conn, "UPDATE users SET password = :pwd");
oci_bind_by_name($stmt, ':pwd', $hash);
oci_execute($stmt);
oci_commit($conn);

$count = oci_num_rows($stmt);
echo "Updated $count users with new password hash\n";

// Verify admin login
$stmt = oci_parse($conn, "SELECT username, password FROM users WHERE username = 'admin'");
oci_execute($stmt);
$row = oci_fetch_array($stmt, OCI_ASSOC);
echo "Admin: {$row['USERNAME']} | Verify: " . (password_verify('password123', $row['PASSWORD']) ? 'PASS' : 'FAIL') . "\n";

oci_close($conn);
echo "Done!\n";
