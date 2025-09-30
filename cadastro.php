<?php
require "conexao.php";
session_start(); 
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["login"];
    $senha = $_POST["senha"];
    $nome = $_POST["nome"];
    // NOVO CAMPO: Captura a permissão do formulário
    $perm = $_POST["perm"];

    // Validação para garantir que a permissão seja válida
    if (!in_array($perm, ['Visitante', 'Organizador'])) {
        $_SESSION["erro"] = "Tipo de permissão inválido.";
        header("Location: cadastro.php");
        exit();
    }

    $checkSql = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(":email", $email);
    $checkStmt->execute();
    
    if ($checkStmt->fetchColumn() > 0) {
        $_SESSION["erro"] = "Este email já está em uso.";
        header("Location: cadastro.php");
        exit();
    }

    $senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);

    // ATUALIZADO: Inclui a permissão (perm) no INSERT
    $sql = "INSERT INTO usuarios (nome, email, senha, perm) VALUES (:nome, :email, :senha, :perm)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":nome", $nome);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":senha", $senhaCriptografada);
    $stmt->bindParam(":perm", $perm);

    if ($stmt->execute()) {
        $_SESSION["erro"] = "Usuario criado com sucesso! Por favor, faça o login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION["erro"] = "Erro ao realizar cadastro.";
        header("Location: cadastro.php");
        exit();
    }
}

include_once("templates/header.php");

if (isset($_SESSION["erro"])) {
    echo "<script>alert('{$_SESSION["erro"]}');</script>";
    unset($_SESSION["erro"]);
}
?>

<link rel="stylesheet" href="css/style.css">

<main class="login-form-page">
    <div class="login-split-container">
        <div class="login-image-side" style="background-image: url('img/essa.jpg');">
        </div>

        <div class="login-form-side">
            <div class="form-card">
                <h2>Cadastro</h2>
                <form method="post">
                    <label for="nome">Nome:</label>
                    <input type="text" name="nome" placeholder="Seu nome completo" required>
                    
                    <label for="login">Login (Email):</label>
                    <input type="email" name="login" placeholder="seu@email.com" required>
                    
                    <label for="senha">Senha:</label>
                    <input type="password" name="senha" placeholder="Crie uma senha" required>
                    
                    <label for="perm">Tipo de Conta:</label>
                    <select id="perm" name="perm" required>
                        <option value="Visitante">Visitante (Quero participar dos eventos)</option>
                        <option value="Organizador">Organizador (Quero criar eventos)</option>
                    </select>

                    <button type="submit" class="submit-button">Cadastrar</button>
                </form>
                <p class="secondary-action">
                    Já tem uma conta? <a href="login.php">Fazer Login</a>
                </p>
            </div>
        </div>
    </div>
</main>