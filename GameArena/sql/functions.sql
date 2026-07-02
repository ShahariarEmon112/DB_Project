-- =====================================================
-- GameArena - PL/SQL Functions
-- =====================================================

-- =====================================================
-- FUNCTION 1: GET_TEAM_WIN_RATE
-- Returns win percentage for a team
-- =====================================================
CREATE OR REPLACE FUNCTION GET_TEAM_WIN_RATE(
    p_team_id IN NUMBER
) RETURN NUMBER AS
    v_total  NUMBER;
    v_wins   NUMBER;
    v_rate   NUMBER;
BEGIN
    SELECT COUNT(*), NVL(SUM(CASE WHEN winner_id = p_team_id THEN 1 ELSE 0 END), 0)
    INTO v_total, v_wins
    FROM match_results mr
    JOIN matches m ON mr.match_id = m.match_id
    WHERE m.team1_id = p_team_id OR m.team2_id = p_team_id;

    IF v_total = 0 THEN
        RETURN 0;
    END IF;

    v_rate := ROUND((v_wins / v_total) * 100, 2);
    RETURN v_rate;

EXCEPTION
    WHEN OTHERS THEN
        RETURN 0;
END GET_TEAM_WIN_RATE;
/

-- =====================================================
-- FUNCTION 2: GET_PLAYER_MVP_COUNT
-- Returns total MVP awards for a player
-- =====================================================
CREATE OR REPLACE FUNCTION GET_PLAYER_MVP_COUNT(
    p_player_id IN NUMBER
) RETURN NUMBER AS
    v_count NUMBER;
BEGIN
    SELECT NVL(SUM(mvp_count), 0) INTO v_count
    FROM player_statistics
    WHERE player_id = p_player_id;

    RETURN v_count;

EXCEPTION
    WHEN OTHERS THEN
        RETURN 0;
END GET_PLAYER_MVP_COUNT;
/

-- =====================================================
-- FUNCTION 3: CALCULATE_POINTS
-- Calculate points based on kills, deaths, assists
-- =====================================================
CREATE OR REPLACE FUNCTION CALCULATE_POINTS(
    p_kills   IN NUMBER,
    p_deaths  IN NUMBER,
    p_assists IN NUMBER
) RETURN NUMBER AS
    v_points NUMBER;
BEGIN
    -- Points = (kills * 10) + (assists * 5) - (deaths * 2)
    -- Minimum 0 points
    v_points := (NVL(p_kills, 0) * 10) + (NVL(p_assists, 0) * 5) - (NVL(p_deaths, 0) * 2);

    IF v_points < 0 THEN
        v_points := 0;
    END IF;

    RETURN v_points;

EXCEPTION
    WHEN OTHERS THEN
        RETURN 0;
END CALCULATE_POINTS;
/

-- =====================================================
-- FUNCTION 4: GET_TOTAL_MATCHES
-- Returns total matches for a team
-- =====================================================
CREATE OR REPLACE FUNCTION GET_TOTAL_MATCHES(
    p_team_id IN NUMBER
) RETURN NUMBER AS
    v_count NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_count
    FROM matches
    WHERE (team1_id = p_team_id OR team2_id = p_team_id)
      AND status = 'Completed';

    RETURN v_count;

EXCEPTION
    WHEN OTHERS THEN
        RETURN 0;
END GET_TOTAL_MATCHES;
/

-- =====================================================
-- FUNCTION 5: GET_TEAM_RANK
-- Returns current rank for a team in a tournament
-- =====================================================
CREATE OR REPLACE FUNCTION GET_TEAM_RANK(
    p_tournament_id IN NUMBER,
    p_team_id       IN NUMBER
) RETURN NUMBER AS
    v_rank NUMBER;
BEGIN
    SELECT rank_position INTO v_rank
    FROM leaderboard
    WHERE tournament_id = p_tournament_id
      AND team_id = p_team_id;

    RETURN v_rank;

EXCEPTION
    WHEN NO_DATA_FOUND THEN
        RETURN 0;
    WHEN OTHERS THEN
        RETURN 0;
END GET_TEAM_RANK;
/

