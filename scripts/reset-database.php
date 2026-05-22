<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script deve ser executado pelo terminal.\n");
    exit(1);
}

$root = dirname(__DIR__);

require $root . '/app/helpers.php';
require $root . '/app/config.php';
require $root . '/app/db.php';
require $root . '/app/schema.php';

$args = array_slice($argv, 1);
$confirmed = in_array('--yes', $args, true);
$withDemo = in_array('--demo', $args, true);

if (!$confirmed) {
    fwrite(STDERR, "Isto apaga todos os dados do banco configurado no .env.\n");
    fwrite(STDERR, "Use: php scripts/reset-database.php --yes\n");
    fwrite(STDERR, "Opcional: adicione --demo para inserir os dados demonstrativos iniciais.\n");
    exit(1);
}

$config = app_config();

if ($config['missing'] !== []) {
    fwrite(STDERR, "Variaveis ausentes no .env: " . implode(', ', $config['missing']) . "\n");
    exit(1);
}

$database = $config['db']['database'];

if ($database === '') {
    fwrite(STDERR, "MYSQL_DATABASE esta vazio.\n");
    exit(1);
}

$pdo = db_connect($config);

try {
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    foreach ([
        'document_files',
        'tasks',
        'board_columns',
        'boards',
        'documents',
        'users',
    ] as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
} catch (Throwable $exception) {
    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    } catch (Throwable) {
    }

    fwrite(STDERR, "Falha ao limpar o banco: " . $exception->getMessage() . "\n");
    exit(1);
}

initialize_schema($pdo);

if ($withDemo) {
    seed_default_content($pdo);
}

fwrite(STDOUT, "Banco '$database' zerado com sucesso.\n");
fwrite(STDOUT, "Abra a aplicacao para refazer o setup do administrador.\n");

if (!$withDemo) {
    fwrite(STDOUT, "Observacao: a aplicacao pode recriar dados demonstrativos ao detectar que ainda nao ha usuarios.\n");
}
