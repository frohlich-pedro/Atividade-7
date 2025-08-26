<?php
require_once '../conexao.php';

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM autores WHERE id_autor = ?");
    $stmt->execute([$id]);

    header('Location: listar.php?mensagem=Autor excluído com sucesso');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        header('Location: listar.php?erro=Não é possível excluir este autor pois existem livros associados a ele');
    } else {
        header('Location: listar.php?erro=Erro ao excluir autor: ' . $e->getMessage());
    }
}
exit();
