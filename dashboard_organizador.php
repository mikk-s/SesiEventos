<?php
session_start();
require_once 'conexao.php';

// VERIFICAÇÃO DE PERMISSÃO DE Organizador
if (!isset($_SESSION['perm']) || $_SESSION['perm'] != 'Organizador') {
    $_SESSION['erro'] = "Acesso negado. Esta página é apenas para Organizadores.";
    header("Location: index.php");
    exit();
}

$organizador_nome = $_SESSION['usuario'];
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

try {
    // Base da Query
    $params = [$organizador_nome];
    $sql_total_eventos = "SELECT COUNT(*) FROM eventos WHERE organizador = ?";
    $sql_total_ingressos = "SELECT SUM(i.quantidade) FROM inscricoes i JOIN eventos e ON i.id_evento = e.id WHERE e.organizador = ?";
    $sql_eventos_finalizados = "SELECT COUNT(*) FROM eventos WHERE organizador = ? AND data < NOW()";

    // Adiciona filtro de data se fornecido
    if ($data_inicio && $data_fim) {
        $sql_total_eventos .= " AND data BETWEEN ? AND ?";
        $sql_total_ingressos .= " AND e.data BETWEEN ? AND ?";
        $sql_eventos_finalizados .= " AND data BETWEEN ? AND ?";
        array_push($params, $data_inicio, $data_fim);
    }

    $stmt_total_eventos = $conn->prepare($sql_total_eventos);
    $stmt_total_eventos->execute($params);
    $total_eventos = $stmt_total_eventos->fetchColumn();

    $stmt_total_ingressos = $conn->prepare($sql_total_ingressos);
    $stmt_total_ingressos->execute($params);
    $total_ingressos = $stmt_total_ingressos->fetchColumn() ?: 0;
    
    // O filtro de data não se aplica a eventos já finalizados de forma lógica, então usamos os params originais.
    $stmt_eventos_finalizados = $conn->prepare($sql_eventos_finalizados);
    $stmt_eventos_finalizados->execute([$organizador_nome]);
    $eventos_finalizados = $stmt_eventos_finalizados->fetchColumn();

} catch (PDOException $e) {
    $erro_stats = "Não foi possível carregar as estatísticas: " . $e->getMessage();
}

include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">

<main class="form-container">
    <div class="form-card" style="max-width: 1000px;">
        <h2>Painel do Organizador</h2>
        <p>Bem-vindo, <?= htmlspecialchars($organizador_nome) ?>. Aqui estão as estatísticas dos seus eventos.</p>

        <form method="GET" class="filter-form" style="margin-bottom: 2rem; text-align: center;">
            <h4>Filtrar por Data do Evento</h4>
            <input type="date" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>">
            <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>">
            <button type="submit" class="btn-primary" style="padding: 0.5rem 1rem;">Filtrar</button>
            <a href="dashboard_organizador.php" style="text-decoration: none;">Limpar Filtro</a>
        </form>
        
        <?php if (isset($erro_stats)): ?>
            <p><?= htmlspecialchars($erro_stats); ?></p>
        <?php else: ?>
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3>Total de Eventos Criados</h3>
                <p class="stat-number"><?= $total_eventos ?></p>
            </div>
            <div class="stat-card">
                <h3>Ingressos Adquiridos (nos seus eventos)</h3>
                <p class="stat-number"><?= $total_ingressos ?></p>
            </div>
            <div class="stat-card">
                <h3>Eventos Finalizados</h3>
                <p class="stat-number"><?= $eventos_finalizados ?></p>
            </div>
        </div>
        <?php endif; ?>

        <hr style="margin: 2rem 0;">
        <h3>Ferramentas de Gerenciamento</h3>
        <div class="dashboard-grid admin-actions">
            <div class="stat-card">
                <h3>Eventos</h3>
                <p>Adicionar, editar e remover seus eventos.</p>
                <a href="gerenciar_eventos.php">Gerenciar Meus Eventos</a>
            </div>
            <div class="stat-card">
                <h3>Ingressos</h3>
                <p>Visualizar e revogar ingressos dos seus eventos.</p>
                <a href="gerenciar_ingressos.php">Gerenciar Ingressos</a>
            </div>
        </div>
    </div>
</main>

<?php include_once("templates/footer.php"); ?>