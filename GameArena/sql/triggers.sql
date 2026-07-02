-- =====================================================
-- GameArena - PL/SQL Triggers
-- =====================================================

-- =====================================================
-- TRIGGER 1: TRG_UPDATE_LEADERBOARD_AFTER_RESULT
-- Automatically update leaderboard after match result
-- =====================================================
CREATE OR REPLACE TRIGGER TRG_UPDATE_LEADERBOARD_AFTER_RESULT
AFTER INSERT OR UPDATE ON match_results
FOR EACH ROW
DECLARE
    v_team1     NUMBER;
    v_team2     NUMBER;
    v_tournament NUMBER;
BEGIN
    -- Get match details
    SELECT team1_id, team2_id, tournament_id
    INTO v_team1, v_team2, v_tournament
    FROM matches
    WHERE match_id = :NEW.match_id;

    -- Update winner stats in leaderboard
    IF :NEW.winner_id IS NOT NULL THEN
        MERGE INTO leaderboard lb
        USING (SELECT v_tournament AS tournament_id, :NEW.winner_id AS team_id FROM dual) src
        ON (lb.tournament_id = src.tournament_id AND lb.team_id = src.team_id)
        WHEN MATCHED THEN
            UPDATE SET wins = wins + 1,
                       points = points + 3,
                       matches_played = matches_played + 1,
                       last_updated = CURRENT_TIMESTAMP
        WHEN NOT MATCHED THEN
            INSERT (tournament_id, team_id, points, wins, matches_played)
            VALUES (src.tournament_id, src.team_id, 3, 1, 1);

        -- Update loser stats
        MERGE INTO leaderboard lb
        USING (SELECT v_tournament AS tournament_id,
                      CASE WHEN :NEW.winner_id = v_team1 THEN v_team2 ELSE v_team1 END AS team_id
               FROM dual) src
        ON (lb.tournament_id = src.tournament_id AND lb.team_id = src.team_id)
        WHEN MATCHED THEN
            UPDATE SET losses = losses + 1,
                       matches_played = matches_played + 1,
                       last_updated = CURRENT_TIMESTAMP
        WHEN NOT MATCHED THEN
            INSERT (tournament_id, team_id, points, losses, matches_played)
            VALUES (src.tournament_id, src.team_id, 0, 1, 1);

        -- Update ranks
        FOR rec IN (SELECT team_id, RANK() OVER (ORDER BY points DESC) AS rn
                    FROM leaderboard
                    WHERE tournament_id = v_tournament) LOOP
            UPDATE leaderboard
            SET rank_position = rec.rn
            WHERE tournament_id = v_tournament AND team_id = rec.team_id;
        END LOOP;
    END IF;
END;
/

-- =====================================================
-- TRIGGER 2: TRG_UPDATE_PLAYER_STATS
-- Automatically update player statistics after match
-- =====================================================
CREATE OR REPLACE TRIGGER TRG_UPDATE_PLAYER_STATS
AFTER INSERT OR UPDATE ON match_results
FOR EACH ROW
DECLARE
    v_tournament NUMBER;
    v_team1     NUMBER;
    v_team2     NUMBER;
BEGIN
    -- Get match details
    SELECT tournament_id, team1_id, team2_id
    INTO v_tournament, v_team1, v_team2
    FROM matches
    WHERE match_id = :NEW.match_id;

    -- Update winner team members stats
    FOR rec IN (SELECT user_id FROM team_members WHERE team_id = :NEW.winner_id) LOOP
        MERGE INTO player_statistics ps
        USING (SELECT rec.user_id AS player_id, v_tournament AS tournament_id FROM dual) src
        ON (ps.player_id = src.player_id AND ps.tournament_id = src.tournament_id)
        WHEN MATCHED THEN
            UPDATE SET wins = wins + 1,
                       points = points + 100,
                       matches_played = matches_played + 1,
                       updated_at = CURRENT_TIMESTAMP
        WHEN NOT MATCHED THEN
            INSERT (player_id, tournament_id, matches_played, wins, points)
            VALUES (src.player_id, src.tournament_id, 1, 1, 100);
    END LOOP;

    -- Update loser team members stats
    FOR rec IN (SELECT user_id FROM team_members WHERE team_id = CASE WHEN :NEW.winner_id = v_team1 THEN v_team2 ELSE v_team1 END) LOOP
        MERGE INTO player_statistics ps
        USING (SELECT rec.user_id AS player_id, v_tournament AS tournament_id FROM dual) src
        ON (ps.player_id = src.player_id AND ps.tournament_id = src.tournament_id)
        WHEN MATCHED THEN
            UPDATE SET losses = losses + 1,
                       matches_played = matches_played + 1,
                       updated_at = CURRENT_TIMESTAMP
        WHEN NOT MATCHED THEN
            INSERT (player_id, tournament_id, matches_played, losses, points)
            VALUES (src.player_id, src.tournament_id, 1, 1, 0);
    END LOOP;

    -- Update MVP count
    IF :NEW.mvp_player_id IS NOT NULL THEN
        UPDATE player_statistics
        SET mvp_count = mvp_count + 1,
            points = points + 50
        WHERE player_id = :NEW.mvp_player_id
          AND tournament_id = v_tournament;
    END IF;
END;
/

-- =====================================================
-- TRIGGER 3: TRG_AUDIT_LOG
-- Log every INSERT, UPDATE, DELETE into AUDIT_LOG
-- =====================================================
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
                '{"username":"' || :NEW.username || '","full_name":"' || :NEW.full_name || '"}',
                CURRENT_TIMESTAMP);
    ELSIF UPDATING THEN
        v_action := 'UPDATE';
        INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, new_values, performed_at)
        VALUES (v_log_id, 'USERS', :OLD.user_id, v_action,
                '{"username":"' || :OLD.username || '"}',
                '{"username":"' || :NEW.username || '"}',
                CURRENT_TIMESTAMP);
    ELSIF DELETING THEN
        v_action := 'DELETE';
        INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, performed_at)
        VALUES (v_log_id, 'USERS', :OLD.user_id, v_action,
                '{"username":"' || :OLD.username || '"}',
                CURRENT_TIMESTAMP);
    END IF;
END;
/

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
                '{"team_name":"' || :NEW.team_name || '"}',
                CURRENT_TIMESTAMP);
    ELSIF UPDATING THEN
        v_action := 'UPDATE';
        INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, new_values, performed_at)
        VALUES (v_log_id, 'TEAMS', :OLD.team_id, v_action,
                '{"team_name":"' || :OLD.team_name || '"}',
                '{"team_name":"' || :NEW.team_name || '"}',
                CURRENT_TIMESTAMP);
    ELSIF DELETING THEN
        v_action := 'DELETE';
        INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, performed_at)
        VALUES (v_log_id, 'TEAMS', :OLD.team_id, v_action,
                '{"team_name":"' || :OLD.team_name || '"}',
                CURRENT_TIMESTAMP);
    END IF;
END;
/

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
                '{"name":"' || :NEW.tournament_name || '","status":"' || :NEW.status || '"}',
                CURRENT_TIMESTAMP);
    ELSIF UPDATING THEN
        v_action := 'UPDATE';
        INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, new_values, performed_at)
        VALUES (v_log_id, 'TOURNAMENTS', :OLD.tournament_id, v_action,
                '{"name":"' || :OLD.tournament_name || '","status":"' || :OLD.status || '"}',
                '{"name":"' || :NEW.tournament_name || '","status":"' || :NEW.status || '"}',
                CURRENT_TIMESTAMP);
    ELSIF DELETING THEN
        v_action := 'DELETE';
        INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, performed_at)
        VALUES (v_log_id, 'TOURNAMENTS', :OLD.tournament_id, v_action,
                '{"name":"' || :OLD.tournament_name || '"}',
                CURRENT_TIMESTAMP);
    END IF;
END;
/

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
                '{"match_name":"' || :NEW.match_name || '","status":"' || :NEW.status || '"}',
                CURRENT_TIMESTAMP);
    ELSIF UPDATING THEN
        v_action := 'UPDATE';
        INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, new_values, performed_at)
        VALUES (v_log_id, 'MATCHES', :OLD.match_id, v_action,
                '{"status":"' || :OLD.status || '"}',
                '{"status":"' || :NEW.status || '"}',
                CURRENT_TIMESTAMP);
    ELSIF DELETING THEN
        v_action := 'DELETE';
        INSERT INTO audit_log (log_id, table_name, record_id, action, old_values, performed_at)
        VALUES (v_log_id, 'MATCHES', :OLD.match_id, v_action,
                '{"match_name":"' || :OLD.match_name || '"}',
                CURRENT_TIMESTAMP);
    END IF;
END;
/

-- =====================================================
-- TRIGGER 4: TRG_PREVENT_DUPLICATE_REGISTRATION
-- Prevent duplicate tournament registration
-- =====================================================
CREATE OR REPLACE TRIGGER TRG_PREVENT_DUPLICATE_REGISTRATION
BEFORE INSERT ON tournament_registrations
FOR EACH ROW
DECLARE
    v_count NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_count
    FROM tournament_registrations
    WHERE tournament_id = :NEW.tournament_id
      AND team_id = :NEW.team_id
      AND status != 'Rejected';

    IF v_count > 0 THEN
        RAISE_APPLICATION_ERROR(-20001, 'This team is already registered for this tournament.');
    END IF;
END;
/
