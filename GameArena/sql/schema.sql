-- =====================================================
-- GameArena - Gaming Tournament Management System
-- Oracle Database Schema
-- Khulna University of Engineering & Technology (KUET)
-- =====================================================

-- Drop existing tables (in reverse dependency order)
BEGIN
    EXECUTE IMMEDIATE 'DROP TABLE audit_log CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE leaderboard CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE player_statistics CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE match_results CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE matches CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE tournament_registrations CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE tournaments CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE game_categories CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE team_members CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE teams CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE roles CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE users CASCADE CONSTRAINTS';
EXCEPTION
    WHEN OTHERS THEN NULL;
END;
/

-- =====================================================
-- ROLES TABLE
-- =====================================================
CREATE TABLE roles (
    role_id     NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    role_name   VARCHAR2(50) NOT NULL UNIQUE,
    description VARCHAR2(200),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- USERS TABLE
-- =====================================================
CREATE TABLE users (
    user_id      NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    username     VARCHAR2(50) NOT NULL UNIQUE,
    email        VARCHAR2(100) NOT NULL UNIQUE,
    password     VARCHAR2(255) NOT NULL,
    full_name    VARCHAR2(100) NOT NULL,
    phone        VARCHAR2(20),
    department   VARCHAR2(100),
    student_id   VARCHAR2(20),
    role_id      NUMBER DEFAULT 2,
    avatar       VARCHAR2(255) DEFAULT 'default.png',
    is_active    NUMBER(1) DEFAULT 1,
    last_login   TIMESTAMP,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles(role_id),
    CONSTRAINT chk_email CHECK (email LIKE '%@%'),
    CONSTRAINT chk_active CHECK (is_active IN (0, 1))
);

-- =====================================================
-- TEAMS TABLE
-- =====================================================
CREATE TABLE teams (
    team_id      NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    team_name    VARCHAR2(100) NOT NULL UNIQUE,
    description  VARCHAR2(500),
    department   VARCHAR2(100),
    captain_id   NUMBER,
    logo         VARCHAR2(255) DEFAULT 'default_team.png',
    is_active    NUMBER(1) DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_team_captain FOREIGN KEY (captain_id) REFERENCES users(user_id),
    CONSTRAINT chk_team_active CHECK (is_active IN (0, 1))
);

-- =====================================================
-- TEAM MEMBERS TABLE
-- =====================================================
CREATE TABLE team_members (
    member_id    NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    team_id      NUMBER NOT NULL,
    user_id      NUMBER NOT NULL,
    role_in_team VARCHAR2(50) DEFAULT 'Member',
    joined_date  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE CASCADE,
    CONSTRAINT fk_tm_user FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT uk_team_member UNIQUE (team_id, user_id),
    CONSTRAINT chk_team_role CHECK (role_in_team IN ('Captain', 'Member', 'Substitute'))
);

-- =====================================================
-- GAME CATEGORIES TABLE
-- =====================================================
CREATE TABLE game_categories (
    category_id   NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    category_name VARCHAR2(100) NOT NULL UNIQUE,
    description   VARCHAR2(500),
    icon          VARCHAR2(255),
    is_active     NUMBER(1) DEFAULT 1,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_gc_active CHECK (is_active IN (0, 1))
);

-- =====================================================
-- TOURNAMENTS TABLE
-- =====================================================
CREATE TABLE tournaments (
    tournament_id   NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tournament_name VARCHAR2(200) NOT NULL,
    category_id     NUMBER NOT NULL,
    description     VARCHAR2(1000),
    start_date      DATE NOT NULL,
    end_date        DATE,
    registration_deadline DATE,
    max_teams       NUMBER DEFAULT 16,
    min_teams       NUMBER DEFAULT 4,
    prize_pool      NUMBER(12,2) DEFAULT 0,
    entry_fee       NUMBER(8,2) DEFAULT 0,
    status          VARCHAR2(20) DEFAULT 'Upcoming',
    venue           VARCHAR2(200),
    rules           VARCHAR2(2000),
    created_by      NUMBER,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tourn_category FOREIGN KEY (category_id) REFERENCES game_categories(category_id),
    CONSTRAINT fk_tourn_creator FOREIGN KEY (created_by) REFERENCES users(user_id),
    CONSTRAINT chk_tourn_status CHECK (status IN ('Upcoming', 'Active', 'Completed', 'Cancelled')),
    CONSTRAINT chk_max_teams CHECK (max_teams > 0),
    CONSTRAINT chk_dates CHECK (end_date >= start_date)
);

-- =====================================================
-- TOURNAMENT REGISTRATIONS TABLE
-- =====================================================
CREATE TABLE tournament_registrations (
    registration_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tournament_id   NUMBER NOT NULL,
    team_id         NUMBER NOT NULL,
    registered_by   NUMBER,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status          VARCHAR2(20) DEFAULT 'Pending',
    CONSTRAINT fk_reg_tournament FOREIGN KEY (tournament_id) REFERENCES tournaments(tournament_id) ON DELETE CASCADE,
    CONSTRAINT fk_reg_team FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE CASCADE,
    CONSTRAINT fk_reg_user FOREIGN KEY (registered_by) REFERENCES users(user_id),
    CONSTRAINT uk_tourn_team UNIQUE (tournament_id, team_id),
    CONSTRAINT chk_reg_status CHECK (status IN ('Pending', 'Confirmed', 'Rejected', 'Withdrawn'))
);

-- =====================================================
-- MATCHES TABLE
-- =====================================================
CREATE TABLE matches (
    match_id        NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tournament_id   NUMBER NOT NULL,
    match_name      VARCHAR2(200),
    team1_id        NUMBER NOT NULL,
    team2_id        NUMBER NOT NULL,
    match_date      DATE NOT NULL,
    match_time      VARCHAR2(10),
    venue           VARCHAR2(200),
    round           VARCHAR2(50),
    status          VARCHAR2(20) DEFAULT 'Scheduled',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_match_tournament FOREIGN KEY (tournament_id) REFERENCES tournaments(tournament_id) ON DELETE CASCADE,
    CONSTRAINT fk_match_team1 FOREIGN KEY (team1_id) REFERENCES teams(team_id),
    CONSTRAINT fk_match_team2 FOREIGN KEY (team2_id) REFERENCES teams(team_id),
    CONSTRAINT chk_match_status CHECK (status IN ('Scheduled', 'Live', 'Completed', 'Cancelled', 'Postponed')),
    CONSTRAINT chk_different_teams CHECK (team1_id != team2_id)
);

-- =====================================================
-- MATCH RESULTS TABLE
-- =====================================================
CREATE TABLE match_results (
    result_id      NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    match_id       NUMBER NOT NULL UNIQUE,
    team1_score    NUMBER DEFAULT 0,
    team2_score    NUMBER DEFAULT 0,
    winner_id      NUMBER,
    mvp_player_id  NUMBER,
    duration_mins  NUMBER,
    notes          VARCHAR2(500),
    recorded_by    NUMBER,
    recorded_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_result_match FOREIGN KEY (match_id) REFERENCES matches(match_id) ON DELETE CASCADE,
    CONSTRAINT fk_result_winner FOREIGN KEY (winner_id) REFERENCES teams(team_id),
    CONSTRAINT fk_result_mvp FOREIGN KEY (mvp_player_id) REFERENCES users(user_id),
    CONSTRAINT fk_result_recorder FOREIGN KEY (recorded_by) REFERENCES users(user_id),
    CONSTRAINT chk_scores CHECK (team1_score >= 0 AND team2_score >= 0)
);

-- =====================================================
-- PLAYER STATISTICS TABLE
-- =====================================================
CREATE TABLE player_statistics (
    stat_id        NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    player_id      NUMBER NOT NULL,
    tournament_id  NUMBER NOT NULL,
    matches_played NUMBER DEFAULT 0,
    wins           NUMBER DEFAULT 0,
    losses         NUMBER DEFAULT 0,
    kills          NUMBER DEFAULT 0,
    deaths         NUMBER DEFAULT 0,
    assists        NUMBER DEFAULT 0,
    mvp_count      NUMBER DEFAULT 0,
    points         NUMBER DEFAULT 0,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ps_player FOREIGN KEY (player_id) REFERENCES users(user_id),
    CONSTRAINT fk_ps_tournament FOREIGN KEY (tournament_id) REFERENCES tournaments(tournament_id) ON DELETE CASCADE,
    CONSTRAINT uk_player_tournament UNIQUE (player_id, tournament_id),
    CONSTRAINT chk_ps_matches CHECK (matches_played >= 0),
    CONSTRAINT chk_ps_kills CHECK (kills >= 0),
    CONSTRAINT chk_ps_deaths CHECK (deaths >= 0)
);

-- =====================================================
-- LEADERBOARD TABLE
-- =====================================================
CREATE TABLE leaderboard (
    leaderboard_id  NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tournament_id   NUMBER NOT NULL,
    team_id         NUMBER NOT NULL,
    rank_position   NUMBER,
    points          NUMBER DEFAULT 0,
    wins            NUMBER DEFAULT 0,
    losses          NUMBER DEFAULT 0,
    draws           NUMBER DEFAULT 0,
    matches_played  NUMBER DEFAULT 0,
    last_updated    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lb_tournament FOREIGN KEY (tournament_id) REFERENCES tournaments(tournament_id) ON DELETE CASCADE,
    CONSTRAINT fk_lb_team FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE CASCADE,
    CONSTRAINT uk_lb_tourn_team UNIQUE (tournament_id, team_id),
    CONSTRAINT chk_lb_points CHECK (points >= 0),
    CONSTRAINT chk_lb_rank CHECK (rank_position > 0)
);

-- =====================================================
-- AUDIT LOG TABLE
-- =====================================================
CREATE TABLE audit_log (
    log_id       NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    table_name   VARCHAR2(50) NOT NULL,
    record_id    NUMBER NOT NULL,
    action       VARCHAR2(10) NOT NULL,
    old_values   CLOB,
    new_values   CLOB,
    performed_by NUMBER,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address   VARCHAR2(45),
    CONSTRAINT fk_audit_user FOREIGN KEY (performed_by) REFERENCES users(user_id),
    CONSTRAINT chk_audit_action CHECK (action IN ('INSERT', 'UPDATE', 'DELETE'))
);

-- =====================================================
-- INDEXES
-- =====================================================
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_role ON users(role_id);
CREATE INDEX idx_teams_captain ON teams(captain_id);
CREATE INDEX idx_team_members_team ON team_members(team_id);
CREATE INDEX idx_team_members_user ON team_members(user_id);
CREATE INDEX idx_tournaments_category ON tournaments(category_id);
CREATE INDEX idx_tournaments_status ON tournaments(status);
CREATE INDEX idx_tournaments_dates ON tournaments(start_date, end_date);
CREATE INDEX idx_reg_tournament ON tournament_registrations(tournament_id);
CREATE INDEX idx_reg_team ON tournament_registrations(team_id);
CREATE INDEX idx_matches_tournament ON matches(tournament_id);
CREATE INDEX idx_matches_team1 ON matches(team1_id);
CREATE INDEX idx_matches_team2 ON matches(team2_id);
CREATE INDEX idx_matches_date ON matches(match_date);
CREATE INDEX idx_match_results_match ON match_results(match_id);
CREATE INDEX idx_player_stats_player ON player_statistics(player_id);
CREATE INDEX idx_player_stats_tournament ON player_statistics(tournament_id);
CREATE INDEX idx_leaderboard_tournament ON leaderboard(tournament_id);
CREATE INDEX idx_leaderboard_team ON leaderboard(team_id);
CREATE INDEX idx_audit_table ON audit_log(table_name);
CREATE INDEX idx_audit_performed ON audit_log(performed_by);

-- =====================================================
-- SEQUENCES (for manual use if needed)
-- =====================================================
CREATE SEQUENCE seq_users START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE seq_teams START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE seq_tournaments START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE seq_matches START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE seq_results START WITH 1 INCREMENT BY 1;

PROMPT 'Schema created successfully!'
