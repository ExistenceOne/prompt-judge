# Overview

- **Project Type:** Solo Korean Undergraduate Developer PHP Term Project
- **Project Concept:** An AI-driven algorithm problem-solving and online judging platform where users submit **prompts** instead of direct code.

## Background

- **Judge Site Evolution**: As the use of AI tools to solve algorithm problems becomes mainstream, traditional coding test environments that purely test syntax and manual logic implementation are losing their relevance.
- **Paradigm Shift**: In the modern software industry, the ability to effectively utilize AI is becoming just as crucial as raw coding skills. Major tech companies (e.g., Meta) have already begun permitting the use of AI tools during technical interviews and coding tests.

## Purpose

In response to this shifting landscape, this project aims to build an online judge platform designed to cultivate **prompt engineering skills**, logical problem decomposition, and AI-assisted problem-solving capabilities.

## Technology Stack

- **PHP (Vanilla / Core PHP)**
    - Web application logic and routing.
    - Constraint: Focus on foundational syntax (procedural or basic OOP) appropriate for a university curriculum, avoiding advanced industry frameworks (like Laravel).
    - Utilizes core features: Forms, Sessions, and Cookies.
- **Apache**
    - Web server hosting.
- **MySQL**
    - Relational database for storing Users, Posts, Comments, Problems, and Judging Histories.
- **Claude API (Anthropic)**
    - Receives the user's prompt and generates the executable source code.
    - Extracts `input_tokens` and `output_tokens` from the `usage` metadata for scoring and limits.
- **Judge0 API**
    - Receives the AI-generated source code, compiles it, and runs it against hidden test cases.
    - Returns standard judging metrics (execution time, memory usage, status id, etc.)

## How It Works (Submission Pipeline)

- **High-Level Data Flow**
    - **User ↔ PHP App ↔ MySQL:** The browser interacts with the PHP/Apache application, which persists Users, Posts, Comments, Problems, and Judging Histories in MySQL.
    - **PHP App → Claude API:** The app sends the user's prompt (plus model/temperature/top‑p settings) and receives back generated source code along with `usage` metadata (`input_tokens`, `output_tokens`).
    - **PHP App → Judge0 API:** The app sends the AI-generated source code and the problem's hidden test cases, and receives back compiled execution results (status id, execution time, memory usage).
- **Step-by-Step Submission Sequence**
    1. The user configures the submission (target language, AI model, temperature, top‑p) and writes a prompt on the Problem Submission Page.
    2. The PHP backend forwards the prompt and configuration to the Claude API.
    3. Claude returns the generated source code plus `usage` metadata (`input_tokens`, `output_tokens`).
    4. The backend compares these token counts against the problem's **Token Limit**. If exceeded, the submission is immediately marked **ITLE** (Input Token Limit Exceeded) or **OTLE** (Output Token Limit Exceeded) — the pipeline stops here without ever calling Judge0.
    5. Otherwise, the generated source code, the corresponding language id (looked up from `Judge0/languages.json` based on the user's selected target language), and the problem's hidden test cases are submitted to the Judge0 API.
    6. Judge0 compiles and executes the code against the hidden test cases, returning a status id (resolved via `Judge0/statuses.json`), execution time, and memory usage.
    7. The backend translates Judge0's status and metrics into the platform's result labels — **AC**, **WA**, **TLE**, **MLE**, or a Compilation/Runtime Error variant — by comparing the returned execution time and memory usage against the problem's declared Time Limit and Memory Limit.
    8. The complete record (prompt, generated source code, token usage, judging metrics, and final result) is written to the Judging History, and the user receives a notification.
- **Where Each Result Label Comes From**
    - **ITLE / OTLE:** Determined locally, *before* contacting Judge0, by comparing Claude's `usage.input_tokens` / `usage.output_tokens` against the problem's Token Limit.
    - **AC / WA / TLE / MLE / Compilation Error / Runtime Error:** Determined from Judge0's returned status id together with its execution time and memory usage, evaluated against the problem's Time Limit and Memory Limit.
- **Reference Data (`Judge0/` folder)**
    - **`languages.json`:** Maps each supported programming language to the Judge0 `language_id` required when submitting code for compilation/execution; this backs the Target Language dropdown on the Problem Submission Page.
    - **`statuses.json`:** Lookup table translating Judge0's numeric status ids (e.g., "Accepted", "Wrong Answer", "Time Limit Exceeded", "Compilation Error") into the human-readable descriptions and result labels shown throughout the Judging History and Detail pages.

## Project Structure

```
prompt-judge/
├── public/          # Apache docroot — entry pages + assets
│   ├── index.php signup.php login.php logout.php
│   ├── problems.php problem.php submit.php
│   ├── history.php judging.php mypage.php
│   ├── board.php post.php post_form.php post_delete.php   # community boards
│   ├── comment_create.php comment_edit.php comment_delete.php
│   ├── notifications.php
│   └── assets/css/style.css, assets/js/app.js
├── src/             # Shared logic (kept OUT of the web root)
│   ├── bootstrap.php db.php auth.php helpers.php layout.php
│   ├── claude.php       # Anthropic Messages API client (cURL)
│   ├── judge0.php       # Judge0 client + language/status lookups
│   ├── community.php    # board categories + post/comment lookups
│   ├── notifications.php# create + read notifications
│   └── pipeline.php     # run_submission(): the full judging pipeline
├── config/
│   ├── config.example.php  # template (committed)
│   └── config.php          # real secrets (gitignored)
├── sql/
│   ├── schema.sql          # tables
│   └── seed.sql            # 2 sample problems + hidden test cases
└── Judge0/          # reference data: languages.json, statuses.json
```

> Implemented so far: the **Core MVP** — authentication, problem list/detail, the
> prompt submission + judging pipeline, and Judging History — plus the
> **community boards** (Notice / Free / Q&A) with comments and per-user
> **notifications** (a bell badge in the header plus a notifications page). The
> dedicated settings page is the one remaining piece from the original spec.

## Getting Started (Local Setup — XAMPP/WAMP)

> **Requirements:** PHP **8.1+** (uses `match`, named arguments, and the `never`
> return type) with the `pdo_mysql` and `curl` extensions enabled, plus MySQL.
> A reachable Judge0 instance and an Anthropic API key are needed to actually judge.

1. **Database.** Create the schema and load sample data:
   ```sh
   mysql -u root -p < sql/schema.sql
   mysql -u root -p prompt_judge < sql/seed.sql
   ```
   (Or import both files through phpMyAdmin.)
2. **Config.** Copy the template and fill in your values:
   ```sh
   cp config/config.example.php config/config.php
   ```
   Set the MySQL credentials, your `ANTHROPIC_API_KEY`, and the Judge0 endpoint
   (self-hosted `http://localhost:2358`, or a RapidAPI host + key).
3. **Serve.** Place the project under XAMPP's `htdocs` and browse to
   `http://localhost/prompt-judge/public/`, or point an Apache vhost docroot at
   the `public/` folder. For a quick local run without Apache:
   ```sh
   php -S localhost:8000 -t public
   ```
4. **Smoke test.** Sign up → log in → open a seeded problem → submit a prompt
   (e.g. *"Read two integers and print their sum."*) → confirm a row appears in
   Judging History with a verdict, token counts, and time/memory. To see
   **ITLE/OTLE**, lower a problem's token limit so the generated code exceeds it.

## Pages & Features

- **Header**
    - **Left Navigation:** Main (Site Name/Logo), Problem List, Judging History, Free Board, Q&A Board, Notices.
    - **Right Navigation:** Dark mode toggle and Login button.
    - **Logged-in State:** Displays a Notification icon, Settings icon, and Username.
        - The Notification icon displays a badge with the number of unread notifications (capped at 9+).
        - Clicking the Username opens a dropdown with "My Page" and "Logout" options.
- **Footer**
    - Copyright (Year 2026), Creator Info, and GitHub Repository link.
- **Main Page (Home)**
    - Introduction to the platform's unique "Prompt-to-Code" concept and a quick guide on how to use the site.
- **Authentication Pages**
    - **Login Page:** Standard ID and Password authentication. Includes a link to Sign-Up.
    - **Sign‑Up Page:** User registration form collecting ID, Password, Name, Email, and Affiliation (University/Company).
- **User Dashboard**
    - **My Page:** Displays user statistics and a list of successfully solved problems.
    - **Settings Page:** UI preferences (Dark/Light mode) and profile visibility toggles.
    - **Notification Page:**
        - Lists alerts received by the user (e.g., replies to their Q&A posts).
        - Visually distinguishes between Read and Unread notifications.
- **Problem Solving Pages**
    - **Problem List Page:**
        - Table displaying Problem ID, Title, Total Submissions, and Accepted Solutions.
        - Search functionality by Problem ID or Title.
    - **Problem Detail Page:**
        - Displays Problem Description, Input/Output formats, Sample Inputs/Outputs.
        - Lists constraints: Time Limit, Memory Limit, and **Token Limit**.
    - **Problem Submission Page:**
        - **AI Configurations:** Model selection, Temperature, and Thinking Budget sliders.
        - **Target Language:** Dropdown to select which programming language the AI should write the solution in (e.g., Python, C++, Java).
        - **Prompt Input:** A large text area for the user to write their instructions to the AI.
- **Judging & Results Pages**
    - **Judging History Page:**
        - Table displaying Judging ID, User ID, Problem ID, Result, Execution Time, Memory Usage, Token Usage, Submission Language, Output Code Size, and Submission Time.
        - Search by User ID or Problem ID. Filter by Judging Result.
        - **Result Labels Examples:**
            - **AC** (Accepted)
            - **WA** (Wrong Answer)
            - **TLE** (Time Limit Exceeded)
            - **MLE** (Memory Limit Exceeded)
            - **ITLE** (Input Token Limit Exceeded)
            - **OTLE** (Output Token Limit Exceeded)
    - **Judging Detail Page:**
        - Displays the user's submitted Prompt, the Claude-generated Source Code, and token usage statistics (`input_tokens`, `output_tokens`).
        - Shows the compiler output or error logs if the submission failed.
- **Community Boards**
    - **Board List Page:**
        - Table displaying Post ID, Category (Notice, Free, Q&A), Related Problem ID (Optional/Nullable), Title, Comment Count, Author, and Date.
        - Search by Title or Author. Filter by Category.
    - **Post Detail Page:**
        - Displays the full post content, title, author, and timestamp.
        - **Comment Section:** Lists comments with author and timestamp.
        - **Comment Interactions:** Logged-in users can write comments. Users can edit or delete their own comments.