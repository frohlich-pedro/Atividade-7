<?php
require_once '../conexao.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$filtro_nome = isset($_GET['nome']) ? $_GET['nome'] : '';
$filtro_nacionalidade = isset($_GET['nacionalidade']) ? $_GET['nacionalidade'] : '';

$where = [];
$params = [];

if (!empty($filtro_nome)) {
    $where[] = "nome LIKE :nome";
    $params[':nome'] = "%$filtro_nome%";
}

if (!empty($filtro_nacionalidade)) {
    $where[] = "nacionalidade = :nacionalidade";
    $params[':nacionalidade'] = $filtro_nacionalidade;
}

$where_clause = '';
if (!empty($where)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where);
}

$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM autores $where_clause");
foreach ($params as $key => $value) {
    $stmt_count->bindValue($key, $value);
}
$stmt_count->execute();
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

$stmt = $pdo->prepare("SELECT * FROM autores $where_clause ORDER BY nome LIMIT :limit OFFSET :offset");
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$autores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nacionalidades = $pdo->query("SELECT DISTINCT nacionalidade FROM autores WHERE nacionalidade IS NOT NULL ORDER BY nacionalidade")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autores - Sistema de Biblioteca</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Gerenciamento de Autores</h1>
        </header>

        <nav>
            <ul>
                <li><a href="../index.php">Início</a></li>
                <li><a href="listar.php">Autores</a></li>
                <li><a href="../livros/listar.php">Livros</a></li>
                <li><a href="../leitores/listar.php">Leitores</a></li>
                <li><a href="../emprestimos/listar.php">Empréstimos</a></li>
            </ul>
        </nav>

        <main>
            <h2>Lista de Autores</h2>

            <div class="filters">
                <form method="get">
                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($filtro_nome); ?>">
                    </div>

                    <div class="form-group">
                        <label for="nacionalidade">Nacionalidade:</label>
                        <select id="nacionalidade" name="nacionalidade">
                            <option value="">Todas</option>
                            <?php foreach ($nacionalidades as $nacionalidade): ?>
                                <option value="<?php echo htmlspecialchars($nacionalidade); ?>" <?php echo $filtro_nacionalidade == $nacionalidade ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nacionalidade); ?>
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

            <a href="criar.php" class="btn btn-success">Novo Autor</a>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Nacionalidade</th>
                        <th>Ano de Nascimento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($autores)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Nenhum autor encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($autores as $autor): ?>
                            <tr>
                                <td><?php echo $autor['id_autor']; ?></td>
                                <td><?php echo htmlspecialchars($autor['nome']); ?></td>
                                <td><?php echo htmlspecialchars($autor['nacionalidade'] ?? 'Não informada'); ?></td>
                                <td><?php echo $autor['ano_nascimento'] ?? 'Não informado'; ?></td>
                                <td class="actions">
                                    <a href="editar.php?id=<?php echo $autor['id_autor']; ?>" class="btn btn-warning">Editar</a>
                                    <a href="excluir.php?id=<?php echo $autor['id_autor']; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este autor?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&nome=<?php echo urlencode($filtro_nome); ?>&nacionalidade=<?php echo urlencode($filtro_nacionalidade); ?>">Anterior</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&nome=<?php echo urlencode($filtro_nome); ?>&nacionalidade=<?php echo urlencode($filtro_nacionalidade); ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&nome=<?php echo urlencode($filtro_nome); ?>&nacionalidade=<?php echo urlencode($filtro_nacionalidade); ?>">Próxima</a>
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