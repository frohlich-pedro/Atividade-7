<?php
require_once '../conexao.php';

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM leitores WHERE id_leitor = ?");
    $stmt->execute([$id]);

    header('Location: listar.php?mensagem=Leitor excluído com sucesso');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        header('Location: listar.php?erro=Não é possível excluir este leitor pois existem empréstimos associados a ele');
    } else {
        header('Location: listar.php?erro=Erro ao excluir leitor: ' . $e->getMessage());
    }
}
exit();