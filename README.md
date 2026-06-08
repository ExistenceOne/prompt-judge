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
        - **AI Configurations:** Model selection, Temperature, and Top-p sliders.
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