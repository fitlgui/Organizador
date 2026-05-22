<?php
declare(strict_types=1);

function read_env_file(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $values = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines ?: [] as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $values[$key] = $value;
    }

    return $values;
}

function app_config(): array
{
    $fileValues = read_env_file(__DIR__ . '/../.env');
    $required = ['MYSQL_DATABASE', 'MYSQL_USER', 'MYSQL_PASSWORD', 'MYSQL_HOST'];
    $config = [];
    $missing = [];

    foreach ($required as $key) {
        $value = $fileValues[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            $missing[] = $key;
        }

        $config[$key] = (string) $value;
    }

    return [
        'db' => [
            'database' => $config['MYSQL_DATABASE'],
            'user' => $config['MYSQL_USER'],
            'password' => $config['MYSQL_PASSWORD'],
            'host' => $config['MYSQL_HOST'],
        ],
        'missing' => $missing,
    ];
}
