<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function clean_text(string $value, int $maxLength): string
{
    $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');
    return function_exists('mb_substr')
        ? mb_substr($value, 0, $maxLength, 'UTF-8')
        : substr($value, 0, $maxLength);
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_failure_redirect_path(string $fallbackPath): string
{
    if ($fallbackPath !== '') {
        return $fallbackPath;
    }

    $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $parts = parse_url($referer);
    $currentHost = (string) ($_SERVER['HTTP_HOST'] ?? '');

    if (!is_array($parts)) {
        return 'index.php';
    }

    $refererHost = (string) ($parts['host'] ?? '');

    if (isset($parts['port'])) {
        $refererHost .= ':' . (string) $parts['port'];
    }

    if ($refererHost === '' || strcasecmp($refererHost, $currentHost) !== 0) {
        return 'index.php';
    }

    $path = trim((string) ($parts['path'] ?? ''), '/');
    $base = trim(BASE_URL, '/');

    if ($base !== '' && str_starts_with($path, $base . '/')) {
        $path = substr($path, strlen($base) + 1);
    }

    if ($path === '' || str_starts_with($path, 'actions/')) {
        return 'index.php';
    }

    return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
}

function verify_csrf(string $fallbackPath = ''): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        unset($_SESSION['csrf_token']);
        flash('warning', 'This form expired for security. Please submit it again.');
        redirect(csrf_failure_redirect_path($fallbackPath));
    }
}

function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false && strlen($email) <= 150;
}

function mask_email(string $email): string
{
    if (!str_contains($email, '@')) {
        return 'registered email';
    }

    [$local, $domain] = explode('@', $email, 2);
    $visible = substr($local, 0, 2);

    return $visible . str_repeat('*', max(3, strlen($local) - 2)) . '@' . $domain;
}

function is_valid_person_name(string $name, int $maxLength = 120): bool
{
    return (bool) preg_match("/^[A-Za-z][A-Za-z .'-]{1," . ($maxLength - 1) . '}$/', $name);
}

function is_valid_roll_number(string $rollNumber): bool
{
    return (bool) preg_match('/^[A-Z0-9-]{3,30}$/', $rollNumber);
}

function is_valid_category_name(string $name): bool
{
    return (bool) preg_match("/^[A-Za-z0-9][A-Za-z0-9 &'().-]{2,119}$/", $name);
}

function allowed_branches(): array
{
    return ['CSE', 'IT', 'ECE', 'EEE', 'ME', 'CE', 'AE'];
}

function password_errors(string $password): array
{
    $errors = [];

    if (strlen($password) < 10) {
        $errors[] = 'at least 10 characters';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'one uppercase letter';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'one lowercase letter';
    }

    if (!preg_match('/\d/', $password)) {
        $errors[] = 'one number';
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'one special character';
    }

    return $errors;
}

function mail_header_value(string $value, int $maxLength = 180): string
{
    return clean_text(str_replace(["\r", "\n"], ' ', $value), $maxLength);
}

function send_mail_message(string $to, string $subject, string $body): bool
{
    if (!is_valid_email($to) || !is_valid_email(MAIL_FROM_ADDRESS)) {
        return false;
    }

    if (MAIL_TRANSPORT === 'mail') {
        $headers = [
            'From: ' . mail_header_value(MAIL_FROM_NAME) . ' <' . MAIL_FROM_ADDRESS . '>',
            'Reply-To: ' . MAIL_FROM_ADDRESS,
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: PHP/' . PHP_VERSION,
        ];

        return mail($to, mail_header_value($subject), $body, implode("\r\n", $headers));
    }

    if (MAIL_TRANSPORT !== 'smtp') {
        return false;
    }

    return send_smtp_message($to, $subject, $body);
}

function smtp_read_response($socket): array
{
    $response = '';
    $code = 0;

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;

        if (preg_match('/^(\d{3})([ -])/', $line, $matches)) {
            $code = (int) $matches[1];

            if ($matches[2] === ' ') {
                break;
            }
        }
    }

    return [$code, $response];
}

function smtp_command($socket, string $command, array $expectedCodes): bool
{
    fwrite($socket, $command . "\r\n");
    [$code] = smtp_read_response($socket);

    return in_array($code, $expectedCodes, true);
}

function smtp_data_body(string $message): string
{
    $message = str_replace(["\r\n", "\r"], "\n", $message);
    $lines = explode("\n", $message);

    foreach ($lines as &$line) {
        if (str_starts_with($line, '.')) {
            $line = '.' . $line;
        }
    }

    return implode("\r\n", $lines);
}

function send_smtp_message(string $to, string $subject, string $body): bool
{
    if (SMTP_HOST === '' || !is_valid_email(MAIL_FROM_ADDRESS)) {
        return false;
    }

    $host = SMTP_HOST;
    $port = SMTP_PORT > 0 ? SMTP_PORT : 587;
    $remote = SMTP_ENCRYPTION === 'ssl' ? 'ssl://' . $host : $host;
    $socket = @fsockopen($remote, $port, $errno, $errstr, 15);

    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, 15);
    [$code] = smtp_read_response($socket);

    if ($code !== 220) {
        fclose($socket);
        return false;
    }

    $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';

    if (!smtp_command($socket, 'EHLO ' . $serverName, [250])) {
        fclose($socket);
        return false;
    }

    if (SMTP_ENCRYPTION === 'tls') {
        if (!smtp_command($socket, 'STARTTLS', [220])) {
            fclose($socket);
            return false;
        }

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            return false;
        }

        if (!smtp_command($socket, 'EHLO ' . $serverName, [250])) {
            fclose($socket);
            return false;
        }
    }

    if (SMTP_USERNAME !== '') {
        if (!smtp_command($socket, 'AUTH LOGIN', [334])
            || !smtp_command($socket, base64_encode(SMTP_USERNAME), [334])
            || !smtp_command($socket, base64_encode(SMTP_PASSWORD), [235])) {
            fclose($socket);
            return false;
        }
    }

    $subject = mail_header_value($subject);
    $fromName = mail_header_value(MAIL_FROM_NAME);
    $headers = [
        'From: ' . $fromName . ' <' . MAIL_FROM_ADDRESS . '>',
        'To: ' . $to,
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ];
    $message = implode("\r\n", $headers) . "\r\n\r\n" . $body;

    $sent = smtp_command($socket, 'MAIL FROM:<' . MAIL_FROM_ADDRESS . '>', [250])
        && smtp_command($socket, 'RCPT TO:<' . $to . '>', [250, 251])
        && smtp_command($socket, 'DATA', [354]);

    if ($sent) {
        fwrite($socket, smtp_data_body($message) . "\r\n.\r\n");
        [$code] = smtp_read_response($socket);
        $sent = $code === 250;
    }

    smtp_command($socket, 'QUIT', [221]);
    fclose($socket);

    return $sent;
}

function selected(string $current, string $expected): string
{
    return $current === $expected ? 'selected' : '';
}

function is_active_status(array $category): bool
{
    return category_state($category) === 'open';
}

function category_state(array $category): string
{
    $now = new DateTimeImmutable('now');
    $startsAt = new DateTimeImmutable($category['starts_at']);
    $endsAt = new DateTimeImmutable($category['ends_at']);

    if ($category['status'] === 'draft') {
        return 'draft';
    }

    if ($category['status'] === 'closed' || $endsAt < $now) {
        return 'closed';
    }

    if ($startsAt > $now) {
        return 'scheduled';
    }

    return 'open';
}

function status_label(string $status): string
{
    return match ($status) {
        'activated', 'active' => 'success',
        'open' => 'success',
        'scheduled' => 'warning',
        'pending', 'draft' => 'warning',
        'deactivated', 'closed', 'inactive' => 'danger',
        default => 'neutral',
    };
}

function initials(string $name): string
{
    $words = preg_split('/\s+/', trim($name));
    $letters = '';

    foreach ($words ?: [] as $word) {
        $letters .= strtoupper(substr($word, 0, 1));
        if (strlen($letters) >= 2) {
            break;
        }
    }

    return $letters ?: 'CV';
}

function format_datetime(?string $value): string
{
    if (!$value) {
        return '-';
    }

    return (new DateTimeImmutable($value))->format('d M Y, h:i A');
}
