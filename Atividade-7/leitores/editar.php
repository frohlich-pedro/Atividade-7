<?php
require_once '../conexao.php';

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];
$mensagem = '';

$stmt = $pdo->prepare("SELECT * FROM leitores WHERE id_leitor = ?");
$stmt->execute([$id]);
$leitor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$leitor) {
    header('Location: listar.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];

    try {
        $stmt = $pdo->prepare("UPDATE leitores SET nome = ?, email = ?, telefone = ? WHERE id_leitor = ?");
        $stmt->execute([$nome, $email, $telefone, $id]);

        $mensagem = "Leitor atualizado com sucesso!";

        $leitor['nome'] = $nome;
        $leitor['email'] = $email;
        $leitor['telefone'] = $telefone;
    } catch (PDOException $e) {
        $mensagem = "Erro ao atualizar leitor: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Leitor - Sistema de Biblioteca</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Editar Leitor</h1>
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
            <h2>Editar Leitor</h2>

            <?php if ($mensagem): ?>
                <div class="alert <?php echo strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $mensagram; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="nome">Nome *</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($leitor['nome']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($leitor['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($leitor['telefone'] ?? ''); ?>">
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