<?php
$conn = oci_connect('gamearena', 'gamearena123', 'localhost:1521/XEPDB1');
if (!$conn) { echo "CONNECT FAIL\n"; exit(1); }

// Check admin password hash
$s = oci_parse($conn, "SELECT user_id, username, password, role_id, is_active FROM users WHERE username = 'admin'");
oci_execute($s);
$row = oci_fetch_array($s, OCI_ASSOC);
if ($row) {
    echo "Admin password hash: " . $row['PASSWORD'] . "\n";
    echo "Verify password123: " . (password_verify('password123', $row['PASSWORD']) ? 'PASS OK' : 'FAIL - WRONG HASH') . "\n";
} else {
    echo "Admin user NOT FOUND!\n";
}
oci_free_statement($s);

// Check all role_ids
$s = oci_parse($conn, "SELECT role_id, role_name FROM roles ORDER BY role_id");
oci_execute($s);
echo "\nRoles:\n";
while ($row = oci_fetch_array($s, OCI_ASSOC)) {
    echo "  role_id=" . $row['ROLE_ID'] . " name=" . $row['ROLE_NAME'] . "\n";
}
oci_free_statement($s);

oci_close($conn);
