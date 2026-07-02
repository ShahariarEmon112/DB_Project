<?php
$conn = oci_connect('gamearena', 'gamearena123', 'localhost:1521/XEPDB1');
if (!$conn) { echo "CONNECT FAIL\n"; exit(1); }

$hashed = password_hash('password123', PASSWORD_DEFAULT);
echo "New hash: $hashed\n";

// Update all users to password123
$s = oci_parse($conn, "UPDATE users SET password = :pwd");
oci_bind_by_name($s, ':pwd', $hashed);
if (oci_execute($s)) {
    $count = oci_num_rows($s);
    echo "Updated $count users to password123\n";
} else {
    $e = oci_error($s);
    echo "FAIL: " . $e['message'] . "\n";
}
oci_free_statement($s);

// Verify admin
$s = oci_parse($conn, "SELECT username, password FROM users WHERE username = 'admin'");
oci_execute($s);
$row = oci_fetch_array($s, OCI_ASSOC);
echo "Admin verify: " . (password_verify('password123', $row['PASSWORD']) ? 'OK - login will work' : 'FAIL') . "\n";
oci_free_statement($s);

oci_close($conn);
