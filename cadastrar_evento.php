<?php
session_start();
require "conexao.php";

// 1. VERIFICAÇÃO DE LOGIN E PERMISSÃO
// Primeiro, checa se o usuário está logado
if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você não está logado! Por favor, faça o login.";
    header("Location: login.php");
    exit();
}

// Segundo, checa se a permissão do usuário é válida para acessar a página
if (isset($_SESSION['perm']) && $_SESSION['perm'] == "Visitante") {
    $_SESSION['erro'] = "Acesso negado. Você não tem permissão para acessar esta página.";
    header("Location: index.php"); // Redireciona para a página inicial
    exit();
}

// 2. BUSCA OS LOCAIS DISPONÍVEIS NO BANCO
try {
    $locais_stmt = $conn->query("SELECT * FROM locais ORDER BY bloco, sala");
    $locais = $locais_stmt->fetchAll();
} catch (PDOException $e) {
    $locais = []; // Se der erro, a lista de locais fica vazia
    $_SESSION['mensagem'] = "Erro ao carregar os locais: " . $e->getMessage();
}


// 3. Processa o formulário quando ele é enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST['nome'] ?? null;
    $data = $_POST['data'] ?? null;
    $local = $_POST['local'] ?? null; // O valor será "Sala X - Bloco Y"
    $max_pessoas = $_POST['max_pessoas'] ?? null;
    $origem = $_POST['origem'] ?? null;
    $descricao_completa = $_POST['descricao_completa'] ?? null;
    $organizador = $_SESSION['usuario'];

    if (empty($nome) || empty($data) || empty($local) || empty($max_pessoas) || empty($origem)) {
        $_SESSION['mensagem'] = "Erro: Por favor, preencha todos os campos obrigatórios.";
    } else {
        try {
            $sql = "INSERT INTO eventos (nome, data, local, max_pessoas, origem, descricao_completa, organizador) 
                    VALUES (:nome, :data, :local, :max_pessoas, :origem, :descricao_completa, :organizador)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":nome", $nome);
            $stmt->bindParam(":data", $data);
            $stmt->bindParam(":local", $local);
            $stmt->bindParam(":max_pessoas", $max_pessoas, PDO::PARAM_INT);
            $stmt->bindParam(":origem", $origem);
            $stmt->bindParam(":descricao_completa", $descricao_completa);
            $stmt->bindParam(":organizador", $organizador);

            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Evento cadastrado com sucesso!";
            } else {
                $_SESSION['mensagem'] = "Erro ao cadastrar o evento.";
            }

        } catch (PDOException $e) {
            $_SESSION['mensagem'] = "Erro no banco de dados: " . $e->getMessage();
        }
    }
    
    header("Location: cadastrar_evento.php");
    exit();
}

include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">

<main class="form-container">
    <div class="form-card">
        <h2>Cadastrar Novo Evento</h2>

        <form method="POST" action="cadastrar_evento.php">
            
            <label for="nome">Nome do Evento:</label>
            <input type="text" id="nome" name="nome" required>
            
            <label for="data">Data e Hora:</label>
            <input type="datetime-local" id="data" name="data" required>
            
            <label for="local">Local:</label>
            <select id="local" name="local" required>
                <option value="">-- Selecione um local --</option>
                <?php if (count($locais) > 0): ?>
                    <?php foreach ($locais as $local_item): ?>
                        <?php 
                            // Concatena sala e bloco para o valor e texto da opção
                            $valor_opcao = htmlspecialchars($local_item['sala']) . ' - Bloco ' . htmlspecialchars($local_item['bloco']);
                        ?>
                        <option value="<?= $valor_opcao ?>"><?= $valor_opcao ?></option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>Nenhum local cadastrado</option>
                <?php endif; ?>
            </select>
            
            <label for="max_pessoas">Lotação Máxima:</label>
            <input type="number" id="max_pessoas" name="max_pessoas" min="1" required>
            
            <label for="origem">Origem:</label>
            <select id="origem" name="origem" required>
                <option value="">-- Selecione a origem --</option>
                <option value="SESI">SESI</option>
                <option value="SENAI">SENAI</option>
            </select>
            
            <label for="descricao_completa">Descrição Completa:</label>
            <textarea id="descricao_completa" name="descricao_completa" rows="4"></textarea>

            <button type="submit" class="submit-button">Cadastrar Evento</button>

        </form>
    </div>
</main>

<?php
if (isset($_SESSION['mensagem']) || isset($_SESSION['erro'])) {
    $mensagem = $_SESSION['mensagem'] ?? $_SESSION['erro'];
    echo "<script>alert('" . addslashes($mensagem) . "');</script>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['erro']);
}
?>

</body>
</html>