<?php
require_once '../conexao.php';

$mensagem = '';

$livros_disponiveis = $pdo->query("
    SELECT l.id_livro, l.titulo, a.nome as autor 
    FROM livros l 
    LEFT JOIN autores a ON l.id_autor = a.id_autor 
    WHERE l.id_livro NOT IN (
        SELECT id_livro 
        FROM emprestimos 
        WHERE data_devolucao IS NULL
    )
    ORDER BY l.titulo
")->fetchAll(PDO::FETCH_ASSOC);

$leitores = $pdo->query("SELECT id_leitor, nome FROM leitores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_livro = $_POST['id_livro'];
    $id_leitor = $_POST['id_leitor'];
    $data_emprestimo = $_POST['data_emprestimo'];

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM emprestimos WHERE id_leitor = ? AND data_devolucao IS NULL");
        $stmt->execute([$id_leitor]);
        $emprestimos_ativos = $stmt->fetchColumn();

        if ($emprestimos_ativos >= 3) {
            throw new Exception("Este leitor já possui 3 empréstimos ativos. Não é possível realizar mais empréstimos.");
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM emprestimos WHERE id_livro = ? AND data_devolucao IS NULL");
        $stmt->execute([$id_livro]);
        $livro_emprestado = $stmt->fetchColumn();

        if ($livro_emprestado > 0) {
            throw new Exception("Este livro já está emprestado.");
        }

        $stmt = $pdo->prepare("INSERT INTO emprestimos (id_livro, id_leitor, data_emprestimo) VALUES (?, ?, ?)");
        $stmt->execute([$id_livro, $id_leitor, $data_emprestimo]);

        $mensagem = "Empréstimo realizado com sucesso!";
    } catch (Exception $e) {
        $mensagem = "Erro ao realizar empréstimo: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Empréstimo - Sistema de Biblioteca</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Novo Empréstimo</h1>
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
            <h2>Novo Empréstimo</h2>

            <?php if ($mensagem): ?>
                <div class="alert <?php echo strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="id_livro">Livro *</label>
                    <select id="id_livro" name="id_livro" required>
                        <option value="">Selecione um livro</option>
                        <?php foreach ($livros_disponiveis as $livro): ?>
                            <option value="<?php echo $livro['id_livro']; ?>">
                                <?php echo htmlspecialchars($livro['titulo'] . ' - ' . $livro['autor']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_leitor">Leitor *</label>
                    <select id="id_leitor" name="id_leitor" required>
                        <option value="">Selecione um leitor</option>
                        <?php foreach ($leitores as $leitor): ?>
                            <option value="<?php echo $leitor['id_leitor']; ?>">
                                <?php echo htmlspecialchars($leitor['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data_emprestimo">Data do Empréstimo *</label>
                    <input type="date" id="data_emprestimo" name="data_emprestimo" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <button type="submit" class="btn btn-success">Salvar</button>
                <a href="listar.php" class="btn">Cancelar</a>
            </form>
        </main>

        <footer>
            <p>Sistema de Biblioteca &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>
</body>

</html>