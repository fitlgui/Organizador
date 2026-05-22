<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $staticFile = realpath(__DIR__ . $requestPath);
    $projectRoot = realpath(__DIR__);
    $assetExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico', 'woff', 'woff2', 'ttf'];
    $extension = strtolower(pathinfo($requestPath, PATHINFO_EXTENSION));

    if (
        $requestPath !== '/' &&
        in_array($extension, $assetExtensions, true) &&
        $staticFile !== false &&
        $projectRoot !== false &&
        str_starts_with($staticFile, $projectRoot) &&
        is_file($staticFile)
    ) {
        return false;
    }
}

require __DIR__ . '/app/helpers.php';
require __DIR__ . '/app/config.php';
require __DIR__ . '/app/db.php';
require __DIR__ . '/app/schema.php';
require __DIR__ . '/app/auth.php';

$config = app_config();
$page = $_GET['page'] ?? 'home';

if ($config['missing'] !== []) {
    render_view('config-error', [
        'pageTitle' => 'Configuração necessária',
        'minimal' => true,
        'missing' => $config['missing'],
    ]);
    exit;
}

try {
    $pdo = db_connect($config);

    initialize_schema($pdo);

    $hasUsers = user_count($pdo) > 0;

    if (!$hasUsers) {
        seed_default_content($pdo);
    }
} catch (Throwable $exception) {
    render_view('config-error', [
        'pageTitle' => 'Erro de conexão',
        'minimal' => true,
        'errorMessage' => $exception->getMessage(),
        'missing' => [],
    ]);
    exit;
}

if (!$hasUsers && $page !== 'setup') {
    redirect_to('setup');
}

if ($page === 'setup') {
    handle_setup($pdo);
}

if ($page === 'login') {
    handle_login($pdo);
}

if ($page === 'logout') {
    logout_user();
    redirect_to('login');
}

if ($page === 'download') {
    handle_download($pdo);
}

if ($page === 'api') {
    handle_api($pdo);
}

$user = require_auth($pdo);

switch ($page) {
    case 'home':
        handle_home($pdo, $user);
        break;
    case 'documentos':
        handle_documents($pdo, $user);
        break;
    case 'projetos':
        handle_projects($pdo, $user);
        break;
    case 'configuracoes':
        handle_settings($pdo, $user);
        break;
    default:
        handle_not_found($user);
}

function handle_setup(PDO $pdo): void
{
    $errors = [];
    $hasUsers = user_count($pdo) > 0;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$hasUsers) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '') {
            $errors[] = 'Informe o nome do administrador.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informe um e-mail válido.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Use uma senha com pelo menos 8 caracteres.';
        }

        if ($errors === []) {
            $_SESSION['user_id'] = create_admin_user($pdo, $name, $email, $password);
            redirect_to('home');
        }
    }

    render_view('setup', [
        'pageTitle' => 'Setup inicial',
        'minimal' => true,
        'hasUsers' => $hasUsers,
        'errors' => $errors,
    ]);
    exit;
}

function handle_login(PDO $pdo): void
{
    if (current_user($pdo) !== null) {
        redirect_to('home');
    }

    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if (login_user($pdo, $email, $password)) {
            redirect_to('home');
        }

        $error = 'E-mail ou senha inválidos.';
    }

    render_view('login', [
        'pageTitle' => 'Entrar',
        'minimal' => true,
        'error' => $error,
    ]);
    exit;
}

function handle_home(PDO $pdo, array $user): void
{
    $stats = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM documents) AS documents,
            (SELECT COUNT(*) FROM boards) AS boards,
            (SELECT COUNT(*) FROM tasks) AS tasks,
            (
                SELECT COUNT(*)
                FROM tasks t
                INNER JOIN board_columns c ON c.id = t.column_id
                WHERE c.title = 'Concluído'
            ) AS done
    ")->fetch();

    render_view('home', [
        'pageTitle' => 'Dashboard',
        'activePage' => 'home',
        'user' => $user,
        'stats' => array_map('intval', $stats ?: []),
    ]);
    exit;
}

function handle_documents(PDO $pdo, array $user): void
{
    $notice = null;
    $noticeType = 'success';
    $maxUploadBytes = max_upload_bytes();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int) ($_POST['document_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $uploadedFile = $_FILES['attachment'] ?? null;
        $hasUploadedFile = is_array($uploadedFile) && ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if ($title === '') {
            $notice = 'Informe um título para salvar o documento.';
            $noticeType = 'error';
        } elseif ($hasUploadedFile && ($uploadedFile['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $notice = upload_error_message((int) $uploadedFile['error']);
            $noticeType = 'error';
        } elseif ($hasUploadedFile && (int) ($uploadedFile['size'] ?? 0) > $maxUploadBytes) {
            $notice = 'O arquivo precisa ter no maximo 5 MB.';
            $noticeType = 'error';
        } elseif ($id > 0) {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('UPDATE documents SET title = ?, content = ?, author_id = ? WHERE id = ?');
            $stmt->execute([$title, $content, (int) $user['id'], $id]);

            if ($hasUploadedFile) {
                save_document_file($pdo, $id, $uploadedFile);
            }

            $pdo->commit();
            $notice = 'Documento atualizado com sucesso.';
        } else {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO documents (title, content, author_id) VALUES (?, ?, ?)');
            $stmt->execute([$title, $content, (int) $user['id']]);
            $documentId = (int) $pdo->lastInsertId();

            if ($hasUploadedFile) {
                save_document_file($pdo, $documentId, $uploadedFile);
            }

            $pdo->commit();
            $notice = 'Documento criado com sucesso.';
        }
    }

    $search = trim($_GET['q'] ?? '');
    $params = [];
    $sql = "
        SELECT
            d.id,
            d.title,
            d.content,
            d.updated_at,
            COALESCE(u.name, 'Tuiutech') AS author_name,
            f.id AS file_id,
            f.original_name AS file_name,
            f.size_bytes AS file_size
        FROM documents d
        LEFT JOIN users u ON u.id = d.author_id
        LEFT JOIN document_files f ON f.document_id = d.id
    ";

    if ($search !== '') {
        $fullTextQuery = build_fulltext_query($search);

        if ($fullTextQuery !== '') {
            $sql .= ' WHERE MATCH(d.title, d.content) AGAINST (? IN BOOLEAN MODE)';
            $params = [$fullTextQuery];
        } else {
            $sql .= ' WHERE d.title LIKE ? OR d.content LIKE ?';
            $term = '%' . $search . '%';
            $params = [$term, $term];
        }
    }

    $sql .= ' ORDER BY d.updated_at DESC, d.id DESC LIMIT 200';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    render_view('documentos', [
        'pageTitle' => 'Documentos',
        'activePage' => 'documentos',
        'user' => $user,
        'documents' => $stmt->fetchAll(),
        'search' => $search,
        'notice' => $notice,
        'noticeType' => $noticeType,
        'maxUploadBytes' => $maxUploadBytes,
    ]);
    exit;
}

function handle_projects(PDO $pdo, array $user): void
{
    render_view('projetos', [
        'pageTitle' => 'Projetos',
        'activePage' => 'projetos',
        'user' => $user,
        'boards' => load_boards($pdo),
    ]);
    exit;
}

function handle_settings(PDO $pdo, array $user): void
{
    render_view('configuracoes', [
        'pageTitle' => 'Configuracoes',
        'activePage' => 'configuracoes',
        'user' => $user,
        'storage' => load_storage_summary($pdo),
    ]);
    exit;
}

function build_fulltext_query(string $search): string
{
    $search = preg_replace('/[+\-<>()~*"@]+/', ' ', $search) ?? '';
    $words = preg_split('/\s+/', trim($search)) ?: [];
    $terms = [];

    foreach ($words as $word) {
        if (strlen($word) >= 4) {
            $terms[] = '+' . $word . '*';
        }
    }

    return implode(' ', $terms);
}

function handle_not_found(array $user): void
{
    render_view('not-found', [
        'pageTitle' => 'Página não encontrada',
        'activePage' => '',
        'user' => $user,
    ]);
    exit;
}

function handle_api(PDO $pdo): void
{
    require_auth($pdo);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['ok' => false, 'message' => 'Método não permitido.'], 405);
    }

    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'create_board':
                api_create_board($pdo);
                break;
            case 'update_board':
                api_update_board($pdo);
                break;
            case 'create_task':
                api_create_task($pdo);
                break;
            case 'move_task':
                api_move_task($pdo);
                break;
            case 'update_task':
                api_update_task($pdo);
                break;
            case 'delete_task':
                api_delete_task($pdo);
                break;
            case 'delete_document':
                api_delete_document($pdo);
                break;
            case 'delete_board':
                api_delete_board($pdo);
                break;
            default:
                json_response(['ok' => false, 'message' => 'Ação inválida.'], 400);
        }
    } catch (Throwable $exception) {
        json_response(['ok' => false, 'message' => $exception->getMessage()], 500);
    }
}

function api_create_board(PDO $pdo): void
{
    $title = trim($_POST['title'] ?? '');

    if ($title === '') {
        json_response(['ok' => false, 'message' => 'Informe o nome do quadro.'], 422);
    }

    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO boards (title, created_by) VALUES (?, ?)');
    $stmt->execute([$title, $userId]);
    $boardId = (int) $pdo->lastInsertId();

    $columnStmt = $pdo->prepare('INSERT INTO board_columns (board_id, title, sort_order) VALUES (?, ?, ?)');
    $columns = [];

    foreach ([['A Fazer', 10], ['Fazendo', 20], ['Concluído', 30]] as [$columnTitle, $order]) {
        $columnStmt->execute([$boardId, $columnTitle, $order]);
        $columns[] = [
            'id' => (int) $pdo->lastInsertId(),
            'board_id' => $boardId,
            'title' => $columnTitle,
            'sort_order' => $order,
            'tasks' => [],
        ];
    }

    $pdo->commit();

    json_response([
        'ok' => true,
        'board' => [
            'id' => $boardId,
            'title' => $title,
            'created_by' => $userId,
            'columns' => $columns,
        ],
    ]);
}

function api_update_board(PDO $pdo): void
{
    $boardId = (int) ($_POST['board_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');

    if ($boardId <= 0 || $title === '') {
        json_response(['ok' => false, 'message' => 'Informe o nome do quadro.'], 422);
    }

    $stmt = $pdo->prepare('UPDATE boards SET title = ? WHERE id = ?');
    $stmt->execute([$title, $boardId]);

    json_response([
        'ok' => true,
        'board' => [
            'id' => $boardId,
            'title' => $title,
        ],
    ]);
}

function api_create_task(PDO $pdo): void
{
    $boardId = (int) ($_POST['board_id'] ?? 0);
    $columnId = (int) ($_POST['column_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');

    if ($boardId <= 0 || $columnId <= 0 || $title === '') {
        json_response(['ok' => false, 'message' => 'Dados da tarefa incompletos.'], 422);
    }

    $stmt = $pdo->prepare("
        SELECT c.id, COALESCE(MAX(t.sort_order), 0) + 10 AS next_sort_order
        FROM board_columns c
        LEFT JOIN tasks t ON t.column_id = c.id
        WHERE c.id = ? AND c.board_id = ?
        GROUP BY c.id
    ");
    $stmt->execute([$columnId, $boardId]);
    $column = $stmt->fetch();

    if (!$column) {
        json_response(['ok' => false, 'message' => 'Coluna inválida.'], 422);
    }


    $sortOrder = (int) $column['next_sort_order'];

    $stmt = $pdo->prepare(
        'INSERT INTO tasks (board_id, column_id, title, status_label, sort_order) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$boardId, $columnId, $title, 'Novo', $sortOrder]);

    json_response([
        'ok' => true,
        'task' => [
            'id' => (int) $pdo->lastInsertId(),
            'board_id' => $boardId,
            'column_id' => $columnId,
            'title' => $title,
            'assignee_initials' => null,
            'status_label' => 'Novo',
            'sort_order' => $sortOrder,
        ],
    ]);
}

function api_move_task(PDO $pdo): void
{
    $taskId = (int) ($_POST['task_id'] ?? 0);
    $columnId = (int) ($_POST['column_id'] ?? 0);
    $assignee = trim($_POST['assignee_initials'] ?? '');
    $taskOrder = array_values(array_filter(array_map('intval', $_POST['task_order'] ?? []), static fn (int $id): bool => $id > 0));

    if ($taskId <= 0 || $columnId <= 0 || $taskOrder === []) {
        json_response(['ok' => false, 'message' => 'Dados de movimentação incompletos.'], 422);
    }

    $stmt = $pdo->prepare("
        SELECT c.board_id, c.title
        FROM board_columns c
        WHERE c.id = ?
    ");
    $stmt->execute([$columnId]);
    $column = $stmt->fetch();

    if (!$column) {
        json_response(['ok' => false, 'message' => 'Coluna inválida.'], 422);
    }

    $boardId = (int) $column['board_id'];
    $columnTitle = (string) $column['title'];
    $statusByColumn = [
        'A Fazer' => 'Em aberto',
        'Fazendo' => 'Em andamento',
        'Concluído' => 'Finalizado',
    ];
    $status = $statusByColumn[$columnTitle] ?? 'Em aberto';
    $assignee = normalize_assignee($assignee);

    if (!in_array($taskId, $taskOrder, true)) {
        $taskOrder[] = $taskId;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'UPDATE tasks SET column_id = ?, board_id = ?, assignee_initials = COALESCE(?, assignee_initials), status_label = ? WHERE id = ?'
    );
    $stmt->execute([$columnId, $boardId, $assignee, $status, $taskId]);

    persist_task_order($pdo, $columnId, $taskOrder);
    $pdo->commit();

    json_response([
        'ok' => true,
        'task' => [
            'id' => $taskId,
            'board_id' => $boardId,
            'column_id' => $columnId,
            'assignee_initials' => $assignee,
            'status_label' => $status,
        ],
    ]);
}

function api_update_task(PDO $pdo): void
{
    $taskId = (int) ($_POST['task_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $assignee = normalize_assignee($_POST['assignee_initials'] ?? '');

    if ($taskId <= 0 || $title === '') {
        json_response(['ok' => false, 'message' => 'Informe o titulo da tarefa.'], 422);
    }

    $stmt = $pdo->prepare('UPDATE tasks SET title = ?, assignee_initials = ? WHERE id = ?');
    $stmt->execute([$title, $assignee, $taskId]);

    json_response([
        'ok' => true,
        'task' => [
            'id' => $taskId,
            'title' => $title,
            'assignee_initials' => $assignee,
        ],
    ]);
}

function api_delete_task(PDO $pdo): void
{
    $taskId = (int) ($_POST['task_id'] ?? 0);

    if ($taskId <= 0) {
        json_response(['ok' => false, 'message' => 'Tarefa invalida.'], 422);
    }

    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
    $stmt->execute([$taskId]);

    json_response(['ok' => true]);
}

function api_delete_document(PDO $pdo): void
{
    $documentId = (int) ($_POST['document_id'] ?? 0);

    if ($documentId <= 0) {
        json_response(['ok' => false, 'message' => 'Documento invalido.'], 422);
    }

    $stmt = $pdo->prepare('DELETE FROM documents WHERE id = ?');
    $stmt->execute([$documentId]);

    json_response(['ok' => true]);
}

function api_delete_board(PDO $pdo): void
{
    $boardId = (int) ($_POST['board_id'] ?? 0);

    if ($boardId <= 0) {
        json_response(['ok' => false, 'message' => 'Quadro inválido.'], 422);
    }

    $stmt = $pdo->prepare('DELETE FROM boards WHERE id = ?');
    $stmt->execute([$boardId]);

    json_response(['ok' => true]);
}

function load_boards(PDO $pdo): array
{
    $boards = $pdo->query('SELECT id, title, created_by, created_at, updated_at FROM boards ORDER BY updated_at DESC, id DESC')->fetchAll();

    if ($boards === []) {
        return [];
    }

    $boardIds = array_map(static fn (array $board): int => (int) $board['id'], $boards);
    $placeholders = implode(',', array_fill(0, count($boardIds), '?'));

    $columnsStmt = $pdo->prepare("
        SELECT id, board_id, title, sort_order
        FROM board_columns
        WHERE board_id IN ($placeholders)
        ORDER BY board_id ASC, sort_order ASC, id ASC
    ");
    $columnsStmt->execute($boardIds);
    $columns = $columnsStmt->fetchAll();

    $tasksStmt = $pdo->prepare("
        SELECT id, board_id, column_id, title, assignee_initials, status_label, sort_order, created_at, updated_at
        FROM tasks
        WHERE board_id IN ($placeholders)
        ORDER BY board_id ASC, column_id ASC, sort_order ASC, id ASC
    ");
    $tasksStmt->execute($boardIds);
    $tasks = $tasksStmt->fetchAll();

    return hydrate_boards($boards, $columns, $tasks);
}

function hydrate_boards(array $boards, array $columns, array $tasks): array
{
    $boardsById = [];

    foreach ($boards as $board) {
        $board['columns'] = [];
        $boardsById[(int) $board['id']] = $board;
    }

    $columnsById = [];

    foreach ($columns as $column) {
        $column['tasks'] = [];
        $columnId = (int) $column['id'];
        $boardId = (int) $column['board_id'];
        $columnsById[$columnId] = $column;
        $boardsById[$boardId]['columns'][] = $columnId;
    }

    foreach ($tasks as $task) {
        $columnId = (int) $task['column_id'];

        if (isset($columnsById[$columnId])) {
            $columnsById[$columnId]['tasks'][] = $task;
        }
    }

    foreach ($boardsById as &$board) {
        $hydratedColumns = [];

        foreach ($board['columns'] as $columnId) {
            $hydratedColumns[] = $columnsById[$columnId];
        }

        $board['columns'] = $hydratedColumns;
    }

    return array_values($boardsById);
}

function persist_task_order(PDO $pdo, int $columnId, array $taskOrder): void
{
    $stmt = $pdo->prepare('UPDATE tasks SET sort_order = ? WHERE id = ? AND column_id = ?');

    foreach (array_values(array_unique($taskOrder)) as $index => $taskId) {
        $stmt->execute([($index + 1) * 10, (int) $taskId, $columnId]);
    }
}

function normalize_assignee(mixed $assignee): ?string
{
    $assignee = strtoupper(trim((string) $assignee));

    if ($assignee === '') {
        return null;
    }

    return substr($assignee, 0, 8);
}

function handle_download(PDO $pdo): void
{
    require_auth($pdo);

    $fileId = (int) ($_GET['file_id'] ?? 0);

    if ($fileId <= 0) {
        http_response_code(404);
        echo 'Arquivo nao encontrado.';
        exit;
    }

    $stmt = $pdo->prepare('SELECT original_name, mime_type, size_bytes, data FROM document_files WHERE id = ?');
    $stmt->execute([$fileId]);
    $file = $stmt->fetch();

    if (!$file) {
        http_response_code(404);
        echo 'Arquivo nao encontrado.';
        exit;
    }

    header('Content-Type: ' . ($file['mime_type'] ?: 'application/octet-stream'));
    header('Content-Length: ' . (int) $file['size_bytes']);
    header('Content-Disposition: attachment; filename="' . addcslashes((string) $file['original_name'], '"\\') . '"');
    echo $file['data'];
    exit;
}

function save_document_file(PDO $pdo, int $documentId, array $uploadedFile): void
{
    $tmpName = (string) ($uploadedFile['tmp_name'] ?? '');

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('Nao foi possivel ler o arquivo enviado.');
    }

    $data = file_get_contents($tmpName);

    if ($data === false) {
        throw new RuntimeException('Nao foi possivel salvar o arquivo enviado.');
    }

    $originalName = basename((string) ($uploadedFile['name'] ?? 'documento'));
    $mimeType = (string) ($uploadedFile['type'] ?? 'application/octet-stream');
    $sizeBytes = (int) ($uploadedFile['size'] ?? strlen($data));

    $pdo->prepare('DELETE FROM document_files WHERE document_id = ?')->execute([$documentId]);

    $stmt = $pdo->prepare('
        INSERT INTO document_files (document_id, original_name, mime_type, size_bytes, data)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->bindValue(1, $documentId, PDO::PARAM_INT);
    $stmt->bindValue(2, $originalName);
    $stmt->bindValue(3, $mimeType);
    $stmt->bindValue(4, $sizeBytes, PDO::PARAM_INT);
    $stmt->bindValue(5, $data, PDO::PARAM_LOB);
    $stmt->execute();
}

function max_upload_bytes(): int
{
    return 5 * 1024 * 1024;
}

function upload_error_message(int $error): string
{
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        return 'O arquivo precisa ter no maximo 5 MB.';
    }

    if ($error === UPLOAD_ERR_PARTIAL) {
        return 'O envio do arquivo foi interrompido. Tente novamente.';
    }

    return 'Nao foi possivel enviar o arquivo.';
}

function load_storage_summary(PDO $pdo): array
{
    $database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();

    $stmt = $pdo->prepare('
        SELECT
            table_name,
            COALESCE(data_length, 0) + COALESCE(index_length, 0) AS total_bytes,
            COALESCE(table_rows, 0) AS rows_count
        FROM information_schema.tables
        WHERE table_schema = ?
        ORDER BY total_bytes DESC, table_name ASC
    ');
    $stmt->execute([$database]);
    $tables = $stmt->fetchAll();

    $documents = $pdo->query('SELECT COUNT(*) FROM documents')->fetchColumn();
    $files = $pdo->query('SELECT COUNT(*), COALESCE(SUM(size_bytes), 0) FROM document_files')->fetch(PDO::FETCH_NUM);
    $tasks = $pdo->query('SELECT COUNT(*) FROM tasks')->fetchColumn();

    $totalBytes = array_sum(array_map(static fn (array $table): int => (int) $table['total_bytes'], $tables));

    return [
        'database' => $database,
        'total_bytes' => $totalBytes,
        'total_human' => format_bytes($totalBytes),
        'documents' => (int) $documents,
        'files' => (int) ($files[0] ?? 0),
        'file_bytes' => (int) ($files[1] ?? 0),
        'file_human' => format_bytes((int) ($files[1] ?? 0)),
        'tasks' => (int) $tasks,
        'tables' => array_map(static fn (array $table): array => [
            'name' => (string) $table['table_name'],
            'rows' => (int) $table['rows_count'],
            'bytes' => (int) $table['total_bytes'],
            'human' => format_bytes((int) $table['total_bytes']),
        ], $tables),
    ];
}

function format_bytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $value = (float) max(0, $bytes);
    $unitIndex = 0;

    while ($value >= 1024 && $unitIndex < count($units) - 1) {
        $value /= 1024;
        $unitIndex++;
    }

    return number_format($value, $unitIndex === 0 ? 0 : 2, ',', '.') . ' ' . $units[$unitIndex];
}
