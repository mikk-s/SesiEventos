<?php
session_start();
require_once 'conexao.php';

// --- VERIFICAÇÕES INICIAIS ---
if (!isset($_SESSION["usuario_id"])) {
    $_SESSION['erro'] = "Você precisa estar logado para adquirir um ingresso.";
    header("Location: login.php");
    exit();
}
$id_usuario = $_SESSION['usuario_id'];

// --- ESTRUTURA DE CONTROLE DE ROTA (GET vs POST) ---

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // --- LÓGICA PARA PROCESSAR A COMPRA (POST) ---
    
    if (!isset($_POST['id_evento']) || !isset($_POST['quantidade'])) {
        $_SESSION['erro'] = "Requisição inválida. Tente novamente.";
        header("Location: eventos.php");
        exit();
    }

    $id_evento = $_POST['id_evento'];
    $quantidade_desejada = (int)$_POST['quantidade'];

    if ($quantidade_desejada <= 0) {
        $_SESSION['erro'] = "A quantidade de ingressos deve ser de no mínimo 1.";
        header("Location: adquirir_ingresso.php?id_evento=" . $id_evento);
        exit();
    }

    try {
        // PASSO 1: Verificar se o usuário já possui inscrição para este evento.
        $stmt_check_inscrito = $conn->prepare("SELECT COUNT(*) FROM inscricoes WHERE id_usuario = ? AND id_evento = ?");
        $stmt_check_inscrito->execute([$id_usuario, $id_evento]);
        if ($stmt_check_inscrito->fetchColumn() > 0) {
            // Lança um erro que será capturado pelo bloco catch.
            throw new Exception("Você já está inscrito neste evento.");
        }

        // PASSO 2: Obter a capacidade máxima e o número atual de ingressos vendidos.
        // Esta consulta é feita separadamente para garantir que tenhamos o estado mais recente do banco.
        $stmt_evento = $conn->prepare(
            "SELECT 
                e.max_pessoas, 
                COALESCE(SUM(i.quantidade), 0) AS inscritos 
            FROM eventos AS e
            LEFT JOIN inscricoes AS i ON e.id = i.id_evento
            WHERE e.id = ?
            GROUP BY e.id"
        );
        $stmt_evento->execute([$id_evento]);
        $evento = $stmt_evento->fetch(PDO::FETCH_ASSOC);

        if (!$evento) {
            throw new Exception("Evento não encontrado.");
        }

        $vagas_restantes = $evento['max_pessoas'] - $evento['inscritos'];

        // PASSO 3: Verificar se a quantidade desejada cabe nas vagas restantes.
        if ($quantidade_desejada > $vagas_restantes) {
            throw new Exception("Não há ingressos suficientes. Apenas {$vagas_restantes} vagas disponíveis.");
        }

        // PASSO 4: Se todas as verificações passaram, inserir a nova inscrição.
        // Como não há transação, este comando é salvo imediatamente no banco.
        $stmt_insert = $conn->prepare("INSERT INTO inscricoes (id_usuario, id_evento, quantidade) VALUES (?, ?, ?)");
        $stmt_insert->execute([$id_usuario, $id_evento, $quantidade_desejada]);

        // Se o código chegou até aqui, a inserção foi bem-sucedida.
        $_SESSION['mensagem'] = "Inscrição realizada com sucesso! Você adquiriu {$quantidade_desejada} ingresso(s).";
        header("Location: meus_ingressos.php");
        exit();

    } catch (Exception $e) {
        // O bloco 'catch' captura qualquer 'throw new Exception' e trata como um erro.
        $_SESSION['erro'] = "Erro: " . $e->getMessage();
        header("Location: adquirir_ingresso.php?id_evento=" . $id_evento);
        exit();
    }

} else {

    
    $id_evento = $_GET['id_evento'] ?? null;
    if (!$id_evento) {
        header("Location: eventos.php");
        exit();
    }

    try {
        $stmt = $conn->prepare(
            "SELECT e.id, e.nome, e.max_pessoas, COALESCE(SUM(i.quantidade), 0) AS inscritos
             FROM eventos AS e LEFT JOIN inscricoes AS i ON e.id = i.id_evento
             WHERE e.id = ? GROUP BY e.id"
        );
        $stmt->execute([$id_evento]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$evento) {
            $_SESSION['erro'] = "Evento não encontrado.";
            header("Location: eventos.php");
            exit();
        }
        $vagas_restantes = $evento['max_pessoas'] - $evento['inscritos'];
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao carregar informações do evento.";
        header("Location: eventos.php");
        exit();
    }

    include_once("templates/header.php");

    if (isset($_SESSION['erro'])) {
        echo "<script>alert('" . addslashes($_SESSION['erro']) . "');</script>";
        unset($_SESSION['erro']);
    }
    ?>
    <link rel="stylesheet" href="css/style.css">
    <main class="form-container">
        <div class="form-card">
            <h2>Adquirir Ingresso</h2>
            <h3 style="margin-bottom: 0;"><?= htmlspecialchars($evento['nome']) ?></h3>
            <p style="margin-top: 5px; color: var(--text-color-secondary);">
                <strong>Vagas restantes:</strong> <?= $vagas_restantes ?>
            </p>

            <?php if ($vagas_restantes > 0): ?>
                <form action="adquirir_ingresso.php" method="POST">
                    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                    
                    <label for="quantidade">Selecione a Quantidade de Ingressos:</label>
                    <input type="number" name="quantidade" id="quantidade" value="1" min="1" max="<?= $vagas_restantes ?>" required>
                    
                    <button type="submit" class="submit-button">Confirmar Compra</button>
                    <a href="eventos.php" style="text-align: center; display: block; margin-top: 1rem;">Cancelar</a>
                </form>
            <?php else: ?>
                <p style="text-align: center; font-weight: bold; margin-top: 2rem;">Ingressos esgotados para este evento.</p>
                <a href="eventos.php" class="submit-button" style="text-decoration: none;">Voltar para Eventos</a>
            <?php endif; ?>
        </div>
    </main>
    <?php 
    include_once("templates/footer.php");
}
?>