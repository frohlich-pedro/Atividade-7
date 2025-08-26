<?php
require_once '../conexao.php';

$mensagem = '';

$autores = $pdo->query("SELECT id_autor, nome FROM autores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $genero = $_POST['genero'];
    $ano_publicacao = $_POST['ano_publicacao'] ?: null;
    $id_autor = $_POST['id_autor'];

    try {
        $ano_atual = date('Y');
        if ($ano_publicacao && ($ano_publicacao <= 1500 || $ano_publicacao > $ano_atual)) {
            throw new Exception("Ano de publicação deve ser maior que 1500 e menor ou igual a $ano_atual");
        }

        $stmt = $pdo->prepare("INSERT INTO livros (titulo, genero, ano_publicacao, id_autor) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titulo, $genero, $ano_publicacao, $id_autor]);

        $mensagem = "Livro cadastrado com sucesso!";
    } catch (Exception $e) {
        $mensagem = "Erro ao cadastrar livro: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Livro - Sistema de Biblioteca</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Cadastrar Novo Livro</h1>
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
            <h2>Novo Livro</h2>

            <?php if ($mensagem): ?>
                <div class="alert <?php echo strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="titulo">Título *</label>
                    <input type="text" id="titulo" name="titulo" required>
                </div>

                <div class="form-group">
                    <label for="genero">Gênero</label>
                    <input type="text" id="genero" name="genero">
                </div>

                <div class="form-group">
                    <label for="ano_publicacao">Ano de Publicação</label>
                    <input type="number" id="ano_publicacao" name="ano_publicacao" min="1501" max="<?php echo date('Y'); ?>">
                </div>

                <div class="form-group">
                    <label for="id_autor">Autor *</label>
                    <select id="id_autor" name="id_autor" required>
                        <option value="">Selecione um autor</option>
                        <?php foreach ($autores as $autor): ?>
                            <option value="<?php echo $autor['id_autor']; ?>"><?php echo htmlspecialchars($autor['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
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