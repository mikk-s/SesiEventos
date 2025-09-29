<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['erro'] = "Acesso negado.";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_inscricao'])) {
    $id_inscricao = $_POST['id_inscricao'];
    $id_usuario = $_SESSION['usuario_id'];

    try {
        // Garante que o usuário só pode excluir a própria inscrição
        $stmt = $conn->prepare("DELETE FROM inscricoes WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$id_inscricao, $id_usuario]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['mensagem'] = "Inscrição cancelada com sucesso!";
        } else {
            $_SESSION['erro'] = "Não foi possível cancelar a inscrição.";
        }

    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao cancelar inscrição: " . $e->getMessage();
    }
}

header("Location: meus_ingressos.php");
exit();