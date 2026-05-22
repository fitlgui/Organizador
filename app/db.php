<?php
declare(strict_types=1);

function db_connect(array $config): PDO
{
    $db = $config['db'];
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $db['host'],
        $db['database']
    );

    return new PDO($dsn, $db['user'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
