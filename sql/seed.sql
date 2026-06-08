-- Prompt Judge — sample data
-- Import AFTER schema.sql:  mysql -u root -p prompt_judge < sql/seed.sql
USE prompt_judge;

-- ---------------------------------------------------------------------------
-- Problem 1: A + B
-- ---------------------------------------------------------------------------
INSERT INTO problems
    (title, description, input_format, output_format, sample_input, sample_output,
     time_limit_ms, memory_limit_kb, input_token_limit, output_token_limit)
VALUES
    ('A + B',
     'Read two integers A and B, then print their sum.',
     'A single line containing two space-separated integers A and B (-10^9 <= A, B <= 10^9).',
     'Print a single integer: A + B.',
     '3 5',
     '8',
     2000, 256000, 4000, 4000);

SET @p1 = LAST_INSERT_ID();
INSERT INTO testcases (problem_id, stdin, expected_output, is_sample) VALUES
    (@p1, '3 5',            '8',           1),
    (@p1, '100 200',        '300',         0),
    (@p1, '-5 5',           '0',           0),
    (@p1, '1000000000 1000000000', '2000000000', 0);

-- ---------------------------------------------------------------------------
-- Problem 2: Sum 1..N
-- ---------------------------------------------------------------------------
INSERT INTO problems
    (title, description, input_format, output_format, sample_input, sample_output,
     time_limit_ms, memory_limit_kb, input_token_limit, output_token_limit)
VALUES
    ('Sum 1..N',
     'Given a positive integer N, print the sum 1 + 2 + ... + N.',
     'A single line containing one integer N (1 <= N <= 10^6).',
     'Print a single integer: the sum from 1 to N.',
     '10',
     '55',
     2000, 256000, 4000, 4000);

SET @p2 = LAST_INSERT_ID();
INSERT INTO testcases (problem_id, stdin, expected_output, is_sample) VALUES
    (@p2, '10',     '55',          1),
    (@p2, '1',      '1',           0),
    (@p2, '100',    '5050',        0),
    (@p2, '1000000','500000500000',0);
