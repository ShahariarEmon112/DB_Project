<?php
/**
 * Test Oracle Connection
 */
echo "<h2>Oracle Database Connection Test</h2>";

// Check OCI8
if (function_exists('oci_connect')) {
    echo "<p style='color:green'>✓ OCI8 extension is loaded</p>";
} else {
    echo "<p style='color:red'>✗ OCI8 extension is NOT loaded</p>";
    exit;
}

// Try connecting
$host = 'localhost';
$port = '1521';
$service = 'XEPDB1';
$username = 'gamearena';
$password = 'gamearena123';

$connStr = "$host:$port/$service";
echo "<p>Connecting to: $connStr as $username</p>";

$conn = @oci_connect($username, $password, $connStr, 'AL32UTF8');

if ($conn) {
    echo "<p style='color:green'>✓ Connected successfully!</p>";

    // Test query
    $stmt = oci_parse($conn, "SELECT table_name FROM user_tables ORDER BY table_name");
    oci_execute($stmt);
    echo "<h3>Tables in GAMEARENA schema:</h3><ul>";
    while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
        echo "<li>{$row['TABLE_NAME']}</li>";
    }
    echo "</ul>";
    oci_free_statement($stmt);

    // Test procedure
    echo "<h3>Testing PL/SQL Procedure:</h3>";
    $stmt = oci_parse($conn, "BEGIN GENERATE_LEADERBOARD(:tid, :st, :msg); END;");
    $tid = 1;
    $st = '';
    $msg = '';
    oci_bind_by_name($stmt, ':tid', $tid);
    oci_bind_by_name($stmt, ':st', $st, 50);
    oci_bind_by_name($stmt, ':msg', $msg, 500);
    oci_execute($stmt);
    echo "<p>Status: $st</p>";
    echo "<p>Message: $msg</p>";
    oci_free_statement($stmt);

    // Test function
    echo "<h3>Testing PL/SQL Function:</h3>";
    $stmt = oci_parse($conn, "SELECT GET_TEAM_WIN_RATE(1) AS win_rate FROM dual");
    oci_execute($stmt);
    $row = oci_fetch_array($stmt, OCI_ASSOC);
    echo "<p>Team 1 Win Rate: {$row['WIN_RATE']}%</p>";
    oci_free_statement($stmt);

    // Test view
    echo "<h3>Testing View (ACTIVE_TOURNAMENTS):</h3>";
    $stmt = oci_parse($conn, "SELECT * FROM ACTIVE_TOURNAMENTS");
    oci_execute($stmt);
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Name</th><th>Category</th><th>Status</th><th>Prize</th></tr>";
    while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
        echo "<tr><td>{$row['TOURNAMENT_NAME']}</td><td>{$row['CATEGORY_NAME']}</td><td>{$row['STATUS']}</td><td>{$row['PRIZE_POOL']}</td></tr>";
    }
    echo "</table>";
    oci_free_statement($stmt);

    oci_close($conn);
    echo "<p style='color:green'>✓ All tests passed!</p>";
} else {
    $e = oci_error();
    echo "<p style='color:red'>✗ Connection failed: {$e['message']}</p>";
}
