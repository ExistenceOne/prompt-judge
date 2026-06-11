-- Prompt Judge — sample data
-- Import AFTER schema.sql:  mysql -u root -p prompt_judge < sql/seed.sql
--
-- All seeded users share the password: password123
USE prompt_judge;

-- ---------------------------------------------------------------------------
-- Users
-- ---------------------------------------------------------------------------
INSERT INTO users (username, password_hash, name, email, affiliation, is_dark) VALUES
    ('admin', '$2y$10$jIQUrMi2FAL9aDtK9hr5vujPjcLP6KW.E/qsyj9rspDnekUwSneS6', '관리자', 'admin@promptjudge.dev', 'Prompt Judge', 0);
SET @u_admin = LAST_INSERT_ID();

INSERT INTO users (username, password_hash, name, email, affiliation, is_dark) VALUES
    ('yoonjae', '$2y$10$jIQUrMi2FAL9aDtK9hr5vujPjcLP6KW.E/qsyj9rspDnekUwSneS6', '김윤재', 'yoonjae@example.com', '한국기술교육대학교', 1);
SET @u_yoonjae = LAST_INSERT_ID();

INSERT INTO users (username, password_hash, name, email, affiliation, is_dark) VALUES
    ('minji', '$2y$10$jIQUrMi2FAL9aDtK9hr5vujPjcLP6KW.E/qsyj9rspDnekUwSneS6', '김민지', 'minji@example.com', '서울고등학교', 0);
SET @u_minji = LAST_INSERT_ID();

INSERT INTO users (username, password_hash, name, email, affiliation, is_dark) VALUES
    ('junho', '$2y$10$jIQUrMi2FAL9aDtK9hr5vujPjcLP6KW.E/qsyj9rspDnekUwSneS6', '이준호', 'junho@example.com', NULL, 0);
SET @u_junho = LAST_INSERT_ID();

INSERT INTO users (username, password_hash, name, email, affiliation, is_dark) VALUES
    ('soeun', '$2y$10$jIQUrMi2FAL9aDtK9hr5vujPjcLP6KW.E/qsyj9rspDnekUwSneS6', '박소은', 'soeun@example.com', '한국기술교육대학교', 0);
SET @u_soeun = LAST_INSERT_ID();

INSERT INTO users (username, password_hash, name, email, affiliation, is_dark) VALUES
    ('testuser', '$2y$10$jIQUrMi2FAL9aDtK9hr5vujPjcLP6KW.E/qsyj9rspDnekUwSneS6', '테스트유저', 'test@example.com', NULL, 0);
SET @u_test = LAST_INSERT_ID();

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

-- ---------------------------------------------------------------------------
-- Problem 3: Reverse a String
-- ---------------------------------------------------------------------------
INSERT INTO problems
    (title, description, input_format, output_format, sample_input, sample_output,
     time_limit_ms, memory_limit_kb, input_token_limit, output_token_limit)
VALUES
    ('Reverse a String',
     'Read a string S consisting of lowercase English letters and print it reversed.',
     'A single line containing the string S (1 <= |S| <= 100).',
     'Print S reversed.',
     'hello',
     'olleh',
     1000, 256000, 4000, 4000);

SET @p3 = LAST_INSERT_ID();
INSERT INTO testcases (problem_id, stdin, expected_output, is_sample) VALUES
    (@p3, 'hello',      'olleh',      1),
    (@p3, 'abcdef',     'fedcba',     0),
    (@p3, 'a',          'a',          0),
    (@p3, 'programming','gnimmargorp',0);

-- ---------------------------------------------------------------------------
-- Problem 4: Nth Fibonacci Number
-- ---------------------------------------------------------------------------
INSERT INTO problems
    (title, description, input_format, output_format, sample_input, sample_output,
     time_limit_ms, memory_limit_kb, input_token_limit, output_token_limit)
VALUES
    ('Nth Fibonacci Number',
     'Given an integer N, print the Nth Fibonacci number, where F(0) = 0 and F(1) = 1.',
     'A single line containing one integer N (0 <= N <= 30).',
     'Print a single integer: F(N).',
     '10',
     '55',
     1000, 256000, 4000, 4000);

SET @p4 = LAST_INSERT_ID();
INSERT INTO testcases (problem_id, stdin, expected_output, is_sample) VALUES
    (@p4, '10', '55',   1),
    (@p4, '0',  '0',    0),
    (@p4, '1',  '1',    0),
    (@p4, '20', '6765', 0);

-- ---------------------------------------------------------------------------
-- Problem 5: Count Primes Up To N
-- ---------------------------------------------------------------------------
INSERT INTO problems
    (title, description, input_format, output_format, sample_input, sample_output,
     time_limit_ms, memory_limit_kb, input_token_limit, output_token_limit)
VALUES
    ('Count Primes Up To N',
     'Given an integer N, print how many prime numbers are less than or equal to N.',
     'A single line containing one integer N (1 <= N <= 100000).',
     'Print a single integer: the count of primes in [2, N].',
     '10',
     '4',
     2000, 256000, 4000, 4000);

SET @p5 = LAST_INSERT_ID();
INSERT INTO testcases (problem_id, stdin, expected_output, is_sample) VALUES
    (@p5, '10',  '4',  1),
    (@p5, '1',   '0',  0),
    (@p5, '2',   '1',  0),
    (@p5, '100', '25', 0);

-- ---------------------------------------------------------------------------
-- Problem 6: Sort Three Numbers
-- ---------------------------------------------------------------------------
INSERT INTO problems
    (title, description, input_format, output_format, sample_input, sample_output,
     time_limit_ms, memory_limit_kb, input_token_limit, output_token_limit)
VALUES
    ('Sort Three Numbers',
     'Given three integers, print them in ascending order separated by single spaces.',
     'A single line containing three space-separated integers (-1000 <= each <= 1000).',
     'Print the three integers sorted ascending, separated by single spaces.',
     '3 1 2',
     '1 2 3',
     1000, 256000, 4000, 4000);

SET @p6 = LAST_INSERT_ID();
INSERT INTO testcases (problem_id, stdin, expected_output, is_sample) VALUES
    (@p6, '3 1 2',     '1 2 3',     1),
    (@p6, '5 5 5',     '5 5 5',     0),
    (@p6, '-1 0 -5',   '-5 -1 0',   0),
    (@p6, '100 -100 0','-100 0 100',0);

-- ---------------------------------------------------------------------------
-- Submissions (Judging History)
-- ---------------------------------------------------------------------------

-- 1) AC — A+B in Python, Sonnet
INSERT INTO submissions
    (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
     prompt, generated_code, input_tokens, output_tokens, code_size, result,
     exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr, created_at)
VALUES
    (@u_yoonjae, @p1, 71, 'Python (3.8.1)', 'claude-sonnet-4-6', 1.00, NULL,
     '두 정수 A와 B를 입력받아 그 합을 출력하는 파이썬 프로그램을 작성해줘.',
     'a, b = map(int, input().split())
print(a + b)',
     120, 18, 46, 'AC', 42, 9344, 3, NULL, NULL, '2026-06-05 10:12:00');

-- 2) WA — Sum 1..N in Python, off-by-one bug, Haiku
INSERT INTO submissions
    (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
     prompt, generated_code, input_tokens, output_tokens, code_size, result,
     exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr, created_at)
VALUES
    (@u_minji, @p2, 71, 'Python (3.8.1)', 'claude-haiku-4-5', 0.70, NULL,
     '정수 N을 입력받아 1부터 N까지의 합을 출력하는 파이썬 프로그램을 작성해줘.',
     'n = int(input())
print(n * (n - 1) // 2)',
     98, 20, 39, 'WA', 38, 9216, 4, NULL, NULL, '2026-06-05 14:30:00');

-- 3) AC — A+B in C++, Opus (no temperature: sampling not supported)
INSERT INTO submissions
    (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
     prompt, generated_code, input_tokens, output_tokens, code_size, result,
     exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr, created_at)
VALUES
    (@u_junho, @p1, 54, 'C++ (GCC 9.2.0)', 'claude-opus-4-8', NULL, NULL,
     'Read two integers from standard input and print their sum, in C++.',
     '#include <bits/stdc++.h>
using namespace std;

int main() {
    long long a, b;
    cin >> a >> b;
    cout << (a + b) << endl;
    return 0;
}',
     145, 52, 132, 'AC', 12, 10240, 3, NULL, NULL, '2026-06-06 09:05:00');

-- 4) TLE — Count Primes in Python, brute-force, Sonnet
INSERT INTO submissions
    (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
     prompt, generated_code, input_tokens, output_tokens, code_size, result,
     exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr, created_at)
VALUES
    (@u_soeun, @p5, 71, 'Python (3.8.1)', 'claude-sonnet-4-6', 1.00, NULL,
     'N 이하의 소수 개수를 출력하는 파이썬 프로그램을 작성해줘.',
     'n = int(input())

def is_prime(x):
    if x < 2:
        return False
    for i in range(2, x):
        if x % i == 0:
            return False
    return True

print(sum(1 for i in range(2, n + 1) if is_prime(i)))',
     160, 85, 211, 'TLE', 2000, 9600, 5, NULL, NULL, '2026-06-07 11:20:00');

-- 5) CE — Reverse String in Java, missing semicolon, Haiku
INSERT INTO submissions
    (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
     prompt, generated_code, input_tokens, output_tokens, code_size, result,
     exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr, created_at)
VALUES
    (@u_test, @p3, 62, 'Java (OpenJDK 13.0.1)', 'claude-haiku-4-5', 1.00, NULL,
     '문자열을 입력받아 거꾸로 출력하는 자바 프로그램을 작성해줘.',
     'import java.util.Scanner;

public class Main {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in)
        String s = sc.next();
        System.out.println(new StringBuilder(s).reverse().toString());
    }
}',
     130, 70, 224, 'CE', NULL, NULL, 6,
     'Main.java:6: error: '';'' expected
        Scanner sc = new Scanner(System.in)
                                            ^
1 error',
     NULL, '2026-06-07 16:45:00');

-- 6) RE — Sort Three Numbers in Python, NameError, Sonnet
INSERT INTO submissions
    (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
     prompt, generated_code, input_tokens, output_tokens, code_size, result,
     exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr, created_at)
VALUES
    (@u_yoonjae, @p6, 71, 'Python (3.8.1)', 'claude-sonnet-4-6', 1.00, NULL,
     '세 정수를 입력받아 오름차순으로 정렬해서 출력하는 프로그램을 작성해줘.',
     'nums = input().split()
a, b, c = int(nums[0]), int(nums[1]), int(nums[2])
result = sorted([a, b, c])
print(result[0], result[1], result[2])
print(debug_value)',
     105, 48, 152, 'RE', 45, 9300, 11, NULL,
     'Traceback (most recent call last):
  File "main.py", line 5, in <module>
    print(debug_value)
NameError: name ''debug_value'' is not defined',
     '2026-06-08 09:30:00');

-- 7) AC — Fibonacci in JavaScript, Haiku
INSERT INTO submissions
    (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
     prompt, generated_code, input_tokens, output_tokens, code_size, result,
     exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr, created_at)
VALUES
    (@u_minji, @p4, 63, 'JavaScript (Node.js 12.14.0)', 'claude-haiku-4-5', 0.50, NULL,
     '정수 N을 입력받아 N번째 피보나치 수를 출력하는 자바스크립트 프로그램을 작성해줘. (F(0)=0, F(1)=1)',
     'const readline = require("readline");
const rl = readline.createInterface({ input: process.stdin });
rl.on("line", (line) => {
    const n = parseInt(line.trim(), 10);
    let a = 0, b = 1;
    for (let i = 0; i < n; i++) {
        [a, b] = [b, a + b];
    }
    console.log(a);
    rl.close();
});',
     142, 95, 287, 'AC', 68, 33024, 3, NULL, NULL, '2026-06-08 13:00:00');

-- 8) ITLE — prompt itself exceeds the problem's input token limit, Opus
INSERT INTO submissions
    (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
     prompt, generated_code, input_tokens, output_tokens, code_size, result,
     exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr, created_at)
VALUES
    (@u_junho, @p2, 71, 'Python (3.8.1)', 'claude-opus-4-8', NULL, NULL,
     '아래는 1부터 N까지의 합을 구하는 문제에 대한 매우 상세한 배경 설명입니다. 가우스가 어린 시절 1부터 100까지의 합을 빠르게 구한 일화부터 시작해서, 등차수열의 합 공식이 유도되는 과정, 시간 복잡도와 공간 복잡도에 대한 분석, 그리고 다양한 프로그래밍 언어에서의 구현 방식 차이까지 모두 포함하여 매우 길게 설명한 뒤, 마지막에 파이썬으로 N을 입력받아 1부터 N까지의 합을 출력하는 프로그램을 작성해 달라는 요청이 이어집니다. (이하 동일한 설명이 수십 차례 반복됩니다...)',
     'n = int(input())
print(n * (n + 1) // 2)',
     4500, 24, 38, 'ITLE', NULL, NULL, NULL, NULL, NULL, '2026-06-09 10:00:00');

-- 9) OTLE — generated code exceeds the problem's output token limit, Sonnet
INSERT INTO submissions
    (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
     prompt, generated_code, input_tokens, output_tokens, code_size, result,
     exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr, created_at)
VALUES
    (@u_soeun, @p1, 71, 'Python (3.8.1)', 'claude-sonnet-4-6', 1.00, NULL,
     '두 정수의 합을 구하는 프로그램을 작성하되, 모든 줄에 매우 상세한 주석과 단계별 설명을 빠짐없이 포함해서 작성해줘.',
     '# Step 1: Read a single line of input from standard input.
# This line is expected to contain two integers separated by a space.
raw = input()

# Step 2: Split the raw input string on whitespace to obtain two tokens.
tokens = raw.split()

# Step 3: Convert each token from a string to an integer.
a = int(tokens[0])
b = int(tokens[1])

# Step 4: Compute the sum of the two integers.
total = a + b

# Step 5: Print the resulting sum to standard output.
print(total)
# (... thousands of additional explanatory comment lines omitted ...)',
     150, 4200, 9800, 'OTLE', NULL, NULL, NULL, NULL, NULL, '2026-06-09 15:20:00');

-- 10) IE — Claude API error, no code generated
INSERT INTO submissions
    (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
     prompt, generated_code, input_tokens, output_tokens, code_size, result,
     exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr, created_at)
VALUES
    (@u_test, @p3, 71, 'Python (3.8.1)', 'claude-sonnet-4-6', 1.00, NULL,
     '문자열을 입력받아 거꾸로 출력하는 프로그램을 작성해줘.',
     NULL, NULL, NULL, NULL, 'IE', NULL, NULL, NULL, NULL,
     'Claude error: Overloaded (HTTP 529)', '2026-06-10 08:15:00');

-- 11) MLE — Sum 1..N in Python, materializes a huge list, Haiku
INSERT INTO submissions
    (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
     prompt, generated_code, input_tokens, output_tokens, code_size, result,
     exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr, created_at)
VALUES
    (@u_yoonjae, @p2, 71, 'Python (3.8.1)', 'claude-haiku-4-5', 1.00, NULL,
     '1부터 N까지의 합을 구하는 프로그램을 작성하되, 1부터 N까지의 모든 정수를 리스트에 저장한 뒤 그 리스트의 합을 구해줘.',
     'n = int(input())
nums = list(range(1, n + 1))
print(sum(nums))',
     110, 28, 64, 'MLE', 850, 300032, 3, NULL, NULL, '2026-06-10 12:40:00');

-- 12) AC — Count Primes in Python using a sieve, extended thinking enabled, Sonnet
INSERT INTO submissions
    (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
     prompt, generated_code, input_tokens, output_tokens, code_size, result,
     exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr, created_at)
VALUES
    (@u_minji, @p5, 71, 'Python (3.8.1)', 'claude-sonnet-4-6', NULL, 2048,
     'N 이하의 소수의 개수를 에라토스테네스의 체를 이용해서 효율적으로 구하는 파이썬 프로그램을 작성해줘.',
     'n = int(input())
if n < 2:
    print(0)
else:
    sieve = [True] * (n + 1)
    sieve[0] = sieve[1] = False
    for i in range(2, int(n ** 0.5) + 1):
        if sieve[i]:
            for j in range(i * i, n + 1, i):
                sieve[j] = False
    print(sum(sieve))',
     180, 110, 296, 'AC', 55, 9728, 3, NULL, NULL, '2026-06-11 09:00:00');

-- ---------------------------------------------------------------------------
-- Community boards: posts, comments, notifications
-- ---------------------------------------------------------------------------

INSERT INTO posts (user_id, category, problem_id, title, body, created_at) VALUES
    (@u_admin, 'notice', NULL, 'Prompt Judge에 오신 것을 환영합니다!',
     'Prompt Judge는 프롬프트만으로 AI에게 코드를 생성시키고, 그 코드를 채점하는 온라인 저지입니다.
문제를 풀고, 프롬프트를 제출하고, 채점 결과를 확인해보세요. 궁금한 점은 Q&A 게시판을 이용해주세요.', '2026-06-01 09:00:00');
SET @post1 = LAST_INSERT_ID();

INSERT INTO posts (user_id, category, problem_id, title, body, created_at) VALUES
    (@u_admin, 'notice', NULL, '[공지] Judge0 서버 정기 점검 안내 (6/15 02:00~04:00)',
     '서비스 안정화를 위해 6월 15일 새벽 2시부터 4시까지 채점 서버 점검이 진행됩니다.
점검 시간 동안에는 프롬프트 제출 및 채점이 일시적으로 지연될 수 있으니 양해 부탁드립니다.', '2026-06-09 18:00:00');
SET @post2 = LAST_INSERT_ID();

INSERT INTO posts (user_id, category, problem_id, title, body, created_at) VALUES
    (@u_minji, 'free', NULL, '다들 어떤 모델을 주로 쓰시나요?',
     '저는 Sonnet 4.6을 기본으로 쓰고 있는데, 다른 분들은 Haiku나 Opus도 많이 쓰시는지 궁금해요.
응답 속도랑 정확도 측면에서 다들 어떤 모델이 제일 만족스러우셨나요?', '2026-06-06 20:11:00');
SET @post3 = LAST_INSERT_ID();

INSERT INTO posts (user_id, category, problem_id, title, body, created_at) VALUES
    (@u_junho, 'free', NULL, '프롬프트 작성 꿀팁 공유합니다',
     '입출력 형식을 프롬프트에 명확하게 적어주면 채점 통과율이 확실히 올라가더라고요.
"표준 입력으로 받아서 표준 출력으로 출력해줘" 같은 문구를 꼭 넣어보세요.', '2026-06-07 21:40:00');
SET @post4 = LAST_INSERT_ID();

INSERT INTO posts (user_id, category, problem_id, title, body, created_at) VALUES
    (@u_soeun, 'qna', @p2, 'Sum 1..N 문제에서 자꾸 시간 초과/메모리 초과가 떠요',
     'N까지의 정수를 리스트에 다 담아서 sum()으로 더하는 방식으로 프롬프트를 작성했는데
N이 큰 경우에 메모리 초과가 나는 것 같습니다. 어떻게 프롬프트를 수정해야 할까요?', '2026-06-08 22:05:00');
SET @post5 = LAST_INSERT_ID();

INSERT INTO posts (user_id, category, problem_id, title, body, created_at) VALUES
    (@u_test, 'qna', @p5, 'Count Primes 문제 입력 범위 질문입니다',
     '문제 설명에 N의 범위가 명확히 안 보이는 것 같아서요. N의 최댓값이 어느 정도인가요?
효율적인 알고리즘을 써야 하는지 궁금합니다.', '2026-06-10 11:30:00');
SET @post6 = LAST_INSERT_ID();

INSERT INTO posts (user_id, category, problem_id, title, body, created_at) VALUES
    (@u_yoonjae, 'free', NULL, '같이 알고리즘 스터디 하실 분 구합니다',
     '매주 주말마다 Prompt Judge 문제를 같이 풀고 프롬프트 작성 노하우를 공유하는 스터디를 만들려고 합니다.
관심 있으신 분은 댓글 남겨주세요!', '2026-06-10 19:00:00');
SET @post7 = LAST_INSERT_ID();

INSERT INTO comments (post_id, user_id, body, created_at) VALUES
    (@post1, @u_minji,   '환영해주셔서 감사합니다! 잘 이용하겠습니다 :)', '2026-06-01 10:30:00'),
    (@post3, @u_junho,   '저는 주로 Sonnet 4.6을 사용해요. 가성비가 좋더라고요.', '2026-06-06 21:00:00'),
    (@post3, @u_test,    '저는 어려운 문제를 풀 때만 Opus를 사용합니다.', '2026-06-07 08:15:00'),
    (@post5, @u_yoonjae, 'list(range(...))로 메모리에 다 올리지 말고 N*(N+1)/2 공식을 쓰도록 프롬프트를 수정해보세요.', '2026-06-09 09:00:00'),
    (@post5, @u_soeun,   '오! 공식으로 바꾸니 바로 통과했습니다. 감사합니다!', '2026-06-09 09:20:00'),
    (@post6, @u_admin,   '문제 설명에 명시된 대로 1 <= N <= 100000 입니다. 이 정도 범위면 에라토스테네스의 체를 사용하는 것을 추천드려요.', '2026-06-10 12:00:00'),
    (@post7, @u_minji,   '참여하고 싶습니다! 언제부터 시작하나요?', '2026-06-10 20:15:00');

INSERT INTO notifications (user_id, actor_id, type, post_id, message, is_read, created_at) VALUES
    (@u_admin,   @u_minji,   'comment', @post1, '김민지님이 귀하의 게시글 "Prompt Judge에 오신 것을 환영합니다!"에 댓글을 남겼습니다.', 1, '2026-06-01 10:30:00'),
    (@u_minji,   @u_junho,   'comment', @post3, '이준호님이 귀하의 게시글 "다들 어떤 모델을 주로 쓰시나요?"에 댓글을 남겼습니다.', 1, '2026-06-06 21:00:00'),
    (@u_minji,   @u_test,    'comment', @post3, '테스트유저님이 귀하의 게시글 "다들 어떤 모델을 주로 쓰시나요?"에 댓글을 남겼습니다.', 0, '2026-06-07 08:15:00'),
    (@u_soeun,   @u_yoonjae, 'comment', @post5, '조윤재님이 귀하의 게시글 "Sum 1..N 문제에서 자꾸 시간 초과/메모리 초과가 떠요"에 댓글을 남겼습니다.', 1, '2026-06-09 09:00:00'),
    (@u_test,    @u_admin,   'comment', @post6, '관리자님이 귀하의 게시글 "Count Primes 문제 입력 범위 질문입니다"에 댓글을 남겼습니다.', 0, '2026-06-10 12:00:00'),
    (@u_yoonjae, @u_minji,   'comment', @post7, '김민지님이 귀하의 게시글 "같이 알고리즘 스터디 하실 분 구합니다"에 댓글을 남겼습니다.', 0, '2026-06-10 20:15:00');
