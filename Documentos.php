<?php
$storageFile = __DIR__ . '/documents.json';

function escape($value) {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function loadDocuments($storageFile) {
    if (file_exists($storageFile)) {
        $contents = file_get_contents($storageFile);
        $documents = json_decode($contents, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($documents)) {
            return $documents;
        }
    }

    return [
        [
            'titulo' => 'Ata de Reunião - Chassi',
            'autor' => 'JF',
            'data' => '02/05/2026',
            'conteudo' => 'Nesta reunião, a equipe decidiu utilizar motores NEO para a tração e montar o chassi em formato U para facilitar a coleta das peças no campo.'
        ],
        [
            'titulo' => 'Lógica da Câmera (Autônomo)',
            'autor' => 'SY',
            'data' => '28/04/2026',
            'conteudo' => 'A câmera será posicionada a 30cm do chão, angulada em 15 graus para cima. O código vai usar a biblioteca PhotonVision para detectar as AprilTags e alinhar o robô automaticamente.'
        ],
        [
            'titulo' => 'Lista de Peças do Robô',
            'autor' => 'JF',
            'data' => '20/04/2026',
            'conteudo' => "- 4x Motores NEO\n- 4x Spark Max\n- 1x RoboRIO 2.0\n- Perfis de alumínio 2x1"
        ],
    ];
}

function saveDocuments($storageFile, $documents) {
    file_put_contents($storageFile, json_encode($documents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$documents = loadDocuments($storageFile);
$statusMessage = null;
$statusType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $texto = trim($_POST['texto'] ?? '');
    $documentIndex = isset($_POST['document_index']) && is_numeric($_POST['document_index']) ? (int)$_POST['document_index'] : null;

    if ($titulo === '') {
        $statusMessage = 'Por favor, dê um título ao seu documento!';
        $statusType = 'error';
    } else {
        $novoDocumento = [
            'titulo' => $titulo,
            'autor' => 'JF',
            'data' => date('d/m/Y'),
            'conteudo' => $texto,
        ];

        if ($documentIndex !== null && isset($documents[$documentIndex])) {
            $documents[$documentIndex] = $novoDocumento;
            $statusMessage = "Documento '{$titulo}' atualizado com sucesso!";
        } else {
            array_unshift($documents, $novoDocumento);
            $statusMessage = "Novo documento '{$titulo}' salvo com sucesso!";
        }

        saveDocuments($storageFile, $documents);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos - Portal Tuiutech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="Cabecario">
        <a href="index.html" class="logo-link">
            <img src="Syle.png" alt="Logo Syle" class="logo-img">
        </a>
        <div class="menu-container">
            <nav id="Menu">
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="Documentos.php" class="active">Documentos</a></li>
                    <li><a href="Projetos.html">Projetos</a></li>
                </ul>
            </nav>
            <div class="grupo-perfil">
                <div class="perfil-btn">JF</div>
                <a href="Login.html" class="btn-sair">Sair</a>
            </div>
        </div>
    </header>

    <div class="container-docs">
        <aside class="sidebar-docs">
            <h3>Categorias</h3>
            <ul>
                <li><a href="#">Mecânica & Design</a></li>
                <li><a href="#">Programação & Autônomo</a></li>
                <li><a href="#">Gestão & Marketing</a></li>
                <li><a href="#">Atas de Reunião</a></li>
            </ul>
        </aside>

        <main class="conteudo-docs">
            <div class="barra-pesquisa">
                <input type="text" placeholder="Pesquisar documentos...">
                <button type="button" class="btn-primario" id="btn-novo-doc">+ Novo Documento</button>
                <a href="https://drive.google.com/drive/folders/1ma3IiRQ9hUQKY1gMS4HG45zitu5vhwEq" class="btn-drive-topo">
                    Acessar Drive da Equipe
                </a>
            </div>

            <?php if ($statusMessage !== null): ?>
                <div class="mensagem <?php echo $statusType === 'error' ? 'erro' : 'sucesso'; ?>">
                    <?php echo escape($statusMessage); ?>
                </div>
            <?php endif; ?>

            <div class="bloco-docs">
                <h2>Documentos Recentes</h2>
                <table class="tabela-docs">
                    <thead>
                        <tr>
                            <th>Nome do Documento</th>
                            <th>Autor</th>
                            <th>Última Modificação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $index => $document): ?>
                            <tr data-index="<?php echo $index; ?>" data-conteudo="<?php echo escape($document['conteudo']); ?>">
                                <td><?php echo escape($document['titulo']); ?></td>
                                <td><?php echo escape($document['autor']); ?></td>
                                <td><?php echo escape($document['data']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="bloco-docs editor-docs">
                <h2>Criar / Editar Documento</h2>
                <form id="formDocumentos" action="Documentos.php" method="post">
                    <input type="hidden" name="document_index" id="document_index" value="">
                    <input type="text" name="titulo" id="tituloDocumento" class="input-titulo" placeholder="Digite o título do documento aqui..." value="">
                    <textarea name="texto" id="textoDocumento" class="input-texto" placeholder="Comece a digitar o seu documento..."></textarea>
                    <div class="editor-acoes">
                        <button type="submit" class="btn-primario" id="btn-salvar-documento">Salvar Documento</button>
                        <button type="button" class="btn-secundario" id="btn-cancelar-documento">Cancelar</button>
                    </div>
                </form>
            </div>

        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>
