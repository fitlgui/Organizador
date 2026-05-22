<?php
declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function route_url(string $page = 'home', array $params = []): string
{
    $query = array_merge(['page' => $page], $params);
    return 'index.php?' . http_build_query($query);
}

function redirect_to(string $page, array $params = []): void
{
    header('Location: ' . route_url($page, $params));
    exit;
}

function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $letters = '';

    foreach ($parts as $part) {
        if ($part !== '') {
            $letters .= substr($part, 0, 1);
        }

        if (strlen($letters) >= 2) {
            break;
        }
    }

    return strtoupper($letters !== '' ? $letters : 'TT');
}

function render_view(string $view, array $params = []): void
{
    extract($params, EXTR_SKIP);

    ob_start();
    require __DIR__ . '/../views/' . $view . '.php';
    $content = ob_get_clean();

    require __DIR__ . '/../views/layout.php';
}
