<?php
require_once 'conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Biblioteca</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Sistema de Gerenciamento de Biblioteca</h1>
        </header>

        <nav>
            <ul>
                <li><a href="autores/listar.php">Autores</a></li>
                <li><a href="livros/listar.php">Livros</a></li>
                <li><a href="leitores/listar.php">Leitores</a></li>
                <li><a href="emprestimos/listar.php">Empréstimos</a></li>
            </ul>
        </nav>

        <main>
            <h2>Bem-vindo ao Sistema de Biblioteca</h2>
            <p>Selecione uma das opções no menu acima para gerenciar o sistema.</p>

            <div class="dashboard">
                <div class="card">
                    <h3>Autores Cadastrados</h3>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM autores");
                    $result = $stmt->fetch();
                    echo "<p>" . $result['total'] . " autores</p>";
                    ?>
                </div>

                <div class="card">
                    <h3>Livros Cadastrados</h3>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM livros");
                    $result = $stmt->fetch();
                    echo "<p>" . $result['total'] . " livros</p>";
                    ?>
                </div>

                <div class="card">
                    <h3>Leitores Cadastrados</h3>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM leitores");
                    $result = $stmt->fetch();
                    echo "<p>" . $result['total'] . " leitores</p>";
                    ?>
                </div>

                <div class="card">
                    <h3>Empréstimos Ativos</h3>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM emprestimos WHERE data_devolucao IS NULL");
                    $result = $stmt->fetch();
                    echo "<p>" . $result['total'] . " empréstimos ativos</p>";
                    ?>
                </div>
            </div>
        </main>

        <footer>
            <p>Sistema de Biblioteca &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>
</body>

</html>