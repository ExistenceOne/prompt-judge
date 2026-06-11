# Prompt Judge — Code Overview

A summary of every PHP and SQL file in the project: what it does, the
functions/queries it defines, and the intent behind its comments. Generated
as a project-report reference for the codebase as of the `feat/korean`
branch.

Stack: vanilla PHP (no framework/Composer), MySQL via PDO, raw cURL clients
for the Anthropic (Claude) and Judge0 APIs. UI strings are mid-migration to
Korean; code comments remain in English.

---

## 1. Configuration (`config/`)

### `config/config.example.php`
Template for local config, copied to `config/config.php` (gitignored).
Comments explain that this file lives outside the web root so secrets can't
be served even if PHP stops executing. Returns one associative array with:
- `db` — MySQL host/port/name/user/pass/charset.
- `claude` — Anthropic API key, endpoint, `anthropic-version` header,
  `max_tokens` cap for generated code, request timeout.
- `judge0` — base URL, extra headers (for RapidAPI-hosted Judge0), timeout
  (synchronous `wait=true` only).
- `site` — display name, GitHub URL, creator name.

### `config/config.php`
The real (Korean-commented) instance of the same structure, with live
credentials filled in.

---

## 2. Core library (`src/`)

All files are `declare(strict_types=1)` and are pulled in once via
`bootstrap.php`.

### `src/bootstrap.php`
Included at the top of every page in `public/`. Comment: "Loads config,
starts the session, and pulls in the shared helpers so each page only needs
a single `require`."
- Turns on full error reporting/display (dev mode note: "Turn display off in
  production").
- Defines `APP_ROOT`, `SRC_PATH`, `CONFIG_PATH`, `JUDGE0_DATA`.
- Loads `config/config.php` into `$GLOBALS['CONFIG']`, exits with HTTP 500 if
  missing.
- Starts the session, then requires `helpers.php`, `db.php`, `auth.php`,
  `community.php`, `notifications.php`, `layout.php`.

### `src/db.php`
"Database access — a single shared PDO connection (MySQL)."
- `db(): PDO` — lazily creates and caches a PDO connection (exception mode,
  associative fetch, real prepares). Exits with HTTP 500 on connection
  failure.
- `db_run(string $sql, array $params = []): PDOStatement` — prepare + execute
  helper used by every query in the app.

### `src/auth.php`
"Authentication: registration, login, logout, and session helpers."
- `current_user(): ?array` — returns the logged-in user's row (statically
  cached per request) or `null`.
- `require_login(): void` — redirects to `login.php` with a flash message if
  not logged in.
- `attempt_login($username, $password): bool` — verifies password hash,
  regenerates the session id on success (comment: "Prevent session
  fixation").
- `register_user(...)` — validates required fields, enforces a 6-character
  minimum password, checks username uniqueness, inserts the new user with
  `password_hash()`. Returns `[success, errorMessage]`.
- `logout(): void` — clears and destroys the session.

### `src/helpers.php`
"Small shared helpers: output escaping, redirects, flash messages, and
result-label metadata."
- `e(?string $value): string` — `htmlspecialchars` wrapper used everywhere
  output is printed.
- `url(string $path = ''): string` — builds a base-relative URL so the app
  works whether served at `/` or a subdirectory.
- `redirect(string $path): never` — `Location:` header + exit.
- `flash()` / `take_flashes()` — one-shot session flash messages.
- `post(string $key, string $default = '')` — trimmed `$_POST` reader.
- `query_int(string $key, int $default = 0)` — integer `$_GET` reader.
- `result_meta(string $code)` — maps a verdict code (`AC`, `WA`, `TLE`,
  `MLE`, `ITLE`, `OTLE`, `CE`, `RE`, `IE`) to a human label and CSS class
  (`ok`/`bad`/`warn`).
- `model_options(): array` — the AI models offered on the submission page.
  Comment documents that Opus 4.7/4.8 reject `temperature` (HTTP 400), so
  models are flagged `sampling => false`/`thinking => true` to control which
  parameters `src/claude.php` includes — extended thinking
  (`budget_tokens`) and sampling (`temperature`) are mutually exclusive on
  the wire.

### `src/judge0.php`
"Judge0 client via raw cURL, plus helpers for the reference data files in
`Judge0/languages.json` and `Judge0/statuses.json`."
- `judge0_languages(): array` — loads + caches + alphabetizes the language
  list from `Judge0/languages.json`.
- `judge0_language_name(int $id): string` — id → name lookup, `'Unknown'` if
  not found.
- `judge0_status_text(int $id): string` — id → description lookup from
  `Judge0/statuses.json`, falling back to `"Status {id}"`.
- `judge0_run(...)` — POSTs base64-encoded source/stdin/expected output to
  `/submissions?base64_encoded=true&wait=true` with the configured CPU/memory
  limits, then base64-decodes `stdout`/`stderr`/`compile_output`/`message` in
  the response. Returns `{ok, result, error}`.

### `src/claude.php`
"Claude API client (Anthropic Messages API) via raw cURL." Header comment
documents the contract:
```
POST https://api.anthropic.com/v1/messages
headers: x-api-key, anthropic-version: 2023-06-01, content-type: application/json
body:    { model, max_tokens, system, messages, [temperature], [thinking] }
reply:   { content: [{type:"text", text}], usage: {input_tokens, output_tokens} }
```
- `claude_generate_code($prompt, $languageName, $model, ?float $temperature, ?int $thinkingBudget): array`
  — builds a fixed system prompt instructing the model to act as a
  code-generation engine (write a complete stdin/stdout program, output raw
  source only). Looks up `model_options()` for `sampling`/`thinking` support:
  - If the model supports thinking and `$thinkingBudget >= 1024`, sends
    `thinking: {type: "enabled", budget_tokens: $thinkingBudget}` and raises
    `max_tokens` by that budget (comment explains thinking and
    temperature/top_p are mutually exclusive per the API).
  - Otherwise, if the model supports sampling and a temperature was given,
    sends `temperature`.
  - Calls `claude_http_post()`, then concatenates all `type === "text"`
    content blocks (ignoring `thinking` blocks) and strips code fences.
  - Returns `{ok, code, input_tokens, output_tokens, error}`.
- `claude_http_post(array $cfg, array $body): array` — generic JSON POST via
  cURL with the Anthropic headers/timeout; normalizes cURL/HTTP/JSON failures
  into `{ok:false, error}`.
- `claude_strip_fences(string $text): string` — "Strip ```lang ... ```
  markdown fences if the model added them anyway," handling both a clean
  fenced block and a dangling leading/trailing fence.

### `src/pipeline.php`
"Submission pipeline: Prompt -> Claude -> token gate -> Judge0 -> result
label." Comment notes it mirrors the README's "How It Works (Submission
Pipeline)" section.
- `run_submission($userId, $problem, $languageId, $model, ?float $temperature, ?int $thinkingBudget, $prompt): int`
  1. Builds a `$record` array (defaults to result `'IE'`) including
     `temperature` and `thinking_budget`.
  2. Calls `claude_generate_code()`. On failure, persists with the error in
     `stderr`.
  3. Records `generated_code`, `input_tokens`, `output_tokens`, `code_size`.
  4. **Token gate, checked before Judge0 is contacted**: if
     `input_tokens > problem.input_token_limit` → result `ITLE`; if
     `output_tokens > problem.output_token_limit` → result `OTLE`.
  5. Loads the problem's test cases; if none exist, result `IE` with an
     explanatory `stderr`.
  6. Runs each test case through `judge0_run()` with a CPU limit derived from
     `time_limit_ms` (rounded up to whole seconds, minimum 1) and the
     configured memory limit; tracks the max time/memory across cases.
  7. `label_from_status()` converts each Judge0 status (+ memory check) into
     a verdict; the **first failing case** decides the final result and
     breaks the loop, capturing `compile_output` (status 6) or `stderr`.
  8. Persists the final record via `persist_submission()`.
- `label_from_status(int $statusId, int $memoryKb, int $memoryLimitKb): string`
  — match expression: `3` → `MLE` if over memory else `AC`; `4` → `WA`;
  `5` → `TLE`; `6` → `CE`; `7..12` → `RE`; default → `IE`.
- `persist_submission(array $r): int` — `INSERT INTO submissions (...)` with
  named placeholders for every column (including `thinking_budget`), returns
  `lastInsertId()`.

### `src/notifications.php`
"Notifications: create alerts and read them back for the bell + list page."
- `notify($userId, ?$actorId, $type, ?$postId, $message)` — inserts a
  notification row; no-op if `$actorId === $userId` (comment: "you don't get
  notified about your own actions").
- `unread_notification_count(int $userId): int` — count where `is_read = 0`.
- `user_notifications(int $userId, int $limit = 50): array` — newest-first,
  joined with the actor's username.
- `mark_notifications_read(int $userId): void` — bulk-marks unread rows read.

### `src/community.php`
"Community boards: categories, post and comment lookups, ownership checks."
Comment notes writes (create/update/delete) live inline in the page handlers
"mirroring the style of auth.php / submit.php"; this file holds shared reads.
- `board_categories(): array` — `notice` → 공지사항, `free` → 자유게시판,
  `qna` → Q&A.
- `board_category_label(string $code): string` — label lookup with fallback
  to the raw code.
- `is_valid_category(string $code): bool`.
- `find_post(int $id): ?array` — post joined with author username/name.
- `post_comments(int $postId): array` — comments joined with author info,
  oldest first.
- `find_comment(int $id): ?array`.
- `owns(array $row, int $userId): bool` — ownership check for posts/comments.

### `src/layout.php`
"Shared page chrome: header (nav) and footer."
- `render_header(string $title = '')` — emits `<!DOCTYPE html>` through the
  opening of `<main>`. Inline script applies a saved `pj-theme` dark-mode
  class before paint (comment: "Apply saved theme immediately to avoid a
  flash of the wrong color"). Nav links: 문제 (problems), 채점 (history),
  게시판 (board); right side shows a theme toggle, notification bell with
  unread badge, username/마이페이지 link and 로그아웃, or a 로그인 link when
  logged out. Renders any pending flash messages.
- `render_footer()` — closes `<main>`, renders a footer (copyright, site
  name, GitHub link), includes `assets/js/app.js`, closes the document.

---

## 3. Public entry points (`public/`)

Every page starts with `require __DIR__ . '/../src/bootstrap.php'`.

### `public/index.php`
Landing page. Hero section pitches the "write a prompt, not code" concept
with links to Browse Problems / Create an account. A "How it works" ordered
list explains the pipeline: pick a problem → choose model/language and write
a prompt → Claude generates code and reports token usage → Judge0 runs it
against hidden tests for a verdict (AC/WA/TLE/MLE, or ITLE/OTLE for blown
token budgets) → review the result in Judging History.

### `public/login.php`
Redirects away if already logged in. On POST, calls `attempt_login()`;
flashes success/failure (Korean messages) and redirects to `index.php` or
re-renders the form.

### `public/signup.php`
Redirects away if already logged in. On POST, calls `register_user()` with
username/password/name/email/affiliation; flashes the result and redirects
to `login.php` on success.

### `public/logout.php`
Calls `logout()`, restarts the session (comment: "Start a fresh session so
the goodbye flash survives the redirect"), regenerates the session id,
flashes a goodbye message, redirects to `index.php`.

### `public/mypage.php`
Requires login. Queries total submissions and accepted count for the user,
plus the distinct list of problems solved (`result = 'AC'`). Renders profile
info, a small stats block (제출/맞았습니다/해결한 문제), and a list of solved
problems linking to each problem page.

### `public/problems.php`
Lists problems with submission/accepted counts via a `LEFT JOIN` +
`GROUP BY`, with optional search by title (`LIKE`) or exact id. Renders a
searchable table (ID, 제목, 제출수, 맞은 사람 수).

### `public/problem.php`
Looks up a single problem by id (404 page if missing). Renders title, time
limit, memory limit, input/output token limits, description, optional
input/output format sections, optional sample input/output, and a button
linking to `submit.php?problem_id=...`.

### `public/submit.php`
Requires login. Loads the target problem (404 if missing), `model_options()`
and `judge0_languages()`. Form defaults: first model, language id `71`
(Python 3.8.1), temperature `1.0`, `thinking_budget` `0`, empty prompt.
On POST:
- Re-reads all form fields (including `thinking_budget`).
- Validates: model must exist, language must resolve via
  `judge0_language_name()`, prompt must be non-empty, and
  `thinking_budget` must be `0` (off) or `>= 1024`.
- On success, calls `run_submission()` with the parsed temperature
  (or `null`) and thinking budget (`null` if `0`), then redirects to
  `judging.php?id=...`.
Renders: model + target-language selects, a Temperature range slider
(0–1, step 0.05) and a "Thinking budget (tokens)" range slider (0–12000,
step 1024) with live `<output>` value display, a note that `0` disables
extended thinking (otherwise ≥1024 required) and that temperature is ignored
when thinking is enabled, and the prompt textarea.

### `public/judging.php`
Looks up a submission joined with its user and problem title (404 if
missing). Renders a verdict banner via `result_meta()`, then a key/value
table: 문제, 사용자, 모델, 언어, 토큰 (I/O), 실행 시간, 메모리, 코드 크기,
optional Judge0 상태 (via `judge0_status_text()`), and 제출 시간. Below that:
the prompt, the generated source code (if any), compiler output (if any),
and stderr/error log (if any).

> Note: this page currently does **not** display `temperature` or
> `thinking_budget`, even though both are persisted on the submission record.

### `public/history.php`
"Judging History" table across all users. Supports filtering by `q` (matches
user id, problem id, or username via `LIKE`) and by `result` (exact verdict
code, from a fixed list `AC, WA, TLE, MLE, ITLE, OTLE, CE, RE, IE`). Renders a
sortable-by-recency (`ORDER BY s.id DESC LIMIT 200`) table with columns: ID,
사용자, 문제, 결과, 시간, 메모리, 토큰 (입/출력), 언어, 코드 크기, 제출 시간.

### `public/board.php`
Community board index. Optional `category` filter (validated against
`is_valid_category()`) and `q` search across title/username/name. Lists
posts with author and comment counts (`LEFT JOIN comments` + `GROUP BY`,
`ORDER BY p.id DESC LIMIT 200`). Shows category tabs (전체 + each category)
and, when logged in, a "새 글 쓰기" button linking to `post_form.php`
(carrying the current category).

### `public/post.php`
Shows a single post (404 if missing) with category tag, author, timestamps
(수정됨 if edited), and an optional "관련 문제" link. If the current user owns
the post, shows 수정/삭제 actions (delete is a confirm-guarded POST form to
`post_delete.php`). Lists comments, each with edit/delete actions for the
owner (delete via confirm-guarded POST to `comment_delete.php`). Logged-in
users get a comment form posting to `comment_create.php`; otherwise a prompt
to log in.

### `public/post_form.php`
Requires login. Handles both create and edit (`?id=` query param). For edits,
loads the post, 404s if missing, and redirects with a flash if the current
user doesn't own it. Validates category, non-empty title/body, and an
optional numeric `problem_id` that must reference an existing problem.
Inserts or updates `posts` accordingly (`updated_at = NOW()` on edit) and
redirects to the post.

### `public/post_delete.php`
Requires login and POST. Loads the post by `id`, checks ownership, then
`DELETE FROM posts` (comment notes comments/notifications cascade via FK),
flashes success, redirects to `board.php`.

### `public/comment_create.php`
Requires login and POST. Validates the target post exists and `body` is
non-empty, inserts the comment, then calls `notify()` to alert the post
author (skipped automatically if they're commenting on their own post),
using a Korean message: `"{name}님이 귀하의 게시글 "{title}"에 댓글을
남겼습니다."`. Redirects back to the post's comment section.

### `public/comment_edit.php`
Requires login. Loads the comment (404 if missing), checks ownership
(redirects with a flash otherwise). On POST with non-empty body, updates the
comment (`updated_at = NOW()`) and redirects back to the post; otherwise
re-renders the edit form pre-filled with the current body.

### `public/comment_delete.php`
Requires login and POST. Loads the comment, checks ownership, deletes it, and
redirects back to the post's comment section.

### `public/notifications.php`
Requires login. Fetches the user's notifications via `user_notifications()`
*before* calling `mark_notifications_read()` (comment: "so we can still show
which were unread"). Renders each as a link to the related post (with
`#comments` anchor) or plain text if there's no post, styled `read`/`unread`,
with a timestamp.

---

## 4. Database (`sql/`)

### `sql/schema.sql`
"Prompt Judge — database schema (Core MVP)." Creates database
`prompt_judge` (`utf8mb4`/`utf8mb4_unicode_ci`), drops tables in
dependency order for safe re-runs, then creates:

- **`users`** — `id`, `username` (unique), `password_hash`, `name`, `email`,
  `affiliation`, `is_dark` (dark-mode preference), `created_at`.
- **`problems`** — title/description/input/output format, sample
  input/output, `time_limit_ms` (default 2000), `memory_limit_kb` (default
  256000), `input_token_limit`/`output_token_limit` (default 4000 each),
  `created_at`.
- **`testcases`** — `problem_id` FK (cascade delete), `stdin`,
  `expected_output`, `is_sample` flag (comment: "Hidden judging test cases
  (`is_sample=1` rows may also be shown on the problem page)").
- **`submissions`** — "One row per judged submission = the Judging History."
  FKs to `users`/`problems` (cascade delete); columns for `language_id`,
  `language_name`, `model`, `temperature`, **`thinking_budget` (INT)**,
  `prompt`, `generated_code`, `input_tokens`, `output_tokens`, `code_size`,
  `result` (comment lists the verdict codes: AC/WA/TLE/MLE/ITLE/OTLE/CE/RE/IE),
  `exec_time_ms`, `memory_kb`, `judge0_status_id`, `compile_output`, `stderr`,
  `created_at`. Indexes on `user_id`, `problem_id`, `result`.
- Community board section (comment: "Community boards"):
  - **`posts`** — `category` (notice/free/qna), optional `problem_id` FK
    (`SET NULL` on delete), `title`, `body`, `created_at`/`updated_at`, FK to
    `users` (cascade). Indexes on `category`, `user_id`.
  - **`comments`** — `post_id`/`user_id` FKs (cascade), `body`,
    `created_at`/`updated_at`. Index on `post_id`.
  - **`notifications`** — "Per-user notifications (e.g. a reply to one of
    your posts)." `user_id` (recipient), `actor_id` (nullable, who triggered
    it, `SET NULL` on delete), `type`, `post_id` (cascade), `message`,
    `is_read`, `created_at`. Composite index on `(user_id, is_read)`.

### `sql/seed.sql`
"Prompt Judge — sample data," imported after `schema.sql`. Inserts two
sample problems with their test cases:
- **Problem 1: "A + B"** — read two integers, print their sum
  (`-10^9 <= A,B <= 10^9`); 1 sample + 3 hidden test cases (including the
  `10^9 + 10^9` boundary).
- **Problem 2: "Sum 1..N"** — print `1 + 2 + ... + N` for `1 <= N <= 10^6`;
  1 sample + 3 hidden test cases (including `N = 10^6`).
Both use `time_limit_ms = 2000`, `memory_limit_kb = 256000`, and
input/output token limits of `4000`.

---

## 5. Cross-cutting notes

- **Submission pipeline**: `submit.php` → `run_submission()` →
  `claude_generate_code()` → token-limit gate → `judge0_run()` per test case
  → `label_from_status()` → `persist_submission()`. `index.php`'s "How it
  works" section and `pipeline.php`'s header comment both describe this flow.
- **Extended thinking vs. sampling**: `model_options()` flags each model with
  `sampling` and `thinking` support; `claude_generate_code()` enforces that
  the two are mutually exclusive (thinking + `budget_tokens` *or*
  `temperature`, never both), per Anthropic's API constraints.
- **Korean localization in progress**: most user-facing strings in `public/`
  (and the nav in `src/layout.php`) are already in Korean; `config/config.php`
  comments are Korean while `config.example.php` remains English. PHP
  doc-comments throughout `src/` are English.
- **Security basics observed**: all DB access goes through parameterized
  `db_run()`; output is consistently passed through `e()`; passwords use
  `password_hash`/`password_verify`; login regenerates the session id;
  ownership checks (`owns()`) gate edit/delete actions on posts/comments.
