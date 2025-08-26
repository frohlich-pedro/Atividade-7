<?php
require_once '../conexao.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $nacionalidade = $_POST['nacionalidade'];
    $ano_nascimento = $_POST['ano_nascimento'] ?: null;

    try {
        $stmt = $pdo->prepare("INSERT INTO autores (nome, nacionalidade, ano_nascimento) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $nacionalidade, $ano_nascimento]);

        $mensagem = "Autor cadastrado com sucesso!";
    } catch (PDOException $e) {
        $mensagem = "Erro ao cadastrar autor: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Autor - Sistema de Biblioteca</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Cadastrar Novo Autor</h1>
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
            <h2>Novo Autor</h2>

            <?php if ($mensagem): ?>
                <div class="alert <?php echo strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="nome">Nome *</label>
                    <input type="text" id="nome" name="nome" required>
                </div>

                <div class="form-group">
                    <label for="nacionalidade">Nacionalidade</label>
                    <input type="text" id="nacionalidade" name="nacionalidade">
                </div>

                <div class="form-group">
                    <label for="ano_nascimento">Ano de Nascimento</label>
                    <input type="number" id="ano_nascimento" name="ano_nascimento" min="0" max="<?php echo date('Y'); ?>">
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