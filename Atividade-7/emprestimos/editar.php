<?php
require_once '../conexao.php';

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];
$mensagem = '';

$stmt = $pdo->prepare("
    SELECT e.*, l.titulo as livro_titulo, a.nome as leitor_nome 
    FROM emprestimos e 
    LEFT JOIN livros l ON e.id_livro = l.id_livro 
    LEFT JOIN leitores a ON e.id_leitor = a.id_leitor 
    WHERE e.id_emprestimo = ?
");
$stmt->execute([$id]);
$emprestimo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$emprestimo) {
    header('Location: listar.php');
    exit();
}

$livros = $pdo->query("SELECT id_livro, titulo FROM livros ORDER BY titulo")->fetchAll(PDO::FETCH_ASSOC);

$leitores = $pdo->query("SELECT id_leitor, nome FROM leitores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_livro = $_POST['id_livro'];
    $id_leitor = $_POST['id_leitor'];
    $data_emprestimo = $_POST['data_emprestimo'];
    $data_devolucao = $_POST['data_devolucao'] ?: null;

    try {
        if ($data_devolucao && strtotime($data_devolucao) < strtotime($data_emprestimo)) {
            throw new Exception("Data de devolução não pode ser anterior à data de empréstimo");
        }

        $stmt = $pdo->prepare("UPDATE emprestimos SET id_livro = ?, id_leitor = ?, data_emprestimo = ?, data_devolucao = ? WHERE id_emprestimo = ?");
        $stmt->execute([$id_livro, $id_leitor, $data_emprestimo, $data_devolucao, $id]);

        $mensagem = "Empréstimo atualizado com sucesso!";

        $emprestimo['id_livro'] = $id_livro;
        $emprestimo['id_leitor'] = $id_leitor;
        $emprestimo['data_emprestimo'] = $data_emprestimo;
        $emprestimo['data_devolucao'] = $data_devolucao;
    } catch (Exception $e) {
        $mensagem = "Erro ao atualizar empréstimo: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empréstimo - Sistema de Biblioteca</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Editar Empréstimo</h1>
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
            <h2>Editar Empréstimo</h2>

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
                        <?php foreach ($livros as $livro): ?>
                            <option value="<?php echo $livro['id_livro']; ?>" <?php echo $emprestimo['id_livro'] == $livro['id_livro'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($livro['titulo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_leitor">Leitor *</label>
                    <select id="id_leitor" name="id_leitor" required>
                        <option value="">Selecione um leitor</option>
                        <?php foreach ($leitores as $leitor): ?>
                            <option value="<?php echo $leitor['id_leitor']; ?>" <?php echo $emprestimo['id_leitor'] == $leitor['id_leitor'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($leitor['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data_emprestimo">Data do Empréstimo *</label>
                    <input type="date" id="data_emprestimo" name="data_emprestimo" value="<?php echo $emprestimo['data_emprestimo']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="data_devolucao">Data de Devolução</label>
                    <input type="date" id="data_devolucao" name="data_devolucao" value="<?php echo $emprestimo['data_devolucao'] ?? ''; ?>">
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