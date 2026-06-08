<?php
/**
 * Authentication: registration, login, logout, and session helpers.
 */

declare(strict_types=1);

/**
 * The currently logged-in user row, or null.
 *
 * @return array<string, mixed>|null
 */
function current_user(): ?array
{
    static $cached = false;
    static $user = null;

    if ($cached) {
        return $user;
    }
    $cached = true;

    $id = $_SESSION['user_id'] ?? null;
    if ($id === null) {
        return null;
    }

    $stmt = db_run('SELECT * FROM users WHERE id = ?', [$id]);
    $user = $stmt->fetch() ?: null;
    return $user;
}

/**
 * Require a logged-in user; redirect to login otherwise.
 */
function require_login(): void
{
    if (current_user() === null) {
        flash('Please log in to continue.', 'warn');
        redirect('login.php');
    }
}

/**
 * Attempt to log in. Returns true on success.
 */
function attempt_login(string $username, string $password): bool
{
    $stmt = db_run('SELECT * FROM users WHERE username = ?', [$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    // Prevent session fixation.
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    return true;
}

/**
 * Register a new user. Returns [success, errorMessage].
 *
 * @return array{0:bool,1:string}
 */
function register_user(
    string $username,
    string $password,
    string $name,
    string $email,
    string $affiliation
): array {
    if ($username === '' || $password === '' || $name === '' || $email === '') {
        return [false, 'Username, password, name, and email are required.'];
    }
    if (strlen($password) < 6) {
        return [false, 'Password must be at least 6 characters.'];
    }

    $exists = db_run('SELECT 1 FROM users WHERE username = ?', [$username])->fetch();
    if ($exists) {
        return [false, 'That username is already taken.'];
    }

    db_run(
        'INSERT INTO users (username, password_hash, name, email, affiliation)
         VALUES (?, ?, ?, ?, ?)',
        [
            $username,
            password_hash($password, PASSWORD_DEFAULT),
            $name,
            $email,
            $affiliation !== '' ? $affiliation : null,
        ]
    );

    return [true, ''];
}

/**
 * Log out the current user.
 */
function logout(): void
{
    $_SESSION = [];
    session_destroy();
}
