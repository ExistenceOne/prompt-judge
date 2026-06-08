-- Prompt Judge — database schema (Core MVP)
-- Import: mysql -u root -p < sql/schema.sql   (or via phpMyAdmin)

CREATE DATABASE IF NOT EXISTS prompt_judge
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE prompt_judge;

-- Drop in dependency order so re-running the script is safe.
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS testcases;
DROP TABLE IF EXISTS problems;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    affiliation   VARCHAR(150) DEFAULT NULL,
    is_dark       TINYINT(1)   NOT NULL DEFAULT 0,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE problems (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    title              VARCHAR(200) NOT NULL,
    description        TEXT         NOT NULL,
    input_format       TEXT         DEFAULT NULL,
    output_format      TEXT         DEFAULT NULL,
    sample_input       TEXT         DEFAULT NULL,
    sample_output      TEXT         DEFAULT NULL,
    time_limit_ms      INT          NOT NULL DEFAULT 2000,
    memory_limit_kb    INT          NOT NULL DEFAULT 256000,
    input_token_limit  INT          NOT NULL DEFAULT 4000,
    output_token_limit INT          NOT NULL DEFAULT 4000,
    created_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hidden judging test cases (is_sample=1 rows may also be shown on the problem page).
CREATE TABLE testcases (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    problem_id      INT  NOT NULL,
    stdin           TEXT NOT NULL,
    expected_output TEXT NOT NULL,
    is_sample       TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (problem_id) REFERENCES problems(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One row per judged submission = the Judging History.
CREATE TABLE submissions (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    problem_id       INT NOT NULL,
    language_id      INT NOT NULL,
    language_name    VARCHAR(100) NOT NULL,
    model            VARCHAR(100) NOT NULL,
    temperature      DECIMAL(3,2) DEFAULT NULL,
    top_p            DECIMAL(3,2) DEFAULT NULL,
    prompt           TEXT NOT NULL,
    generated_code   MEDIUMTEXT   DEFAULT NULL,
    input_tokens     INT DEFAULT NULL,
    output_tokens    INT DEFAULT NULL,
    code_size        INT DEFAULT NULL,
    -- Result label: AC / WA / TLE / MLE / ITLE / OTLE / CE / RE / IE
    result           VARCHAR(8)   NOT NULL,
    exec_time_ms     INT DEFAULT NULL,
    memory_kb        INT DEFAULT NULL,
    judge0_status_id INT DEFAULT NULL,
    compile_output   TEXT DEFAULT NULL,
    stderr           TEXT DEFAULT NULL,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (problem_id) REFERENCES problems(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_submissions_user    ON submissions(user_id);
CREATE INDEX idx_submissions_problem ON submissions(problem_id);
CREATE INDEX idx_submissions_result  ON submissions(result);
