-- =====================================================
-- GameArena - Sample Data (Manual PKs)
-- Password: password123 for all users
-- =====================================================

-- ROLES
INSERT INTO roles (role_id, role_name, description) VALUES (1, 'Admin', 'System administrator');
INSERT INTO roles (role_id, role_name, description) VALUES (2, 'Player', 'Regular player');
INSERT INTO roles (role_id, role_name, description) VALUES (3, 'Team Captain', 'Team leader');
INSERT INTO roles (role_id, role_name, description) VALUES (4, 'Organizer', 'Tournament organizer');

-- USERS (1=Admin, 2=Player, 3=Captain)
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (1, 'admin', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'System Admin', '01700000000', 'CSE', 'ADMIN001', 1);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (2, 'captain_titans', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Rafiq Hasan', '01710000001', 'CSE', 'KUET2020001', 3);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (3, 'captain_warriors', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Sakib Rahman', '01710000002', 'CSE', 'KUET2020002', 3);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (4, 'captain_mavericks', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Tanvir Ahmed', '01710000003', 'EEE', 'KUET2020003', 3);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (5, 'captain_legends', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Mehedi Hasan', '01710000004', 'EEE', 'KUET2020004', 3);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (6, 'captain_guardians', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Kamal Hossain', '01710000005', 'ECE', 'KUET2020005', 3);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (7, 'captain_falcons', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Arif Islam', '01710000006', 'ME', 'KUET2020006', 3);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (8, 'captain_thunder', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Zahid Hasan', '01710000007', 'CE', 'KUET2020007', 3);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (9, 'captain_strikers', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Nabil Khan', '01710000008', 'URP', 'KUET2020008', 3);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (10, 'captain_hunters', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Faruk Sheikh', '01710000009', 'BME', 'KUET2020009', 3);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (11, 'captain_gladiators', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Imran Hossain', '01710000010', 'IEM', 'KUET2020010', 3);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (12, 'player1', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Ashraful Islam', '01720000001', 'CSE', 'KUET2021001', 2);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (13, 'player2', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Shahriar Emon', '01720000002', 'CSE', 'KUET2021002', 2);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (14, 'player3', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Nafis Sadik', '01720000003', 'CSE', 'KUET2021003', 2);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (15, 'player4', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Tanvir Rahman', '01720000004', 'EEE', 'KUET2021004', 2);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (16, 'player5', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Rakibul Hasan', '01720000005', 'EEE', 'KUET2021005', 2);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (17, 'player6', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Fahim Ahmed', '01720000006', 'ECE', 'KUET2021006', 2);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (18, 'player7', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Samiul Islam', '01720000007', 'ME', 'KUET2021007', 2);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (19, 'player8', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Habibur Rahman', '01720000008', 'CE', 'KUET2021008', 2);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (20, 'player9', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Moshiur Rahman', '01720000009', 'URP', 'KUET2021009', 2);
INSERT INTO users (user_id, username, password, full_name, phone, department, student_id, role_id) VALUES (21, 'player10', '$2y$10$fwIVD/VXAAs0aIll2vyGdOjrTxj0wM5YzFYHBZzh8i/s97RS9xp2W', 'Anisur Khan', '01720000010', 'BME', 'KUET2021010', 2);

-- TEAMS
INSERT INTO teams (team_id, team_name, description, department, captain_id) VALUES (1, 'KUET Titans', 'The powerhouse of KUET gaming', 'CSE', 2);
INSERT INTO teams (team_id, team_name, description, department, captain_id) VALUES (2, 'KUET Warriors', 'Never surrender, always fight', 'CSE', 3);
INSERT INTO teams (team_id, team_name, description, department, captain_id) VALUES (3, 'CSE Mavericks', 'Innovation meets gaming', 'CSE', 4);
INSERT INTO teams (team_id, team_name, description, department, captain_id) VALUES (4, 'EEE Legends', 'Legendary in every match', 'EEE', 5);
INSERT INTO teams (team_id, team_name, description, department, captain_id) VALUES (5, 'ECE Guardians', 'Guardians of the arena', 'ECE', 6);
INSERT INTO teams (team_id, team_name, description, department, captain_id) VALUES (6, 'ME Falcons', 'Swift and deadly', 'ME', 7);
INSERT INTO teams (team_id, team_name, description, department, captain_id) VALUES (7, 'CE Thunder', 'Thunder strikes twice', 'CE', 8);
INSERT INTO teams (team_id, team_name, description, department, captain_id) VALUES (8, 'URP Strikers', 'Striking with precision', 'URP', 9);
INSERT INTO teams (team_id, team_name, description, department, captain_id) VALUES (9, 'BME Hunters', 'Hunting down victories', 'BME', 10);
INSERT INTO teams (team_id, team_name, description, department, captain_id) VALUES (10, 'IEM Gladiators', 'Gladiators of the digital age', 'IEM', 11);

-- TEAM MEMBERS
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (1, 1, 2, 'Captain');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (2, 1, 12, 'Member');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (3, 1, 13, 'Member');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (4, 2, 3, 'Captain');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (5, 2, 14, 'Member');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (6, 2, 15, 'Member');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (7, 3, 4, 'Captain');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (8, 3, 16, 'Member');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (9, 4, 5, 'Captain');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (10, 4, 17, 'Member');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (11, 5, 6, 'Captain');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (12, 5, 18, 'Member');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (13, 6, 7, 'Captain');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (14, 6, 19, 'Member');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (15, 7, 8, 'Captain');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (16, 7, 20, 'Member');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (17, 8, 9, 'Captain');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (18, 8, 21, 'Member');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (19, 9, 10, 'Captain');
INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (20, 10, 11, 'Captain');

-- GAME CATEGORIES
INSERT INTO game_categories (category_id, category_name, description, icon) VALUES (1, 'Valorant', '5v5 Tactical FPS', 'fa-crosshairs');
INSERT INTO game_categories (category_id, category_name, description, icon) VALUES (2, 'CS2', 'Counter-Strike 2', 'fa-gun');
INSERT INTO game_categories (category_id, category_name, description, icon) VALUES (3, 'PUBG Mobile', 'Battle Royale Mobile', 'fa-mobile-alt');
INSERT INTO game_categories (category_id, category_name, description, icon) VALUES (4, 'FIFA 24', 'Football Simulation', 'fa-futbol');
INSERT INTO game_categories (category_id, category_name, description, icon) VALUES (5, 'League of Legends', 'MOBA by Riot Games', 'fa-chess');
INSERT INTO game_categories (category_id, category_name, description, icon) VALUES (6, 'Dota 2', 'MOBA by Valve', 'fa-dice');
INSERT INTO game_categories (category_id, category_name, description, icon) VALUES (7, 'Free Fire', 'Battle Royale Mobile', 'fa-fire');
INSERT INTO game_categories (category_id, category_name, description, icon) VALUES (8, 'Rocket League', 'Car Football', 'fa-car');

-- TOURNAMENTS
INSERT INTO tournaments (tournament_id, tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by) VALUES (1, 'KUET Gaming League 2024', 1, 'The ultimate Valorant tournament', TO_DATE('2024-03-15', 'YYYY-MM-DD'), TO_DATE('2024-04-15', 'YYYY-MM-DD'), TO_DATE('2024-03-10', 'YYYY-MM-DD'), 16, 4, 50000, 500, 'Completed', 'KUET Auditorium', 1);
INSERT INTO tournaments (tournament_id, tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by) VALUES (2, 'KUET Battle Arena', 2, 'CS2 championship', TO_DATE('2024-04-01', 'YYYY-MM-DD'), TO_DATE('2024-04-30', 'YYYY-MM-DD'), TO_DATE('2024-03-28', 'YYYY-MM-DD'), 12, 4, 75000, 750, 'Completed', 'CSE Lab Building', 1);
INSERT INTO tournaments (tournament_id, tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by) VALUES (3, 'KUET Esports Championship 2024', 3, 'PUBG Mobile tournament', TO_DATE('2024-05-01', 'YYYY-MM-DD'), TO_DATE('2024-05-31', 'YYYY-MM-DD'), TO_DATE('2024-04-28', 'YYYY-MM-DD'), 20, 8, 100000, 1000, 'Active', 'Online + KUET Campus', 1);
INSERT INTO tournaments (tournament_id, tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by) VALUES (4, 'KUET TechFest FIFA Tournament', 4, 'FIFA 24 1v1 tournament', TO_DATE('2024-07-01', 'YYYY-MM-DD'), TO_DATE('2024-07-05', 'YYYY-MM-DD'), TO_DATE('2024-06-28', 'YYYY-MM-DD'), 32, 16, 30000, 300, 'Upcoming', 'Student Center', 1);
INSERT INTO tournaments (tournament_id, tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by) VALUES (5, 'KUET PUBG Mobile League', 3, 'PUBG Mobile team battle', TO_DATE('2024-08-01', 'YYYY-MM-DD'), TO_DATE('2024-08-31', 'YYYY-MM-DD'), TO_DATE('2024-07-28', 'YYYY-MM-DD'), 25, 10, 150000, 1500, 'Upcoming', 'Online', 1);
INSERT INTO tournaments (tournament_id, tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by) VALUES (6, 'KUET CS2 Masters', 2, 'CS2 tournament for all departments', TO_DATE('2024-09-01', 'YYYY-MM-DD'), TO_DATE('2024-09-30', 'YYYY-MM-DD'), TO_DATE('2024-08-28', 'YYYY-MM-DD'), 16, 8, 80000, 800, 'Upcoming', 'CSE Auditorium', 1);

-- TOURNAMENT REGISTRATIONS
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (1, 1, 1, 2, 'Confirmed');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (2, 1, 2, 3, 'Confirmed');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (3, 1, 3, 4, 'Confirmed');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (4, 1, 4, 5, 'Confirmed');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (5, 2, 1, 2, 'Confirmed');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (6, 2, 2, 3, 'Confirmed');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (7, 2, 5, 6, 'Confirmed');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (8, 3, 1, 2, 'Confirmed');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (9, 3, 2, 3, 'Confirmed');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (10, 3, 3, 4, 'Confirmed');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (11, 3, 6, 7, 'Pending');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (12, 3, 7, 8, 'Pending');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (13, 4, 1, 2, 'Pending');
INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, status) VALUES (14, 4, 4, 5, 'Pending');

-- MATCHES
INSERT INTO matches (match_id, tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status) VALUES (1, 1, 'Semifinal 1', 1, 3, TO_DATE('2024-04-10', 'YYYY-MM-DD'), '14:00', 'KUET Auditorium', 'Semifinal', 'Completed');
INSERT INTO matches (match_id, tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status) VALUES (2, 1, 'Semifinal 2', 2, 4, TO_DATE('2024-04-10', 'YYYY-MM-DD'), '16:00', 'KUET Auditorium', 'Semifinal', 'Completed');
INSERT INTO matches (match_id, tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status) VALUES (3, 1, 'Grand Final', 1, 2, TO_DATE('2024-04-15', 'YYYY-MM-DD'), '15:00', 'KUET Auditorium', 'Final', 'Completed');
INSERT INTO matches (match_id, tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status) VALUES (4, 2, 'Group Match 1', 1, 5, TO_DATE('2024-04-15', 'YYYY-MM-DD'), '10:00', 'CSE Lab', 'Group Stage', 'Completed');
INSERT INTO matches (match_id, tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status) VALUES (5, 2, 'Group Match 2', 2, 5, TO_DATE('2024-04-20', 'YYYY-MM-DD'), '10:00', 'CSE Lab', 'Group Stage', 'Completed');
INSERT INTO matches (match_id, tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status) VALUES (6, 3, 'Opening Match', 1, 2, TO_DATE('2024-05-05', 'YYYY-MM-DD'), '10:00', 'Online', 'Group Stage', 'Completed');
INSERT INTO matches (match_id, tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status) VALUES (7, 3, 'Group Battle', 3, 6, TO_DATE('2024-05-10', 'YYYY-MM-DD'), '14:00', 'Online', 'Group Stage', 'Scheduled');
INSERT INTO matches (match_id, tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status) VALUES (8, 3, 'Quarterfinal', 1, 3, TO_DATE('2024-05-20', 'YYYY-MM-DD'), '15:00', 'Online', 'Quarterfinal', 'Scheduled');

-- MATCH RESULTS
INSERT INTO match_results (result_id, match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, recorded_by) VALUES (1, 1, 13, 8, 1, 12, 45, 1);
INSERT INTO match_results (result_id, match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, recorded_by) VALUES (2, 2, 11, 13, 2, 14, 52, 1);
INSERT INTO match_results (result_id, match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, recorded_by) VALUES (3, 3, 13, 10, 1, 13, 48, 1);
INSERT INTO match_results (result_id, match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, recorded_by) VALUES (4, 4, 16, 12, 1, 12, 55, 1);
INSERT INTO match_results (result_id, match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, recorded_by) VALUES (5, 5, 10, 16, 5, 18, 50, 1);
INSERT INTO match_results (result_id, match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, recorded_by) VALUES (6, 6, 13, 11, 1, 2, 42, 1);

-- PLAYER STATISTICS
INSERT INTO player_statistics (stat_id, player_id, tournament_id, matches_played, wins, losses, kills, deaths, assists, mvp_count, points) VALUES (1, 12, 1, 3, 3, 0, 45, 20, 15, 2, 300);
INSERT INTO player_statistics (stat_id, player_id, tournament_id, matches_played, wins, losses, kills, deaths, assists, mvp_count, points) VALUES (2, 13, 1, 3, 3, 0, 38, 22, 18, 1, 280);
INSERT INTO player_statistics (stat_id, player_id, tournament_id, matches_played, wins, losses, kills, deaths, assists, mvp_count, points) VALUES (3, 14, 1, 2, 1, 1, 30, 25, 12, 0, 200);
INSERT INTO player_statistics (stat_id, player_id, tournament_id, matches_played, wins, losses, kills, deaths, assists, mvp_count, points) VALUES (4, 15, 1, 2, 1, 1, 28, 28, 10, 0, 180);
INSERT INTO player_statistics (stat_id, player_id, tournament_id, matches_played, wins, losses, kills, deaths, assists, mvp_count, points) VALUES (5, 2, 3, 1, 1, 0, 18, 8, 5, 1, 150);
INSERT INTO player_statistics (stat_id, player_id, tournament_id, matches_played, wins, losses, kills, deaths, assists, mvp_count, points) VALUES (6, 16, 3, 0, 0, 0, 0, 0, 0, 0, 0);

-- LEADERBOARD
INSERT INTO leaderboard (leaderboard_id, tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played) VALUES (1, 1, 1, 1, 300, 3, 0, 0, 3);
INSERT INTO leaderboard (leaderboard_id, tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played) VALUES (2, 1, 2, 2, 200, 2, 1, 0, 3);
INSERT INTO leaderboard (leaderboard_id, tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played) VALUES (3, 1, 3, 3, 100, 1, 1, 0, 2);
INSERT INTO leaderboard (leaderboard_id, tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played) VALUES (4, 1, 4, 4, 50, 0, 1, 0, 1);
INSERT INTO leaderboard (leaderboard_id, tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played) VALUES (5, 2, 1, 1, 200, 2, 0, 0, 2);
INSERT INTO leaderboard (leaderboard_id, tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played) VALUES (6, 2, 5, 2, 150, 1, 1, 0, 2);
INSERT INTO leaderboard (leaderboard_id, tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played) VALUES (7, 2, 2, 3, 100, 1, 1, 0, 2);
INSERT INTO leaderboard (leaderboard_id, tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played) VALUES (8, 3, 1, 1, 100, 1, 0, 0, 1);
INSERT INTO leaderboard (leaderboard_id, tournament_id, team_id, rank_position, points, wins, losses, draws, matches_played) VALUES (9, 3, 2, 2, 0, 0, 1, 0, 1);

COMMIT;
