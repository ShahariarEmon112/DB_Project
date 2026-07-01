<?php
$conn = @oci_connect('gamearena', 'gamearena123', 'localhost:1521/XEPDB1', 'AL32UTF8');
if ($conn) {
    echo 'Connection OK | ';
    $stmt = oci_parse($conn, 'SELECT COUNT(*) AS cnt FROM users');
    oci_execute($stmt);
    $row = oci_fetch_array($stmt, OCI_ASSOC);
    echo 'Users: ' . $row['CNT'] . ' | ';
    
    $stmt = oci_parse($conn, "SELECT password FROM users WHERE username = 'admin'");
    oci_execute($stmt);
    $row = oci_fetch_array($stmt, OCI_ASSOC);
    $hash = $row['PASSWORD'];
    echo 'Hash length: ' . strlen($hash) . ' | ';
    echo 'Verify password123: ' . (password_verify('password123', $hash) ? 'PASS' : 'FAIL');
} else {
    echo 'FAILED';
}
