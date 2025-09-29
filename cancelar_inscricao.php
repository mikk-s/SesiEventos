<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['erro'] = "Acesso negado.";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_inscricao']) && isset($_POST['quantidade'])) {
    $id_inscricao = $_POST['id_inscricao'];
    $quantidade_a_cancelar = (int)$_POST['quantidade'];
    $id_usuario = $_SESSION['usuario_id'];

    if ($quantidade_a_cancelar <= 0) {
        $_SESSION['erro'] = "A quantidade a ser cancelada deve ser maior que zero.";
        header("Location: meus_ingressos.php");
        exit();
    }

    try {
        // Busca a inscrição atual para verificar a propriedade e a quantidade
        $stmt = $conn->prepare("SELECT * FROM inscricoes WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$id_inscricao, $id_usuario]);
        $inscricao = $stmt->fetch();

        if (!$inscricao) {
            throw new Exception("Inscrição não encontrada ou não pertence a você.");
        }

        if ($quantidade_a_cancelar > $inscricao['quantidade']) {
            throw new Exception("Você não pode cancelar mais ingressos do que possui.");
        }

        // Se a quantidade a cancelar for igual à que o usuário possui, deleta a inscrição.
        if ($quantidade_a_cancelar == $inscricao['quantidade']) {
            $stmt_delete = $conn->prepare("DELETE FROM inscricoes WHERE id = ?");
            $stmt_delete->execute([$id_inscricao]);
            $_SESSION['mensagem'] = "Inscrição cancelada com sucesso!";
        } 
        // Se for menor, apenas atualiza a quantidade.
        else {
            $nova_quantidade = $inscricao['quantidade'] - $quantidade_a_cancelar;
            $stmt_update = $conn->prepare("UPDATE inscricoes SET quantidade = ? WHERE id = ?");
            $stmt_update->execute([$nova_quantidade, $id_inscricao]);
            $_SESSION['mensagem'] = "{$quantidade_a_cancelar} ingresso(s) cancelado(s) com sucesso!";
        }

    } catch (Exception $e) {
        $_SESSION['erro'] = "Erro: " . $e->getMessage();
    }
} else {
    $_SESSION['erro'] = "Requisição inválida.";
}

header("Location: meus_ingressos.php");
exit();