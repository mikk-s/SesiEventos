<?php
session_start();
require_once 'conexao.php';

// 1. Verifica se o usuário está logado
if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você precisa estar logado para adquirir um ingresso.";
    header("Location: login.php");
    exit();
}

// 2. Verifica se o ID do evento foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['id_evento'])) {
    header("Location: index.php");
    exit();
}

$id_evento = $_POST['id_evento'];
$id_usuario = $_SESSION['usuario_id'];

try {
    // 3. Verifica se o usuário já está inscrito no evento
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM inscricoes WHERE id_usuario = ? AND id_evento = ?");
    $stmt_check->execute([$id_usuario, $id_evento]);
    if ($stmt_check->fetchColumn() > 0) {
        // Lança um erro se a inscrição já existir
        throw new Exception("Você já está inscrito neste evento.");
    }

    // 4. Busca a capacidade máxima e o número atual de inscritos
    $stmt_evento = $conn->prepare("SELECT max_pessoas, (SELECT COUNT(*) FROM inscricoes WHERE id_evento = eventos.id) AS inscritos FROM eventos WHERE id = ?");
    $stmt_evento->execute([$id_evento]);
    $evento = $stmt_evento->fetch();

    if (!$evento) {
        throw new Exception("Evento não encontrado.");
    }

    // 5. Verifica se o limite de pessoas foi excedido
    if ($evento['inscritos'] >= $evento['max_pessoas']) {
        throw new Exception("Ingressos esgotados para este evento.");
    }

    // 6. Se tudo estiver certo, insere a nova inscrição no banco
    $stmt_insert = $conn->prepare("INSERT INTO inscricoes (id_usuario, id_evento) VALUES (?, ?)");
    $stmt_insert->execute([$id_usuario, $id_evento]);

    $_SESSION['mensagem'] = "Inscrição realizada com sucesso!";

} catch (Exception $e) {
    // Captura qualquer um dos erros lançados e guarda na sessão
    $_SESSION['erro'] = "Erro: " . $e->getMessage();
}

// Redireciona de volta para a página inicial para mostrar o resultado
header("Location: index.php");
exit();