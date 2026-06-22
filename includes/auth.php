<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    $statement = db()->prepare('SELECT * FROM admins WHERE id = ?');
    $statement->execute([$_SESSION['admin_id']]);
    $admin = $statement->fetch();

    return $admin ?: null;
}

function current_student(): ?array
{
    if (empty($_SESSION['student_id'])) {
        return null;
    }

    $statement = db()->prepare('SELECT * FROM students WHERE id = ?');
    $statement->execute([$_SESSION['student_id']]);
    $student = $statement->fetch();

    return $student ?: null;
}

function require_admin(): array
{
    $admin = current_admin();

    if (!$admin) {
        flash('warning', 'Please login as admin.');
        redirect('admin/login.php');
    }

    return $admin;
}

function pending_admin(): ?array
{
    $adminId = $_SESSION['pending_admin_id'] ?? null;
    $startedAt = (int) ($_SESSION['pending_admin_started_at'] ?? 0);

    if (!$adminId || time() - $startedAt > 600) {
        unset($_SESSION['pending_admin_id'], $_SESSION['pending_admin_started_at']);
        return null;
    }

    $statement = db()->prepare('SELECT * FROM admins WHERE id = ?');
    $statement->execute([(int) $adminId]);
    $admin = $statement->fetch();

    return $admin ?: null;
}

function require_student(): array
{
    $student = current_student();

    if (!$student) {
        flash('warning', 'Please login as student.');
        redirect('student/login.php');
    }

    if ($student['status'] !== 'activated') {
        unset($_SESSION['student_id']);
        flash('warning', 'Your account is waiting for admin activation.');
        redirect('student/login.php');
    }

    return $student;
}

function verify_admin_credentials(string $email, string $password): ?array
{
    $statement = db()->prepare('SELECT * FROM admins WHERE email = ?');
    $statement->execute([$email]);
    $admin = $statement->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        return $admin;
    }

    return null;
}

function begin_admin_email_verification(array $admin): bool
{
    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = (new DateTimeImmutable('+' . EMAIL_CODE_TTL_MINUTES . ' minutes'))->format('Y-m-d H:i:s');
    $subject = APP_NAME . ' admin verification code';
    $body = "Hello " . $admin['name'] . ",\n\n"
        . "Your " . APP_NAME . " admin verification code is: " . $code . "\n\n"
        . "This code expires in " . EMAIL_CODE_TTL_MINUTES . " minutes. If you did not try to login, change the admin password and review audit logs.\n";

    $statement = db()->prepare(
        'UPDATE admins
         SET email_code_hash = ?, email_code_expires_at = ?, email_code_sent_at = NOW()
         WHERE id = ?'
    );
    $statement->execute([
        password_hash($code, PASSWORD_DEFAULT),
        $expiresAt,
        (int) $admin['id'],
    ]);

    if (!send_mail_message((string) $admin['email'], $subject, $body)) {
        clear_admin_email_code((int) $admin['id']);
        log_activity('admin', (int) $admin['id'], 'admin_email_code_failed', 'Admin password verified, but email verification code could not be sent.');
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['pending_admin_id'] = (int) $admin['id'];
    $_SESSION['pending_admin_started_at'] = time();
    unset($_SESSION['admin_id'], $_SESSION['student_id']);
    log_activity('admin', (int) $admin['id'], 'admin_password_verified', 'Admin password verified; email verification code sent.');

    return true;
}

function verify_admin_email_code(array $admin, string $code): bool
{
    $code = preg_replace('/\D/', '', $code) ?? '';
    $hash = (string) ($admin['email_code_hash'] ?? '');
    $expiresAt = (string) ($admin['email_code_expires_at'] ?? '');

    if (strlen($code) !== 6 || $hash === '' || $expiresAt === '') {
        return false;
    }

    if (new DateTimeImmutable($expiresAt) < new DateTimeImmutable('now')) {
        clear_admin_email_code((int) $admin['id']);
        return false;
    }

    return password_verify($code, $hash);
}

function clear_admin_email_code(int $adminId): void
{
    $statement = db()->prepare(
        'UPDATE admins
         SET email_code_hash = NULL, email_code_expires_at = NULL, email_code_sent_at = NULL
         WHERE id = ?'
    );
    $statement->execute([$adminId]);
}

function complete_admin_login(array $admin): void
{
    clear_admin_email_code((int) $admin['id']);
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    unset($_SESSION['student_id'], $_SESSION['pending_admin_id'], $_SESSION['pending_admin_started_at']);
    log_activity('admin', (int) $admin['id'], 'admin_login', 'Admin signed in with email verification.');
}

function login_student(string $rollNumber, string $password): string
{
    $statement = db()->prepare('SELECT * FROM students WHERE roll_number = ?');
    $statement->execute([$rollNumber]);
    $student = $statement->fetch();

    if (!$student || !password_verify($password, $student['password_hash'])) {
        return 'invalid';
    }

    if ($student['status'] !== 'activated') {
        return $student['status'];
    }

    session_regenerate_id(true);
    $_SESSION['student_id'] = (int) $student['id'];
    unset($_SESSION['admin_id']);
    log_activity('student', (int) $student['id'], 'student_login', 'Student signed in.');

    return 'ok';
}

function too_many_login_attempts(string $attemptType, string $identifier, int $maxAttempts = 5, int $minutes = 15): bool
{
    $statement = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE attempt_type = ?
           AND identifier = ?
           AND ip_address = ?
           AND successful = 0
           AND created_at >= (NOW() - INTERVAL ' . (int) $minutes . ' MINUTE)'
    );
    $statement->execute([$attemptType, strtolower($identifier), client_ip()]);

    return (int) $statement->fetchColumn() >= $maxAttempts;
}

function record_login_attempt(string $attemptType, string $identifier, bool $successful): void
{
    try {
        $statement = db()->prepare(
            'INSERT INTO login_attempts (attempt_type, identifier, ip_address, successful)
             VALUES (?, ?, ?, ?)'
        );
        $statement->execute([$attemptType, strtolower($identifier), client_ip(), $successful ? 1 : 0]);

        if ($successful) {
            $cleanup = db()->prepare(
                'DELETE FROM login_attempts
                 WHERE attempt_type = ? AND identifier = ? AND ip_address = ? AND successful = 0'
            );
            $cleanup->execute([$attemptType, strtolower($identifier), client_ip()]);
        }
    } catch (Throwable $throwable) {
        // Login attempt logging should not break the site.
    }
}

function logout_user(): void
{
    $actorType = isset($_SESSION['admin_id']) ? 'admin' : (isset($_SESSION['student_id']) ? 'student' : 'system');
    $actorId = $_SESSION['admin_id'] ?? $_SESSION['student_id'] ?? null;

    if ($actorId !== null) {
        log_activity($actorType, (int) $actorId, 'logout', 'User signed out.');
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function log_activity(string $actorType, ?int $actorId, string $action, ?string $details = null): void
{
    try {
        $statement = db()->prepare('INSERT INTO audit_logs (actor_type, actor_id, action, details) VALUES (?, ?, ?, ?)');
        $statement->execute([$actorType, $actorId, $action, $details]);
    } catch (Throwable $throwable) {
        // Logging should never block the main voting flow.
    }
}
