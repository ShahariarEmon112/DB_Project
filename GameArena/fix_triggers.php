<?php
$conn = oci_connect('gamearena', 'gamearena123', 'localhost:1521/XEPDB1');
if (!$conn) { echo "CONNECT FAIL\n"; exit(1); }

$triggers = [
    "TRG_AUDIT_USERS" => "
        CREATE OR REPLACE TRIGGER TRG_AUDIT_USERS
        AFTER INSERT OR UPDATE OR DELETE ON users
        FOR EACH ROW
        DECLARE
            v_action VARCHAR2(10);
            v_log_id NUMBER;
        BEGIN
            SELECT NVL(MAX(log_id), 0) + 1 INTO v_log_id FROM audit_log;
            IF INSERTING THEN
                v_action := 'INSERT';
                INSERT INTO audit_log (log_id, table_name, record_id, action, new_values, performed_at)
                VALUES (v_log_id, 'USERS', :NEW.user_id, v_action,
                        '{\"username\":\"' || :NEW.username || '\",\"full_name\":\"' || :NEW.full_name || '\"}',
                        CURRENT_TIMESTAMP);
            ELSIF UPDATING THEN
                v_action := 'UPDATE';
                INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, new_values, performed_at)
                VALUES (v_log_id, 'USERS', :OLD.user_id, v_action,
                        '{\"username\":\"' || :OLD.username || '\"}',
                        '{\"username\":\"' || :NEW.username || '\"}',
                        CURRENT_TIMESTAMP);
            ELSIF DELETING THEN
                v_action := 'DELETE';
                INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, performed_at)
                VALUES (v_log_id, 'USERS', :OLD.user_id, v_action,
                        '{\"username\":\"' || :OLD.username || '\"}',
                        CURRENT_TIMESTAMP);
            END IF;
        END;",
    "TRG_AUDIT_TEAMS" => "
        CREATE OR REPLACE TRIGGER TRG_AUDIT_TEAMS
        AFTER INSERT OR UPDATE OR DELETE ON teams
        FOR EACH ROW
        DECLARE
            v_action VARCHAR2(10);
            v_log_id NUMBER;
        BEGIN
            SELECT NVL(MAX(log_id), 0) + 1 INTO v_log_id FROM audit_log;
            IF INSERTING THEN
                v_action := 'INSERT';
                INSERT INTO audit_log (log_id, table_name, record_id, action, new_values, performed_at)
                VALUES (v_log_id, 'TEAMS', :NEW.team_id, v_action,
                        '{\"team_name\":\"' || :NEW.team_name || '\"}',
                        CURRENT_TIMESTAMP);
            ELSIF UPDATING THEN
                v_action := 'UPDATE';
                INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, new_values, performed_at)
                VALUES (v_log_id, 'TEAMS', :OLD.team_id, v_action,
                        '{\"team_name\":\"' || :OLD.team_name || '\"}',
                        '{\"team_name\":\"' || :NEW.team_name || '\"}',
                        CURRENT_TIMESTAMP);
            ELSIF DELETING THEN
                v_action := 'DELETE';
                INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, performed_at)
                VALUES (v_log_id, 'TEAMS', :OLD.team_id, v_action,
                        '{\"team_name\":\"' || :OLD.team_name || '\"}',
                        CURRENT_TIMESTAMP);
            END IF;
        END;",
    "TRG_AUDIT_TOURNAMENTS" => "
        CREATE OR REPLACE TRIGGER TRG_AUDIT_TOURNAMENTS
        AFTER INSERT OR UPDATE OR DELETE ON tournaments
        FOR EACH ROW
        DECLARE
            v_action VARCHAR2(10);
            v_log_id NUMBER;
        BEGIN
            SELECT NVL(MAX(log_id), 0) + 1 INTO v_log_id FROM audit_log;
            IF INSERTING THEN
                v_action := 'INSERT';
                INSERT INTO audit_log (log_id, table_name, record_id, action, new_values, performed_at)
                VALUES (v_log_id, 'TOURNAMENTS', :NEW.tournament_id, v_action,
                        '{\"name\":\"' || :NEW.tournament_name || '\",\"status\":\"' || :NEW.status || '\"}',
                        CURRENT_TIMESTAMP);
            ELSIF UPDATING THEN
                v_action := 'UPDATE';
                INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, new_values, performed_at)
                VALUES (v_log_id, 'TOURNAMENTS', :OLD.tournament_id, v_action,
                        '{\"name\":\"' || :OLD.tournament_name || '\",\"status\":\"' || :OLD.status || '\"}',
                        '{\"name\":\"' || :NEW.tournament_name || '\",\"status\":\"' || :NEW.status || '\"}',
                        CURRENT_TIMESTAMP);
            ELSIF DELETING THEN
                v_action := 'DELETE';
                INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, performed_at)
                VALUES (v_log_id, 'TOURNAMENTS', :OLD.tournament_id, v_action,
                        '{\"name\":\"' || :OLD.tournament_name || '\"}',
                        CURRENT_TIMESTAMP);
            END IF;
        END;",
    "TRG_AUDIT_MATCHES" => "
        CREATE OR REPLACE TRIGGER TRG_AUDIT_MATCHES
        AFTER INSERT OR UPDATE OR DELETE ON matches
        FOR EACH ROW
        DECLARE
            v_action VARCHAR2(10);
            v_log_id NUMBER;
        BEGIN
            SELECT NVL(MAX(log_id), 0) + 1 INTO v_log_id FROM audit_log;
            IF INSERTING THEN
                v_action := 'INSERT';
                INSERT INTO audit_log (log_id, table_name, record_id, action, new_values, performed_at)
                VALUES (v_log_id, 'MATCHES', :NEW.match_id, v_action,
                        '{\"match_name\":\"' || :NEW.match_name || '\",\"status\":\"' || :NEW.status || '\"}',
                        CURRENT_TIMESTAMP);
            ELSIF UPDATING THEN
                v_action := 'UPDATE';
                INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, new_values, performed_at)
                VALUES (v_log_id, 'MATCHES', :OLD.match_id, v_action,
                        '{\"status\":\"' || :OLD.status || '\"}',
                        '{\"status\":\"' || :NEW.status || '\"}',
                        CURRENT_TIMESTAMP);
            ELSIF DELETING THEN
                v_action := 'DELETE';
                INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, performed_at)
                VALUES (v_log_id, 'MATCHES', :OLD.match_id, v_action,
                        '{\"match_name\":\"' || :OLD.match_name || '\"}',
                        CURRENT_TIMESTAMP);
            END IF;
        END;"
];

foreach ($triggers as $name => $sql) {
    $s = oci_parse($conn, $sql);
    if (!$s) {
        $e = oci_error($conn);
        echo "PARSE FAIL $name: " . $e['message'] . "\n";
        continue;
    }
    if (oci_execute($s)) {
        echo "$name OK\n";
    } else {
        $e = oci_error($s);
        echo "$name FAIL: " . $e['message'] . "\n";
    }
    oci_free_statement($s);
}

// Verify admin user role_id
$s = oci_parse($conn, "SELECT user_id, username, role_id, is_active FROM users WHERE username = 'admin'");
oci_execute($s);
$row = oci_fetch_array($s, OCI_ASSOC);
if ($row) {
    echo "\nAdmin user: ID=" . $row['USER_ID'] . " role_id=" . $row['ROLE_ID'] . " is_active=" . $row['IS_ACTIVE'] . "\n";
} else {
    echo "\nAdmin user NOT FOUND!\n";
}
oci_free_statement($s);

oci_close($conn);
echo "\nDone.\n";
