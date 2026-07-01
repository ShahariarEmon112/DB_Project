-- =====================================================
-- GameArena - Sample Data
-- KUET-themed Data
-- =====================================================

-- =====================================================
-- ROLES
-- =====================================================
INSERT INTO roles (role_name, description) VALUES ('Admin', 'System administrator with full access');
INSERT INTO roles (role_name, description) VALUES ('Player', 'Regular player/user');
INSERT INTO roles (role_name, description) VALUES ('Team Captain', 'Team leader/captain');
INSERT INTO roles (role_name, description) VALUES ('Organizer', 'Tournament organizer');

-- =====================================================
-- USERS (Password: password123 for all)
-- =====================================================
-- Admin
INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('admin', 'admin@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin', '01700000000', 'CSE', 'ADMIN001', 1);

-- Team Captains
INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('captain_titans', 'titans@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rafiq Hasan', '01710000001', 'CSE', 'KUET2020001', 3);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('captain_warriors', 'warriors@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sakib Rahman', '01710000002', 'CSE', 'KUET2020002', 3);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('captain_mavericks', 'mavericks@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tanvir Ahmed', '01710000003', 'EEE', 'KUET2020003', 3);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('captain_legends', 'legends@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mehedi Hasan', '01710000004', 'EEE', 'KUET2020004', 3);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('captain_guardians', 'guardians@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kamal Hossain', '01710000005', 'ECE', 'KUET2020005', 3);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('captain_falcons', 'falcons@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Arif Islam', '01710000006', 'ME', 'KUET2020006', 3);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('captain_thunder', 'thunder@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Zahid Hasan', '01710000007', 'CE', 'KUET2020007', 3);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('captain_strikers', 'strikers@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nabil Khan', '01710000008', 'URP', 'KUET2020008', 3);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('captain_hunters', 'hunters@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Faruk Sheikh', '01710000009', 'BME', 'KUET2020009', 3);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('captain_gladiators', 'gladiators@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Imran Hossain', '01710000010', 'IEM', 'KUET2020010', 3);

-- Regular Players
INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('player1', 'player1@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ashraful Islam', '01720000001', 'CSE', 'KUET2021001', 2);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('player2', 'player2@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Shahriar Emon', '01720000002', 'CSE', 'KUET2021002', 2);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('player3', 'player3@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nafis Sadik', '01720000003', 'CSE', 'KUET2021003', 2);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('player4', 'player4@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tanvir Rahman', '01720000004', 'EEE', 'KUET2021004', 2);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('player5', 'player5@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rakibul Hasan', '01720000005', 'EEE', 'KUET2021005', 2);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('player6', 'player6@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fahim Ahmed', '01720000006', 'ECE', 'KUET2021006', 2);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('player7', 'player7@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Samiul Islam', '01720000007', 'ME', 'KUET2021007', 2);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('player8', 'player8@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Habibur Rahman', '01720000008', 'CE', 'KUET2021008', 2);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('player9', 'player9@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Moshiur Rahman', '01720000009', 'URP', 'KUET2021009', 2);

INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
VALUES ('player10', 'player10@kuet.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Anisur Khan', '01720000010', 'BME', 'KUET2021010', 2);

-- =====================================================
-- TEAMS
-- =====================================================
INSERT INTO teams (team_name, description, department, captain_id)
VALUES ('KUET Titans', 'The powerhouse of KUET gaming', 'CSE', 2);

INSERT INTO teams (team_name, description, department, captain_id)
VALUES ('KUET Warriors', 'Never surrender, always fight', 'CSE', 3);

INSERT INTO teams (team_name, description, department, captain_id)
VALUES ('CSE Mavericks', 'Innovation meets gaming', 'CSE', 4);

INSERT INTO teams (team_name, description, department, captain_id)
VALUES ('EEE Legends', 'Legendary in every match', 'EEE', 5);

INSERT INTO teams (team_name, description, department, captain_id)
VALUES ('ECE Guardians', 'Guardians of the arena', 'ECE', 6);

INSERT INTO teams (team_name, description, department, captain_id)
VALUES ('ME Falcons', 'Swift and deadly', 'ME', 7);

INSERT INTO teams (team_name, description, department, captain_id)
VALUES ('CE Thunder', 'Thunder strikes twice', 'CE', 8);

INSERT INTO teams (team_name, description, department, captain_id)
VALUES ('URP Strikers', 'Striking with precision', 'URP', 9);

INSERT INTO teams (team_name, description, department, captain_id)
VALUES ('BME Hunters', 'Hunting down victories', 'BME', 10);

INSERT INTO teams (team_name, description, department, captain_id)
VALUES ('IEM Gladiators', 'Gladiators of the digital age', 'IEM', 11);

-- =====================================================
-- TEAM MEMBERS
-- =====================================================
INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (1, 2, 'Captain');
INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (1, 12, 'Member');
INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (1, 13, 'Member');

INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (2, 3, 'Captain');
INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (2, 14, 'Member');
INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (2, 15, 'Member');

INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (3, 4, 'Captain');
INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (3, 16, 'Member');

INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (4, 5, 'Captain');
INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (4, 17, 'Member');

INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (5, 6, 'Captain');
INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (5, 18, 'Member');

INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (6, 7, 'Captain');
INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (6, 19, 'Member');

INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (7, 8, 'Captain');
INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (7, 20, 'Member');

INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (8, 9, 'Captain');
INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (8, 21, 'Member');

INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (9, 10, 'Captain');

INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (10, 11, 'Captain');

-- =====================================================
-- GAME CATEGORIES
-- =====================================================
INSERT INTO game_categories (category_name, description, icon) VALUES ('Valorant', '5v5 Tactical FPS by Riot Games', 'fa-crosshairs');
INSERT INTO game_categories (category_name, description, icon) VALUES ('CS2', 'Counter-Strike 2 - Competitive FPS', 'fa-gun');
INSERT INTO game_categories (category_name, description, icon) VALUES ('PUBG Mobile', 'Battle Royale Mobile Game', 'fa-mobile-alt');
INSERT INTO game_categories (category_name, description, icon) VALUES ('FIFA 24', 'Football Simulation Game', 'fa-futbol');
INSERT INTO game_categories (category_name, description, icon) VALUES ('League of Legends', 'MOBA by Riot Games', 'fa-chess');
INSERT INTO game_categories (category_name, description, icon) VALUES ('Dota 2', 'MOBA by Valve', 'fa-dice');
INSERT INTO game_categories (category_name, description, icon) VALUES ('Free Fire', 'Battle Royale Mobile Game', 'fa-fire');
INSERT INTO game_categories (category_name, description, icon) VALUES ('Rocket League', 'Car Football Game', 'fa-car');

-- =====================================================
-- TOURNAMENTS
-- =====================================================
INSERT INTO tournaments (tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by)
VALUES ('KUET Gaming League 2024', 1, 'The ultimate Valorant tournament at KUET', TO_DATE('2024-03-15', 'YYYY-MM-DD'), TO_DATE('2024-04-15', 'YYYY-MM-DD'), TO_DATE('2024-03-10', 'YYYY-MM-DD'), 16, 4, 50000, 500, 'Completed', 'KUET Auditorium', 1);

INSERT INTO tournaments (tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by)
VALUES ('KUET Battle Arena', 2, 'CS2 championship at KUET', TO_DATE('2024-04-01', 'YYYY-MM-DD'), TO_DATE('2024-04-30', 'YYYY-MM-DD'), TO_DATE('2024-03-28', 'YYYY-MM-DD'), 12, 4, 75000, 750, 'Completed', 'CSE Lab Building', 1);

INSERT INTO tournaments (tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by)
VALUES ('KUET Esports Championship 2024', 3, 'PUBG Mobile tournament', TO_DATE('2024-05-01', 'YYYY-MM-DD'), TO_DATE('2024-05-31', 'YYYY-MM-DD'), TO_DATE('2024-04-28', 'YYYY-MM-DD'), 20, 8, 100000, 1000, 'Active', 'Online + KUET Campus', 1);

INSERT INTO tournaments (tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by)
VALUES ('KUET Valorant Cup 2024', 1, 'Valorant 5v5 tournament', TO_DATE('2024-06-01', 'YYYY-MM-DD'), TO_DATE('2024-06-30', 'YYYY-MM-DD'), TO_DATE('2024-05-28', 'YYYY-MM-DD'), 16, 8, 60000, 600, 'Upcoming', 'KUET Gaming Zone', 1);

INSERT INTO tournaments (tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by)
VALUES ('KUET TechFest FIFA Tournament', 4, 'FIFA 24 1v1 tournament', TO_DATE('2024-07-01', 'YYYY-MM-DD'), TO_DATE('2024-07-05', 'YYYY-MM-DD'), TO_DATE('2024-06-28', 'YYYY-MM-DD'), 32, 16, 30000, 300, 'Upcoming', 'Student Center', 1);

INSERT INTO tournaments (tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by)
VALUES ('KUET PUBG Mobile League', 3, 'PUBG Mobile team battle', TO_DATE('2024-08-01', 'YYYY-MM-DD'), TO_DATE('2024-08-31', 'YYYY-MM-DD'), TO_DATE('2024-07-28', 'YYYY-MM-DD'), 25, 10, 150000, 1500, 'Upcoming', 'Online', 1);

INSERT INTO tournaments (tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by)
VALUES ('KUET CS2 Masters', 2, 'CS2 tournament for all departments', TO_DATE('2024-09-01', 'YYYY-MM-DD'), TO_DATE('2024-09-30', 'YYYY-MM-DD'), TO_DATE('2024-08-28', 'YYYY-MM-DD'), 16, 8, 80000, 800, 'Upcoming', 'CSE Auditorium', 1);

-- =====================================================
-- TOURNAMENT REGISTRATIONS
-- =====================================================
INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (1, 1, 2, 'Confirmed');
INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (1, 2, 3, 'Confirmed');
INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (1, 3, 4, 'Confirmed');
INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (1, 4, 5, 'Confirmed');

INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (2, 1, 2, 'Confirmed');
INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (2, 2, 3, 'Confirmed');
INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (2, 5, 6, 'Confirmed');

INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (3, 1, 2, 'Confirmed');
INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (3, 2, 3, 'Confirmed');
INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (3, 3, 4, 'Confirmed');
INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (3, 6, 7, 'Pending');
INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (3, 7, 8, 'Pending');

INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (4, 1, 2, 'Pending');
INSERT INTO tournament_registrations (tournament_id, team_id, registered_by, status) VALUES (4, 4, 5, 'Pending');

-- =====================================================
-- MATCHES
-- =====================================================
-- Tournament 1 matches (Completed)
INSERT INTO matches (tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status)
VALUES (1, 'Semifinal 1', 1, 3, TO_DATE('2024-04-10', 'YYYY-MM-DD'), '14:00', 'KUET Auditorium', 'Semifinal', 'Completed');

INSERT INTO matches (tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status)
VALUES (1, 'Semifinal 2', 2, 4, TO_DATE('2024-04-10', 'YYYY-MM-DD'), '16:00', 'KUET Auditorium', 'Semifinal', 'Completed');

INSERT INTO matches (tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status)
VALUES (1, 'Grand Final', 1, 2, TO_DATE('2024-04-15', 'YYYY-MM-DD'), '15:00', 'KUET Auditorium', 'Final', 'Completed');

-- Tournament 2 matches (Completed)
INSERT INTO matches (tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status)
VALUES (2, 'Group Match 1', 1, 5, TO_DATE('2024-04-15', 'YYYY-MM-DD'), '10:00', 'CSE Lab', 'Group Stage', 'Completed');

INSERT INTO matches (tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status)
VALUES (2, 'Group Match 2', 2, 5, TO_DATE('2024-04-20', 'YYYY-MM-DD'), '10:00', 'CSE Lab', 'Group Stage', 'Completed');

-- Tournament 3 matches (Active)
INSERT INTO matches (tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status)
VALUES (3, 'Opening Match', 1, 2, TO_DATE('2024-05-05', 'YYYY-MM-DD'), '10:00', 'Online', 'Group Stage', 'Completed');

INSERT INTO matches (tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status)
VALUES (3, 'Group Battle', 3, 6, TO_DATE('2024-05-10', 'YYYY-MM-DD'), '14:00', 'Online', 'Group Stage', 'Scheduled');

INSERT INTO matches (tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status)
VALUES (3, 'Quarterfinal', 1, 3, TO_DATE('2024-05-20', 'YYYY-MM-DD'), '15:00', 'Online', 'Quarterfinal', 'Scheduled');

-- =====================================================
-- MATCH RESULTS
-- =====================================================
INSERT INTO match_results (match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, recorded_by)
VALUES (1, 13, 8, 1, 12, 45, 1);

INSERT INTO match_results (match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, recorded_by)
VALUES (2, 11, 13, 2, 14, 52, 1);

INSERT INTO match_results (match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, recorded_by)
VALUES (3, 13, 10, 1, 13, 48, 1);

INSERT INTO match_results (match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, recorded_by)
VALUES (4, 16, 12, 1, 12, 55, 1);

INSERT INTO match_results (match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, recorded_by)
VALUES (5, 10, 16, 5, 18, 50, 1);

INSERT INTO match_results (match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, recorded_by)
VALUES (6, 13, 11, 1, 2, 42, 1);

-- =====================================================
-- PLAYER STATISTICS
-- =====================================================
INSERT INTO player_statistics (player_id, tournament_id, matches_played, wins, losses, kills, deaths, assists, mvp_count, points)
VALUES (12, 1, 3, 3, 0, 45, 20, 15, 2, 300);

INSERT INTO player_statistics (player_id, tournament_id, matches_played, wins, losses, kills, deaths, assists, mvp_count, points)
VALUES (13, 1, 3, 3, 0, 38, 22, 18, 1, 280);

INSERT INTO player_statistics (player_id, tournament_id, matches_played, wins, losses, kills, deaths, assists, mvp_count, points)
VALUES (14, 1, 2, 1, 1, 30, 25, 12, 0, 200);

INSERT INTO player_statistics (player_id, tournament_id, matches_played, wins, losses, kills, deaths, assists, mvp_count, points)
VALUES (15, 1, 2, 1, 1, 28, 28, 10, 0, 180);

INSERT INTO player_statistics (player_id, tournament_id, matches_played, wins, losses, kills, deaths, assists, mvp_count, points)
VALUES (2, 3, 1, 1, 0, 18, 8, 5, 1, 150);

INSERT INTO player_statistics (player_id, tournament_id, matches_played, wins, losses, kills, deaths, assists, mvp_count, points)
VALUES (16, 3, 0, 0, 0, 0, 0, 0, 0, 0);

-- =====================================================
-- LEADERBOARD
-- =====================================================
INSERT INTO leaderboard (tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played)
VALUES (1, 1, 1, 300, 3, 0, 0, 3);

INSERT INTO leaderboard (tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played)
VALUES (1, 2, 2, 200, 2, 1, 0, 3);

INSERT INTO leaderboard (tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played)
VALUES (1, 3, 3, 100, 1, 1, 0, 2);

INSERT INTO leaderboard (tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played)
VALUES (1, 4, 4, 50, 0, 1, 0, 1);

INSERT INTO leaderboard (tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played)
VALUES (2, 1, 1, 200, 2, 0, 0, 2);

INSERT INTO leaderboard (tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played)
VALUES (2, 5, 2, 150, 1, 1, 0, 2);

INSERT INTO leaderboard (tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played)
VALUES (2, 2, 3, 100, 1, 1, 0, 2);

INSERT INTO leaderboard (tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played)
VALUES (3, 1, 1, 100, 1, 0, 0, 1);

INSERT INTO leaderboard (tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played)
VALUES (3, 2, 2, 0, 0, 1, 0, 1);

COMMIT;

PROMPT 'Sample data inserted successfully!'
