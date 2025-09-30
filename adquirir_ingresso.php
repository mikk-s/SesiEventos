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
        $stmt = $conn->prepare(
            "SELECT 
                e.max_pessoas, e.limite_por_usuario,
                COALESCE((SELECT SUM(quantidade) FROM inscricoes WHERE id_evento = e.id), 0) AS inscritos,
                COALESCE((SELECT quantidade FROM inscricoes WHERE id_evento = e.id AND id_usuario = :id_usuario), 0) AS meus_ingressos_atuais
            FROM eventos AS e WHERE e.id = :id_evento"
        );
        $stmt->bindParam(":id_evento", $id_evento);
        $stmt->bindParam(":id_usuario", $id_usuario);
        $stmt->execute();
        $evento_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$evento_info) { throw new Exception("Evento não encontrado."); }

        // Valida o limite por usuário (isso continua valendo)
        $total_ingressos_apos_compra = $evento_info['meus_ingressos_atuais'] + $quantidade_desejada;
        if ($total_ingressos_apos_compra > $evento_info['limite_por_usuario']) {
            $permitido = $evento_info['limite_por_usuario'] - $evento_info['meus_ingressos_atuais'];
            throw new Exception("Limite por usuário excedido. Você pode adquirir no máximo mais {$permitido} ingresso(s).");
        }

        // **CORREÇÃO PRINCIPAL (POST):** Só valida as vagas se o evento NÃO for ilimitado.
        if ($evento_info['max_pessoas'] > 0) {
            $vagas_restantes = $evento_info['max_pessoas'] - $evento_info['inscritos'];
            if ($quantidade_desejada > $vagas_restantes) {
                throw new Exception("Não há ingressos suficientes. Apenas {$vagas_restantes} vagas disponíveis.");
            }
        }

        // Lógica de inserir ou atualizar a inscrição (sem alterações)
        if ($evento_info['meus_ingressos_atuais'] > 0) {
            $stmt_update = $conn->prepare("UPDATE inscricoes SET quantidade = ? WHERE id_evento = ? AND id_usuario = ?");
            $stmt_update->execute([$total_ingressos_apos_compra, $id_evento, $id_usuario]);
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO inscricoes (id_usuario, id_evento, quantidade) VALUES (?, ?, ?)");
            $stmt_insert->execute([$id_usuario, $id_evento, $quantidade_desejada]);
        }

        $_SESSION['mensagem'] = "Compra realizada com sucesso!";
        header("Location: meus_ingressos.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['erro'] = "Erro: " . $e->getMessage();
        header("Location: adquirir_ingresso.php?id_evento=" . $id_evento);
        exit();
    }

} else {

    // --- LÓGICA PARA EXIBIR A PÁGINA DE SELEÇÃO (GET) ---
    
    $id_evento = $_GET['id_evento'] ?? null;
    if (!$id_evento) { header("Location: eventos.php"); exit(); }

    try {
        $stmt = $conn->prepare(
            "SELECT e.id, e.nome, e.max_pessoas, e.limite_por_usuario,
                COALESCE(SUM(i.quantidade), 0) AS inscritos,
                COALESCE((SELECT quantidade FROM inscricoes WHERE id_evento = e.id AND id_usuario = :id_usuario), 0) AS meus_ingressos
             FROM eventos AS e LEFT JOIN inscricoes AS i ON e.id = i.id_evento
             WHERE e.id = :id_evento GROUP BY e.id"
        );
        $stmt->bindParam(":id_evento", $id_evento);
        $stmt->bindParam(":id_usuario", $id_usuario);
        $stmt->execute();
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$evento) { header("Location: eventos.php"); exit(); }

        // **CORREÇÃO PRINCIPAL (GET):** Calcula o máximo que o usuário pode comprar.
        // Se max_pessoas é 0 (ilimitado), as vagas restantes são tratadas como um número gigante (infinito).
        $vagas_restantes = ($evento['max_pessoas'] > 0) 
            ? $evento['max_pessoas'] - $evento['inscritos'] 
            : PHP_INT_MAX; 

        $limite_compra_usuario = $evento['limite_por_usuario'] - $evento['meus_ingressos'];
        $max_compra = min($vagas_restantes, $limite_compra_usuario);

    } catch (PDOException $e) { /* ... */ }

    include_once("templates/header.php");
    if (isset($_SESSION['erro'])) { echo "<script>alert('" . addslashes($_SESSION['erro']) . "');</script>"; unset($_SESSION['erro']); }
    ?>
    <link rel="stylesheet" href="css/style.css">
    <main class="form-container">
        <div class="form-card">
            <h2>Adquirir Ingresso</h2>
            <h3 style="margin-bottom: 0;"><?= htmlspecialchars($evento['nome']) ?></h3>
            <p style="margin-top: 5px; color: var(--text-color-secondary);">
                <strong>Vagas restantes:</strong> <?= ($evento['max_pessoas'] > 0) ? $vagas_restantes : 'Ilimitadas' ?> | 
                <strong>Limite por usuário:</strong> <?= $evento['limite_por_usuario'] ?>
            </p>
            <?php if ($evento['meus_ingressos'] > 0): ?>
                <p>Você já possui <?= $evento['meus_ingressos'] ?> ingresso(s) para este evento.</p>
            <?php endif; ?>

            <?php if ($max_compra > 0): ?>
                <form action="adquirir_ingresso.php" method="POST">
                    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                    <label for="quantidade">Selecione a Quantidade de Ingressos:</label>
                    <input type="number" name="quantidade" id="quantidade" value="1" min="1" max="<?= $max_compra ?>" required>
                    <button type="submit" class="submit-button">Confirmar Compra</button>
                </form>
            <?php else: ?>
                <p style="text-align: center; font-weight: bold; margin-top: 2rem;">
                    <?php if ($vagas_restantes <= 0 && $evento['max_pessoas'] > 0): ?>
                        Ingressos esgotados para este evento.
                    <?php else: ?>
                        Você atingiu o limite de ingressos para este evento.
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
    </main>
    <?php 
    include_once("templates/footer.php");
}
?>