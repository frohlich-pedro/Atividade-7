<?php
require_once '../conexao.php';

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM emprestimos WHERE id_emprestimo = ?");
    $stmt->execute([$id]);

    header('Location: listar.php?mensagem=Empréstimo excluído com sucesso');
} catch (PDOException $e) {
    header('Location: listar.php?erro=Erro ao excluir empréstimo: ' . $e->getMessage());
}
exit();