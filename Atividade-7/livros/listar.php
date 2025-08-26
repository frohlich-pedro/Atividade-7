<?php
require_once '../conexao.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$filtro_titulo = isset($_GET['titulo']) ? $_GET['titulo'] : '';
$filtro_genero = isset($_GET['genero']) ? $_GET['genero'] : '';
$filtro_autor = isset($_GET['id_autor']) ? $_GET['id_autor'] : '';

$where = [];
$params = [];

if (!empty($filtro_titulo)) {
    $where[] = "l.titulo LIKE :titulo";
    $params[':titulo'] = "%$filtro_titulo%";
}

if (!empty($filtro_genero)) {
    $where[] = "l.genero LIKE :genero";
    $params[':genero'] = "%$filtro_genero%";
}

if (!empty($filtro_autor)) {
    $where[] = "l.id_autor = :id_autor";
    $params[':id_autor'] = $filtro_autor;
}

$where_clause = '';
if (!empty($where)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where);
}

$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM livros l $where_clause");
foreach ($params as $key => $value) {
    $stmt_count->bindValue($key, $value);
}
$stmt_count->execute();
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

$stmt = $pdo->prepare("
    SELECT l.*, a.nome as autor_nome 
    FROM livros l 
    LEFT JOIN autores a ON l.id_autor = a.id_autor 
    $where_clause 
    ORDER BY l.titulo 
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$livros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$autores = $pdo->query("SELECT id_autor, nome FROM autores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$generos = $pdo->query("SELECT DISTINCT genero FROM livros WHERE genero IS NOT NULL ORDER BY genero")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livros - Sistema de Biblioteca</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Gerenciamento de Livros</h1>
        </header>

        <nav>
            <ul>
                <li><a href="../index.php">Início</a></li>
                <li><a href="../autores/listar.php">Autores</a></li>
                <li><a href="listar.php">Livros</a></li>
                <li><a href="../leitores/listar.php">Leitores</a></li>
                <li><a href="../emprestimos/listar.php">Empréstimos</a></li>
            </ul>
        </nav>

        <main>
            <h2>Lista de Livros</h2>

            <div class="filters">
                <form method="get">
                    <div class="form-group">
                        <label for="titulo">Título:</label>
                        <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($filtro_titulo); ?>">
                    </div>

                    <div class="form-group">
                        <label for="genero">Gênero:</label>
                        <select id="genero" name="genero">
                            <option value="">Todos</option>
                            <?php foreach ($generos as $genero): ?>
                                <option value="<?php echo htmlspecialchars($genero); ?>" <?php echo $filtro_genero == $genero ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($genero); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_autor">Autor:</label>
                        <select id="id_autor" name="id_autor">
                            <option value="">Todos</option>
                            <?php foreach ($autores as $autor): ?>
                                <option value="<?php echo $autor['id_autor']; ?>" <?php echo $filtro_autor == $autor['id_autor'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($autor['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="listar.php" class="btn">Limpar</a>
                    </div>
                </form>
            </div>

            <a href="criar.php" class="btn btn-success">Novo Livro</a>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Gênero</th>
                        <th>Ano de Publicação</th>
                        <th>Autor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($livros)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Nenhum livro encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($livros as $livro): ?>
                            <tr>
                                <td><?php echo $livro['id_livro']; ?></td>
                                <td><?php echo htmlspecialchars($livro['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($livro['genero'] ?? 'Não informado'); ?></td>
                                <td><?php echo $livro['ano_publicacao'] ?? 'Não informado'; ?></td>
                                <td><?php echo htmlspecialchars($livro['autor_nome'] ?? 'Não informado'); ?></td>
                                <td class="actions">
                                    <a href="editar.php?id=<?php echo $livro['id_livro']; ?>" class="btn btn-warning">Editar</a>
                                    <a href="excluir.php?id=<?php echo $livro['id_livro']; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este livro?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&titulo=<?php echo urlencode($filtro_titulo); ?>&genero=<?php echo urlencode($filtro_genero); ?>&id_autor=<?php echo urlencode($filtro_autor); ?>">Anterior</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&titulo=<?php echo urlencode($filtro_titulo); ?>&genero=<?php echo urlencode($filtro_genero); ?>&id_autor=<?php echo urlencode($filtro_autor); ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&titulo=<?php echo urlencode($filtro_titulo); ?>&genero=<?php echo urlencode($filtro_genero); ?>&id_autor=<?php echo urlencode($filtro_autor); ?>">Próxima</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>

        <footer>
            <p>Sistema de Biblioteca &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>
</body>

</html>