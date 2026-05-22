<?php
declare(strict_types=1);

function initialize_schema(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(40) NOT NULL DEFAULT 'admin',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS documents (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(190) NOT NULL,
            content LONGTEXT NULL,
            author_id INT UNSIGNED NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_documents_author (author_id),
            INDEX idx_documents_updated (updated_at, id),
            FULLTEXT INDEX idx_documents_fulltext (title, content),
            CONSTRAINT fk_documents_author
                FOREIGN KEY (author_id) REFERENCES users(id)
                ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS document_files (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            document_id INT UNSIGNED NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            mime_type VARCHAR(190) NOT NULL DEFAULT 'application/octet-stream',
            size_bytes INT UNSIGNED NOT NULL DEFAULT 0,
            data LONGBLOB NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_document_files_document (document_id),
            INDEX idx_document_files_size (size_bytes),
            CONSTRAINT fk_document_files_document
                FOREIGN KEY (document_id) REFERENCES documents(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS boards (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(190) NOT NULL,
            created_by INT UNSIGNED NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_boards_creator (created_by),
            INDEX idx_boards_updated (updated_at, id),
            CONSTRAINT fk_boards_creator
                FOREIGN KEY (created_by) REFERENCES users(id)
                ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS board_columns (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            board_id INT UNSIGNED NOT NULL,
            title VARCHAR(90) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            INDEX idx_columns_board (board_id),
            INDEX idx_columns_board_sort (board_id, sort_order, id),
            INDEX idx_columns_title_id (title, id),
            CONSTRAINT fk_columns_board
                FOREIGN KEY (board_id) REFERENCES boards(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tasks (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            board_id INT UNSIGNED NOT NULL,
            column_id INT UNSIGNED NOT NULL,
            title VARCHAR(190) NOT NULL,
            assignee_initials VARCHAR(8) NULL,
            status_label VARCHAR(40) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tasks_board (board_id),
            INDEX idx_tasks_column (column_id),
            INDEX idx_tasks_board_sort (board_id, sort_order, id),
            INDEX idx_tasks_column_sort (column_id, sort_order, id),
            CONSTRAINT fk_tasks_board
                FOREIGN KEY (board_id) REFERENCES boards(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_tasks_column
                FOREIGN KEY (column_id) REFERENCES board_columns(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    ensure_index($pdo, 'documents', 'idx_documents_updated', 'ALTER TABLE documents ADD INDEX idx_documents_updated (updated_at, id)');
    ensure_index($pdo, 'documents', 'idx_documents_fulltext', 'ALTER TABLE documents ADD FULLTEXT INDEX idx_documents_fulltext (title, content)');
    ensure_index($pdo, 'document_files', 'idx_document_files_size', 'ALTER TABLE document_files ADD INDEX idx_document_files_size (size_bytes)');
    ensure_index($pdo, 'boards', 'idx_boards_updated', 'ALTER TABLE boards ADD INDEX idx_boards_updated (updated_at, id)');
    ensure_index($pdo, 'board_columns', 'idx_columns_board_sort', 'ALTER TABLE board_columns ADD INDEX idx_columns_board_sort (board_id, sort_order, id)');
    ensure_index($pdo, 'board_columns', 'idx_columns_title_id', 'ALTER TABLE board_columns ADD INDEX idx_columns_title_id (title, id)');
    ensure_index($pdo, 'tasks', 'idx_tasks_board_sort', 'ALTER TABLE tasks ADD INDEX idx_tasks_board_sort (board_id, sort_order, id)');
    ensure_index($pdo, 'tasks', 'idx_tasks_column_sort', 'ALTER TABLE tasks ADD INDEX idx_tasks_column_sort (column_id, sort_order, id)');
}

function schema_ready(PDO $pdo): bool
{
    try {
        $pdo->query('SELECT 1 FROM users LIMIT 1');
        return true;
    } catch (Throwable $exception) {
        return false;
    }
}

function ensure_index(PDO $pdo, string $table, string $indexName, string $sql): void
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = ?
          AND index_name = ?
    ");
    $stmt->execute([$table, $indexName]);

    if ((int) $stmt->fetchColumn() === 0) {
        $pdo->exec($sql);
    }
}

function seed_default_content(PDO $pdo): void
{
    if (!(bool) $pdo->query('SELECT EXISTS(SELECT 1 FROM documents LIMIT 1)')->fetchColumn()) {
        $stmt = $pdo->prepare('INSERT INTO documents (title, content, author_id) VALUES (?, ?, NULL)');
        $stmt->execute([
            'Ata de Reunião - Chassi',
            'Nesta reunião, a equipe decidiu utilizar motores NEO para a tração e montar o chassi em formato U para facilitar a coleta das peças no campo.',
        ]);
        $stmt->execute([
            'Lógica da Câmera (Autônomo)',
            'A câmera será posicionada a 30cm do chão, angulada em 15 graus para cima. O código vai usar a biblioteca PhotonVision para detectar as AprilTags e alinhar o robô automaticamente.',
        ]);
        $stmt->execute([
            'Lista de Peças do Robô',
            "- 4x Motores NEO\n- 4x Spark Max\n- 1x RoboRIO 2.0\n- Perfis de alumínio 2x1",
        ]);
    }

    if (!(bool) $pdo->query('SELECT EXISTS(SELECT 1 FROM boards LIMIT 1)')->fetchColumn()) {
        $pdo->beginTransaction();

        $pdo->prepare('INSERT INTO boards (title, created_by) VALUES (?, NULL)')
            ->execute(['Engenharia - Temporada 2026']);
        $boardId = (int) $pdo->lastInsertId();

        $columnStmt = $pdo->prepare('INSERT INTO board_columns (board_id, title, sort_order) VALUES (?, ?, ?)');
        $columns = [
            ['A Fazer', 10],
            ['Fazendo', 20],
            ['Concluído', 30],
        ];
        $columnIds = [];

        foreach ($columns as [$title, $order]) {
            $columnStmt->execute([$boardId, $title, $order]);
            $columnIds[$title] = (int) $pdo->lastInsertId();
        }

        $taskStmt = $pdo->prepare(
            'INSERT INTO tasks (board_id, column_id, title, assignee_initials, status_label, sort_order) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $taskStmt->execute([$boardId, $columnIds['A Fazer'], 'Cortar perfis de alumínio', null, '10 Mai', 10]);
        $taskStmt->execute([$boardId, $columnIds['A Fazer'], 'Configurar controle do Xbox', null, '12 Mai', 20]);
        $taskStmt->execute([$boardId, $columnIds['Fazendo'], 'Montar caixa de redução', 'JF', 'Hoje', 10]);

        $pdo->commit();
    }
}
