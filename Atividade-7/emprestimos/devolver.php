<?php
require_once '../conexao.php';

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("UPDATE emprestimos SET data_devolucao = CURDATE() WHERE id_emprestimo = ? AND data_devolucao IS NULL");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        header('Location: listar.php?mensagem=Livro devolvido com sucesso');
    } else {
        header('Location: listar.php?erro=Empréstimo não encontrado ou já devolvido');
    }
} catch (PDOException $e) {
    header('Location: listar.php?erro=Erro ao registrar devolução: ' . $e->getMessage());
}
exit();