<?php
require_once '../conexao.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$filtro_nome = isset($_GET['nome']) ? $_GET['nome'] : '';
$filtro_email = isset($_GET['email']) ? $_GET['email'] : '';

$where = [];
$params = [];

if (!empty($filtro_nome)) {
    $where[] = "nome LIKE :nome";
    $params[':nome'] = "%$filtro_nome%";
}

if (!empty($filtro_email)) {
    $where[] = "email LIKE :email";
    $params[':email'] = "%$filtro_email%";
}

$where_clause = '';
if (!empty($where)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where);
}

$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM leitores $where_clause");
foreach ($params as $key => $value) {
    $stmt_count->bindValue($key, $value);
}
$stmt_count->execute();
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

$stmt = $pdo->prepare("SELECT * FROM leitores $where_clause ORDER BY nome LIMIT :limit OFFSET :offset");
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$leitores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leitores - Sistema de Biblioteca</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Gerenciamento de Leitores</h1>
        </header>

        <nav>
            <ul>
                <li><a href="../index.php">Início</a></li>
                <li><a href="../autores/listar.php">Autores</a></li>
                <li><a href="../livros/listar.php">Livros</a></li>
                <li><a href="listar.php">Leitores</a></li>
                <li><a href="../emprestimos/listar.php">Empréstimos</a></li>
            </ul>
        </nav>

        <main>
            <h2>Lista de Leitores</h2>

            <div class="filters">
                <form method="get">
                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($filtro_nome); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($filtro_email); ?>">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="listar.php" class="btn">Limpar</a>
                    </div>
                </form>
            </div>

            <a href="criar.php" class="btn btn-success">Novo Leitor</a>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leitores)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Nenhum leitor encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leitores as $leitor): ?>
                            <tr>
                                <td><?php echo $leitor['id_leitor']; ?></td>
                                <td><?php echo htmlspecialchars($leitor['nome']); ?></td>
                                <td><?php echo htmlspecialchars($leitor['email'] ?? 'Não informado'); ?></td>
                                <td><?php echo htmlspecialchars($leitor['telefone'] ?? 'Não informado'); ?></td>
                                <td class="actions">
                                    <a href="editar.php?id=<?php echo $leitor['id_leitor']; ?>" class="btn btn-warning">Editar</a>
                                    <a href="excluir.php?id=<?php echo $leitor['id_leitor']; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este leitor?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&nome=<?php echo urlencode($filtro_nome); ?>&email=<?php echo urlencode($filtro_email); ?>">Anterior</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&nome=<?php echo urlencode($filtro_nome); ?>&email=<?php echo urlencode($filtro_email); ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&nome=<?php echo urlencode($filtro_nome); ?>&email=<?php echo urlencode($filtro_email); ?>">Próxima</a>
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