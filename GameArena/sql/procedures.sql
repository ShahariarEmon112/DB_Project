-- =====================================================
-- GameArena - PL/SQL Procedures
-- =====================================================

-- =====================================================
-- PROCEDURE 1: REGISTER_TEAM
-- Register a team for a tournament
-- =====================================================
CREATE OR REPLACE PROCEDURE REGISTER_TEAM(
    p_tournament_id IN NUMBER,
    p_team_id       IN NUMBER,
    p_registered_by IN NUMBER,
    p_status        OUT VARCHAR2,
    p_message       OUT VARCHAR2
) AS
    v_deadline     DATE;
    v_max_teams    NUMBER;
    v_current      NUMBER;
    v_team_exists  NUMBER;
    v_registration NUMBER;
BEGIN
    -- Check registration deadline
    SELECT registration_deadline, max_teams
    INTO v_deadline, v_max_teams
    FROM tournaments
    WHERE tournament_id = p_tournament_id;

    IF SYSDATE > v_deadline THEN
        p_status := 'ERROR';
        p_message := 'Registration deadline has passed.';
        RETURN;
    END IF;

    -- Check max teams
    SELECT COUNT(*)
    INTO v_current
    FROM tournament_registrations
    WHERE tournament_id = p_tournament_id
      AND status != 'Rejected';

    IF v_current >= v_max_teams THEN
        p_status := 'ERROR';
        p_message := 'Tournament is full. Maximum teams reached.';
        RETURN;
    END IF;

    -- Check if already registered
    SELECT COUNT(*)
    INTO v_team_exists
    FROM tournament_registrations
    WHERE tournament_id = p_tournament_id
      AND team_id = p_team_id;

    IF v_team_exists > 0 THEN
        p_status := 'ERROR';
        p_message := 'Team is already registered for this tournament.';
        RETURN;
    END IF;

    -- Register the team
    INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status)
    VALUES (p_tournament_id, p_team_id, p_registered_by, 'Pending');

    p_status := 'SUCCESS';
    p_message := 'Team registered successfully. Awaiting confirmation.';
    COMMIT;

EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        p_status := 'ERROR';
        p_message := 'Error: ' || SQLERRM;
END REGISTER_TEAM;
/

-- =====================================================
-- PROCEDURE 2: CREATE_MATCH
-- Create a new match in a tournament
-- =====================================================
CREATE OR REPLACE PROCEDURE CREATE_MATCH(
    p_tournament_id IN NUMBER,
    p_match_name    IN VARCHAR2,
    p_team1_id      IN NUMBER,
    p_team2_id      IN NUMBER,
    p_match_date    IN DATE,
    p_match_time    IN VARCHAR2,
    p_venue         IN VARCHAR2,
    p_round         IN VARCHAR2,
    p_status        OUT VARCHAR2,
    p_message       OUT VARCHAR2
) AS
    v_tourn_status VARCHAR2(20);
    v_team1_reg    NUMBER;
    v_team2_reg    NUMBER;
BEGIN
    -- Check tournament status
    SELECT status INTO v_tourn_status
    FROM tournaments
    WHERE tournament_id = p_tournament_id;

    IF v_tourn_status != 'Active' AND v_tourn_status != 'Upcoming' THEN
        p_status := 'ERROR';
        p_message := 'Cannot create match for a ' || v_tourn_status || ' tournament.';
        RETURN;
    END IF;

    -- Check teams are registered
    SELECT COUNT(*) INTO v_team1_reg
    FROM tournament_registrations
    WHERE tournament_id = p_tournament_id AND team_id = p_team1_id AND status = 'Confirmed';

    SELECT COUNT(*) INTO v_team2_reg
    FROM tournament_registrations
    WHERE tournament_id = p_tournament_id AND team_id = p_team2_id AND status = 'Confirmed';

    IF v_team1_reg = 0 OR v_team2_reg = 0 THEN
        p_status := 'ERROR';
        p_message := 'Both teams must be registered and confirmed.';
        RETURN;
    END IF;

    -- Check teams are different
    IF p_team1_id = p_team2_id THEN
        p_status := 'ERROR';
        p_message := 'A team cannot play against itself.';
        RETURN;
    END IF;

    -- Create the match
    INSERT INTO matches (tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status)
    VALUES (p_tournament_id, p_match_name, p_team1_id, p_team2_id, p_match_date, p_match_time, p_venue, p_round, 'Scheduled');

    p_status := 'SUCCESS';
    p_message := 'Match created successfully.';
    COMMIT;

EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        p_status := 'ERROR';
        p_message := 'Error: ' || SQLERRM;
END CREATE_MATCH;
/

-- =====================================================
-- PROCEDURE 3: UPDATE_MATCH_RESULT
-- Update match result and trigger leaderboard update
-- =====================================================
CREATE OR REPLACE PROCEDURE UPDATE_MATCH_RESULT(
    p_match_id      IN NUMBER,
    p_team1_score   IN NUMBER,
    p_team2_score   IN NUMBER,
    p_winner_id     IN NUMBER,
    p_mvp_player_id IN NUMBER,
    p_duration      IN NUMBER,
    p_notes         IN VARCHAR2,
    p_recorded_by   IN NUMBER,
    p_status        OUT VARCHAR2,
    p_message       OUT VARCHAR2
) AS
    v_match_status VARCHAR2(20);
    v_tournament   NUMBER;
    v_team1        NUMBER;
    v_team2        NUMBER;
BEGIN
    -- Check match exists and is scheduled
    SELECT status, tournament_id, team1_id, team2_id
    INTO v_match_status, v_tournament, v_team1, v_team2
    FROM matches
    WHERE match_id = p_match_id;

    IF v_match_status = 'Completed' THEN
        p_status := 'ERROR';
        p_message := 'Match result already recorded.';
        RETURN;
    END IF;

    IF v_match_status = 'Cancelled' THEN
        p_status := 'ERROR';
        p_message := 'Cannot record result for a cancelled match.';
        RETURN;
    END IF;

    -- Validate winner
    IF p_winner_id NOT IN (v_team1, v_team2) THEN
        p_status := 'ERROR';
        p_message := 'Winner must be one of the participating teams.';
        RETURN;
    END IF;

    -- Insert or update result
    MERGE INTO match_results mr
    USING (SELECT p_match_id AS match_id FROM dual) src
    ON (mr.match_id = src.match_id)
    WHEN MATCHED THEN
        UPDATE SET team1_score = p_team1_score,
                   team2_score = p_team2_score,
                   winner_id = p_winner_id,
                   mvp_player_id = p_mvp_player_id,
                   duration_mins = p_duration,
                   notes = p_notes,
                   recorded_by = p_recorded_by,
                   recorded_at = CURRENT_TIMESTAMP
    WHEN NOT MATCHED THEN
        INSERT (match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, notes, recorded_by)
        VALUES (p_match_id, p_team1_score, p_team2_score, p_winner_id, p_mvp_player_id, p_duration, p_notes, p_recorded_by);

    -- Update match status
    UPDATE matches SET status = 'Completed' WHERE match_id = p_match_id;

    -- Update player statistics for winner
    UPDATE player_statistics
    SET wins = wins + 1,
        points = points + 100,
        matches_played = matches_played + 1,
        updated_at = CURRENT_TIMESTAMP
    WHERE tournament_id = v_tournament
      AND player_id IN (SELECT user_id FROM team_members WHERE team_id = p_winner_id);

    -- Update player statistics for loser
    UPDATE player_statistics
    SET losses = losses + 1,
        matches_played = matches_played + 1,
        updated_at = CURRENT_TIMESTAMP
    WHERE tournament_id = v_tournament
      AND player_id IN (SELECT user_id FROM team_members WHERE team_id = CASE WHEN p_winner_id = v_team1 THEN v_team2 ELSE v_team1 END);

    -- Update MVP
    UPDATE player_statistics
    SET mvp_count = mvp_count + 1,
        points = points + 50
    WHERE tournament_id = v_tournament
      AND player_id = p_mvp_player_id;

    -- Update leaderboard for winner
    MERGE INTO leaderboard lb
    USING (SELECT v_tournament AS tournament_id, p_winner_id AS team_id FROM dual) src
    ON (lb.tournament_id = src.tournament_id AND lb.team_id = src.team_id)
    WHEN MATCHED THEN
        UPDATE SET wins = wins + 1,
                   points = points + 3,
                   matches_played = matches_played + 1,
                   last_updated = CURRENT_TIMESTAMP
    WHEN NOT MATCHED THEN
        INSERT (tournament_id, team_id, points, wins, matches_played)
        VALUES (src.tournament_id, src.team_id, 3, 1, 1);

    -- Update leaderboard for loser
    MERGE INTO leaderboard lb
    USING (SELECT v_tournament AS tournament_id, CASE WHEN p_winner_id = v_team1 THEN v_team2 ELSE v_team1 END AS team_id FROM dual) src
    ON (lb.tournament_id = src.tournament_id AND lb.team_id = src.team_id)
    WHEN MATCHED THEN
        UPDATE SET losses = losses + 1,
                   matches_played = matches_played + 1,
                   last_updated = CURRENT_TIMESTAMP
    WHEN NOT MATCHED THEN
        INSERT (tournament_id, team_id, points, losses, matches_played)
        VALUES (src.tournament_id, src.team_id, 0, 1, 1);

    -- Update ranks using cursor
    FOR rec IN (SELECT team_id, RANK() OVER (ORDER BY points DESC) AS rn
                FROM leaderboard
                WHERE tournament_id = v_tournament) LOOP
        UPDATE leaderboard
        SET rank_position = rec.rn
        WHERE tournament_id = v_tournament AND team_id = rec.team_id;
    END LOOP;

    p_status := 'SUCCESS';
    p_message := 'Match result recorded and leaderboard updated.';
    COMMIT;

EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        p_status := 'ERROR';
        p_message := 'Error: ' || SQLERRM;
END UPDATE_MATCH_RESULT;
/

-- =====================================================
-- PROCEDURE 4: GENERATE_LEADERBOARD
-- Generate/refresh leaderboard for a tournament
-- =====================================================
CREATE OR REPLACE PROCEDURE GENERATE_LEADERBOARD(
    p_tournament_id IN NUMBER,
    p_status        OUT VARCHAR2,
    p_message       OUT VARCHAR2
) AS
    v_count NUMBER;
BEGIN
    -- Delete existing leaderboard
    DELETE FROM leaderboard WHERE tournament_id = p_tournament_id;

    -- Generate new leaderboard from match results
    INSERT INTO leaderboard (tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played)
    SELECT
        p_tournament_id,
        team_id,
        RANK() OVER (ORDER BY total_points DESC) AS rank_pos,
        total_points,
        total_wins,
        total_losses,
        0,
        (total_wins + total_losses)
    FROM (
        SELECT
            team_id,
            SUM(CASE WHEN winner_id = team_id THEN 3 ELSE 0 END) AS total_points,
            SUM(CASE WHEN winner_id = team_id THEN 1 ELSE 0 END) AS total_wins,
            SUM(CASE WHEN winner_id != team_id THEN 1 ELSE 0 END) AS total_losses
        FROM (
            SELECT m.team1_id AS team_id, mr.winner_id
            FROM matches m
            JOIN match_results mr ON m.match_id = mr.match_id
            WHERE m.tournament_id = p_tournament_id
            UNION ALL
            SELECT m.team2_id AS team_id, mr.winner_id
            FROM matches m
            JOIN match_results mr ON m.match_id = mr.match_id
            WHERE m.tournament_id = p_tournament_id
        )
        GROUP BY team_id
    );

    SELECT COUNT(*) INTO v_count FROM leaderboard WHERE tournament_id = p_tournament_id;

    p_status := 'SUCCESS';
    p_message := 'Leaderboard generated with ' || v_count || ' teams.';
    COMMIT;

EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        p_status := 'ERROR';
        p_message := 'Error: ' || SQLERRM;
END GENERATE_LEADERBOARD;
/

-- =====================================================
-- PROCEDURE 5: GENERATE_TOURNAMENT_REPORT
-- Generate comprehensive tournament report
-- =====================================================
CREATE OR REPLACE PROCEDURE GENERATE_TOURNAMENT_REPORT(
    p_tournament_id IN NUMBER,
    p_total_teams   OUT NUMBER,
    p_total_matches OUT NUMBER,
    p_completed     OUT NUMBER,
    p_top_team      OUT VARCHAR2,
    p_top_player    OUT VARCHAR2,
    p_total_goals   OUT NUMBER
) AS
BEGIN
    -- Total registered teams
    SELECT COUNT(*) INTO p_total_teams
    FROM tournament_registrations
    WHERE tournament_id = p_tournament_id AND status = 'Confirmed';

    -- Total matches
    SELECT COUNT(*) INTO p_total_matches
    FROM matches
    WHERE tournament_id = p_tournament_id;

    -- Completed matches
    SELECT COUNT(*) INTO p_completed
    FROM matches
    WHERE tournament_id = p_tournament_id AND status = 'Completed';

    -- Top team
    BEGIN
        SELECT t.team_name INTO p_top_team
        FROM leaderboard lb
        JOIN teams t ON lb.team_id = t.team_id
        WHERE lb.tournament_id = p_tournament_id
        ORDER BY lb.points DESC
        FETCH FIRST 1 ROWS ONLY;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_top_team := 'N/A';
    END;

    -- Top player
    BEGIN
        SELECT u.full_name INTO p_top_player
        FROM player_statistics ps
        JOIN users u ON ps.player_id = u.user_id
        WHERE ps.tournament_id = p_tournament_id
        ORDER BY ps.points DESC
        FETCH FIRST 1 ROWS ONLY;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_top_player := 'N/A';
    END;

    -- Total goals/scores
    SELECT NVL(SUM(team1_score + team2_score), 0) INTO p_total_goals
    FROM match_results mr
    JOIN matches m ON mr.match_id = m.match_id
    WHERE m.tournament_id = p_tournament_id;

END GENERATE_TOURNAMENT_REPORT;
/

PROMPT 'Procedures created successfully!'
