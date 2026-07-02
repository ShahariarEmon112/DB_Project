-- =====================================================
-- GameArena - Oracle Views
-- =====================================================

-- =====================================================
-- VIEW 1: ACTIVE_TOURNAMENTS
-- Shows all active and upcoming tournaments
-- =====================================================
CREATE OR REPLACE VIEW ACTIVE_TOURNAMENTS AS
SELECT
    t.tournament_id,
    t.tournament_name,
    gc.category_name,
    gc.icon AS category_icon,
    t.description,
    t.start_date,
    t.end_date,
    t.registration_deadline,
    t.max_teams,
    t.min_teams,
    t.prize_pool,
    t.entry_fee,
    t.status,
    t.venue,
    u.full_name AS organizer_name,
    (SELECT COUNT(*) FROM tournament_registrations tr WHERE tr.tournament_id = t.tournament_id AND tr.status = 'Confirmed') AS registered_teams,
    (SELECT COUNT(*) FROM matches m WHERE m.tournament_id = t.tournament_id) AS total_matches,
    t.created_at
FROM tournaments t
JOIN game_categories gc ON t.category_id = gc.category_id
LEFT JOIN users u ON t.created_by = u.user_id
WHERE t.status IN ('Active', 'Upcoming')
ORDER BY t.start_date;

-- =====================================================
-- VIEW 2: TEAM_RANKINGS
-- Shows team rankings with statistics
-- =====================================================
CREATE OR REPLACE VIEW TEAM_RANKINGS AS
SELECT
    t.team_id,
    t.team_name,
    t.department,
    u.full_name AS captain_name,
    (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.team_id) AS member_count,
    (SELECT COUNT(*) FROM matches m WHERE (m.team1_id = t.team_id OR m.team2_id = t.team_id) AND m.status = 'Completed') AS total_matches,
    (SELECT COUNT(*) FROM match_results mr
     JOIN matches m ON mr.match_id = m.match_id
     WHERE (m.team1_id = t.team_id OR m.team2_id = t.team_id) AND mr.winner_id = t.team_id) AS total_wins,
    (SELECT COUNT(*) FROM match_results mr
     JOIN matches m ON mr.match_id = m.match_id
     WHERE (m.team1_id = t.team_id OR m.team2_id = t.team_id) AND mr.winner_id != t.team_id AND m.status = 'Completed') AS total_losses,
    NVL(GET_TEAM_WIN_RATE(t.team_id), 0) AS win_rate,
    t.created_at
FROM teams t
LEFT JOIN users u ON t.captain_id = u.user_id
WHERE t.is_active = 1
ORDER BY total_wins DESC, total_matches DESC;

-- =====================================================
-- VIEW 3: PLAYER_STATISTICS_VIEW
-- Shows comprehensive player statistics
-- =====================================================
CREATE OR REPLACE VIEW PLAYER_STATISTICS_VIEW AS
SELECT
    u.user_id,
    u.username,
    u.full_name,
    u.department,
    u.student_id,
    NVL(SUM(ps.matches_played), 0) AS total_matches,
    NVL(SUM(ps.wins), 0) AS total_wins,
    NVL(SUM(ps.losses), 0) AS total_losses,
    NVL(SUM(ps.kills), 0) AS total_kills,
    NVL(SUM(ps.deaths), 0) AS total_deaths,
    NVL(SUM(ps.assists), 0) AS total_assists,
    NVL(SUM(ps.mvp_count), 0) AS total_mvps,
    NVL(SUM(ps.points), 0) AS total_points,
    CASE WHEN NVL(SUM(ps.deaths), 0) = 0 THEN 0
         ELSE ROUND(SUM(ps.kills) / SUM(ps.deaths), 2) END AS kd_ratio,
    GET_PLAYER_MVP_COUNT(u.user_id) AS career_mvps
FROM users u
LEFT JOIN player_statistics ps ON u.user_id = ps.player_id
WHERE u.role_id != 1
GROUP BY u.user_id, u.username, u.full_name, u.department, u.student_id
ORDER BY total_points DESC;

-- =====================================================
-- VIEW 4: UPCOMING_MATCHES
-- Shows all upcoming scheduled matches
-- =====================================================
CREATE OR REPLACE VIEW UPCOMING_MATCHES AS
SELECT
    m.match_id,
    m.match_name,
    m.match_date,
    m.match_time,
    m.venue,
    m.round,
    m.status,
    t1.team_name AS team1_name,
    t2.team_name AS team2_name,
    t.tournament_name,
    gc.category_name,
    gc.icon AS category_icon,
    m.created_at
FROM matches m
JOIN teams t1 ON m.team1_id = t1.team_id
JOIN teams t2 ON m.team2_id = t2.team_id
JOIN tournaments t ON m.tournament_id = t.tournament_id
JOIN game_categories gc ON t.category_id = gc.category_id
WHERE m.status IN ('Scheduled', 'Live')
ORDER BY m.match_date ASC, m.match_time ASC;

-- =====================================================
-- VIEW 5: MATCH_DETAILS
-- Detailed match information with results
-- =====================================================
CREATE OR REPLACE VIEW MATCH_DETAILS AS
SELECT
    m.match_id,
    m.match_name,
    m.match_date,
    m.match_time,
    m.venue,
    m.round,
    m.status,
    t1.team_name AS team1_name,
    t2.team_name AS team2_name,
    t.tournament_name,
    gc.category_name,
    mr.team1_score,
    mr.team2_score,
    tw.team_name AS winner_name,
    mv.full_name AS mvp_name,
    mr.duration_mins,
    mr.notes,
    rec.full_name AS recorded_by_name,
    mr.recorded_at
FROM matches m
JOIN teams t1 ON m.team1_id = t1.team_id
JOIN teams t2 ON m.team2_id = t2.team_id
JOIN tournaments t ON m.tournament_id = t.tournament_id
JOIN game_categories gc ON t.category_id = gc.category_id
LEFT JOIN match_results mr ON m.match_id = mr.match_id
LEFT JOIN teams tw ON mr.winner_id = tw.team_id
LEFT JOIN users mv ON mr.mvp_player_id = mv.user_id
LEFT JOIN users rec ON mr.recorded_by = rec.user_id;

-- =====================================================
-- VIEW 6: TOURNAMENT_STATS
-- Tournament statistics overview
-- =====================================================
CREATE OR REPLACE VIEW TOURNAMENT_STATS AS
SELECT
    t.tournament_id,
    t.tournament_name,
    gc.category_name,
    t.status,
    t.start_date,
    t.end_date,
    t.prize_pool,
    (SELECT COUNT(*) FROM tournament_registrations tr WHERE tr.tournament_id = t.tournament_id AND tr.status = 'Confirmed') AS registered_teams,
    (SELECT COUNT(*) FROM matches m WHERE m.tournament_id = t.tournament_id) AS total_matches,
    (SELECT COUNT(*) FROM matches m WHERE m.tournament_id = t.tournament_id AND m.status = 'Completed') AS completed_matches,
    (SELECT NVL(SUM(mr.team1_score + mr.team2_score), 0) FROM match_results mr
     JOIN matches m ON mr.match_id = m.match_id WHERE m.tournament_id = t.tournament_id) AS total_scores,
    u.full_name AS organizer_name
FROM tournaments t
JOIN game_categories gc ON t.category_id = gc.category_id
LEFT JOIN users u ON t.created_by = u.user_id;

