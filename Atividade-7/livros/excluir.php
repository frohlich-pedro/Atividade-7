<?php
require_once '../conexao.php';

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM livros WHERE id_livro = ?");
    $stmt->execute([$id]);

    header('Location: listar.php?mensagem=Livro excluído com sucesso');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        header('Location: listar.php?erro=Não é possível excluir este livro pois existem empréstimos associados a ele');
    } else {
        header('Location: listar.php?erro=Erro ao excluir livro: ' . $e->getMessage());
    }
}
exit();
