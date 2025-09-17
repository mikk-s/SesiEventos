<?php
require "conexao.php";
session_start(); 
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["login"];
    $senha = $_POST["senha"];
    $nome = $_POST["nome"];

    $checkSql = "SELECT COUNT(*) FROM usuarios WHERE email = :email and nome = :nome ";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(":email", $email);
    $checkStmt->bindParam(":nome", $nome);
    $checkStmt->execute();
    $userExists = $checkStmt->fetchColumn();

    if ($userExists > 0) {
        $_SESSION["erro"] = "Usuário já existe. Por favor, escolha outro nome de usuário.";
        header("Location: cadastro.php");
        exit();
    }

    $senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":senha", $senhaCriptografada);
    $stmt->bindParam(":nome", $nome);

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

if (isset($_SESSION["erro"])) {
    echo "<script>alert('{$_SESSION["erro"]}');</script>";
    unset($_SESSION["erro"]);
}
include_once("templates/header.php")
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
                    <input type="text" name="nome" placeholder="Nome" required>
                    <label for="login">Login (Email):</label>
                    <input type="email" name="login" placeholder="Login" required>
                    <label for="senha">Senha:</label>
                    <input type="password" name="senha" placeholder="Senha" required>
                    <button type="submit" class="submit-button">Cadastrar</button>
                </form>
                <p class="secondary-action">
                    Já tem uma conta? <a href="login.php">Fazer Login</a>
                </p>
            </div>
        </div>
    </div>
</main>