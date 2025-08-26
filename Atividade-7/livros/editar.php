<?php
require_once '../conexao.php';

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];
$mensagem = '';

$stmt = $pdo->prepare("SELECT * FROM livros WHERE id_livro = ?");
$stmt->execute([$id]);
$livro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$livro) {
    header('Location: listar.php');
    exit();
}

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

        $stmt = $pdo->prepare("UPDATE livros SET titulo = ?, genero = ?, ano_publicacao = ?, id_autor = ? WHERE id_livro = ?");
        $stmt->execute([$titulo, $genero, $ano_publicacao, $id_autor, $id]);

        $mensagem = "Livro atualizado com sucesso!";

        $livro['titulo'] = $titulo;
        $livro['genero'] = $genero;
        $livro['ano_publicacao'] = $ano_publicacao;
        $livro['id_autor'] = $id_autor;
    } catch (Exception $e) {
        $mensagem = "Erro ao atualizar livro: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Livro - Sistema de Biblioteca</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Editar Livro</h1>
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
            <h2>Editar Livro</h2>

            <?php if ($mensagem): ?>
                <div class="alert <?php echo strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="titulo">Título *</label>
                    <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($livro['titulo']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="genero">Gênero</label>
                    <input type="text" id="genero" name="genero" value="<?php echo htmlspecialchars($livro['genero'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="ano_publicacao">Ano de Publicação</label>
                    <input type="number" id="ano_publicacao" name="ano_publicacao" value="<?php echo $livro['ano_publicacao'] ?? ''; ?>" min="1501" max="<?php echo date('Y'); ?>">
                </div>

                <div class="form-group">
                    <label for="id_autor">Autor *</label>
                    <select id="id_autor" name="id_autor" required>
                        <option value="">Selecione um autor</option>
                        <?php foreach ($autores as $autor): ?>
                            <option value="<?php echo $autor['id_autor']; ?>" <?php echo $livro['id_autor'] == $autor['id_autor'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($autor['nome']); ?>
                            </option>
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