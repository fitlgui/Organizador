<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function user_count(PDO $pdo): int
{
    return (int) $pdo->query('SELECT EXISTS(SELECT 1 FROM users LIMIT 1)')->fetchColumn();
}

function current_user(PDO $pdo): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
    $stmt->execute([(int) $_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function require_auth(PDO $pdo): array
{
    $user = current_user($pdo);

    if ($user === null) {
        redirect_to('login');
    }

    return $user;
}

function login_user(PDO $pdo, string $email, string $password): bool
{
    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $stmt->execute([strtolower(trim($email))]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];

    return true;
}

function create_admin_user(PDO $pdo, string $name, string $email, string $password): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([
        trim($name),
        strtolower(trim($email)),
        password_hash($password, PASSWORD_DEFAULT),
        'admin',
    ]);

    return (int) $pdo->lastInsertId();
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
