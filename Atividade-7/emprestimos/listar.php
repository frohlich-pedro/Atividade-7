<?php
require_once '../conexao.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$filtro_status = isset($_GET['status']) ? $_GET['status'] : 'todos';
$filtro_leitor = isset($_GET['id_leitor']) ? $_GET['id_leitor'] : '';
$filtro_livro = isset($_GET['id_livro']) ? $_GET['id_livro'] : '';

$where = [];
$params = [];

if ($filtro_status === 'ativos') {
    $where[] = "e.data_devolucao IS NULL";
} elseif ($filtro_status === 'concluidos') {
    $where[] = "e.data_devolucao IS NOT NULL";
}

if (!empty($filtro_leitor)) {
    $where[] = "e.id_leitor = :id_leitor";
    $params[':id_leitor'] = $filtro_leitor;
}

if (!empty($filtro_livro)) {
    $where[] = "e.id_livro = :id_livro";
    $params[':id_livro'] = $filtro_livro;
}

$where_clause = '';
if (!empty($where)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where);
}

$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM emprestimos e $where_clause");
foreach ($params as $key => $value) {
    $stmt_count->bindValue($key, $value);
}
$stmt_count->execute();
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

$stmt = $pdo->prepare("
    SELECT e.*, l.titulo as livro_titulo, a.nome as leitor_nome 
    FROM emprestimos e 
    LEFT JOIN livros l ON e.id_livro = l.id_livro 
    LEFT JOIN leitores a ON e.id_leitor = a.id_leitor 
    $where_clause 
    ORDER BY e.data_emprestimo DESC 
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$emprestimos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$leitores = $pdo->query("SELECT id_leitor, nome FROM leitores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$livros = $pdo->query("SELECT id_livro, titulo FROM livros ORDER BY titulo")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empréstimos - Sistema de Biblioteca</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Gerenciamento de Empréstimos</h1>
        </header>

        <nav>
            <ul>
                <li><a href="../index.php">Início</a></li>
                <li><a href="../autores/listar.php">Autores</a></li>
                <li><a href="../livros/listar.php">Livros</a></li>
                <li><a href="../leitores/listar.php">Leitores</a></li>
                <li><a href="listar.php">Empréstimos</a></li>
            </ul>
        </nav>

        <main>
            <h2>Lista de Empréstimos</h2>

            <div class="filters">
                <form method="get">
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="todos" <?php echo $filtro_status == 'todos' ? 'selected' : ''; ?>>Todos</option>
                            <option value="ativos" <?php echo $filtro_status == 'ativos' ? 'selected' : ''; ?>>Ativos</option>
                            <option value="concluidos" <?php echo $filtro_status == 'concluidos' ? 'selected' : ''; ?>>Concluídos</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_leitor">Leitor:</label>
                        <select id="id_leitor" name="id_leitor">
                            <option value="">Todos</option>
                            <?php foreach ($leitores as $leitor): ?>
                                <option value="<?php echo $leitor['id_leitor']; ?>" <?php echo $filtro_leitor == $leitor['id_leitor'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($leitor['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_livro">Livro:</label>
                        <select id="id_livro" name="id_livro">
                            <option value="">Todos</option>
                            <?php foreach ($livros as $livro): ?>
                                <option value="<?php echo $livro['id_livro']; ?>" <?php echo $filtro_livro == $livro['id_livro'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($livro['titulo']); ?>
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

            <a href="criar.php" class="btn btn-success">Novo Empréstimo</a>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Livro</th>
                        <th>Leitor</th>
                        <th>Data Empréstimo</th>
                        <th>Data Devolução</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($emprestimos)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">Nenhum empréstimo encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($emprestimos as $emprestimo): ?>
                            <tr>
                                <td><?php echo $emprestimo['id_emprestimo']; ?></td>
                                <td><?php echo htmlspecialchars($emprestimo['livro_titulo']); ?></td>
                                <td><?php echo htmlspecialchars($emprestimo['leitor_nome']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($emprestimo['data_emprestimo'])); ?></td>
                                <td><?php echo $emprestimo['data_devolucao'] ? date('d/m/Y', strtotime($emprestimo['data_devolucao'])) : 'Não devolvido'; ?></td>
                                <td>
                                    <?php if ($emprestimo['data_devolucao']): ?>
                                        <span class="status status-concluido">Concluído</span>
                                    <?php else: ?>
                                        <span class="status status-ativo">Ativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <?php if (!$emprestimo['data_devolucao']): ?>
                                        <a href="devolver.php?id=<?php echo $emprestimo['id_emprestimo']; ?>" class="btn btn-success">Devolver</a>
                                    <?php endif; ?>
                                    <a href="editar.php?id=<?php echo $emprestimo['id_emprestimo']; ?>" class="btn btn-warning">Editar</a>
                                    <a href="excluir.php?id=<?php echo $emprestimo['id_emprestimo']; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este empréstimo?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($filtro_status); ?>&id_leitor=<?php echo urlencode($filtro_leitor); ?>&id_livro=<?php echo urlencode($filtro_livro); ?>">Anterior</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($filtro_status); ?>&id_leitor=<?php echo urlencode($filtro_leitor); ?>&id_livro=<?php echo urlencode($filtro_livro); ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($filtro_status); ?>&id_leitor=<?php echo urlencode($filtro_leitor); ?>&id_livro=<?php echo urlencode($filtro_livro); ?>">Próxima</a>
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