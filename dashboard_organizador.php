<?php
session_start();
require_once 'conexao.php';

// VERIFICAÇÃO DE PERMISSÃO: Apenas Organizador ou Administrador (Admin pode ver para depuração)
if (!isset($_SESSION['perm']) || !in_array($_SESSION['perm'], ['Organizador', 'Administrador'])) {
    $_SESSION['erro'] = "Acesso negado.";
    header("Location: index.php");
    exit();
}

// O nome do organizador é o nome do usuário logado
$organizador_nome = $_SESSION['usuario'];

try {
    // 1. Total de eventos criados pelo organizador
    $stmt_total_eventos = $conn->prepare("SELECT COUNT(*) FROM eventos WHERE organizador = ?");
    $stmt_total_eventos->execute([$organizador_nome]);
    $total_eventos = $stmt_total_eventos->fetchColumn();

    // 2. Total de ingressos adquiridos em todos os eventos do organizador
    $stmt_total_ingressos = $conn->prepare(
        "SELECT SUM(i.quantidade) 
         FROM inscricoes i 
         JOIN eventos e ON i.id_evento = e.id 
         WHERE e.organizador = ?"
    );
    $stmt_total_ingressos->execute([$organizador_nome]);
    $total_ingressos = $stmt_total_ingressos->fetchColumn() ?: 0; // Se for nulo, retorna 0

    // 3. Total de eventos do organizador que já aconteceram (finalizados)
    $stmt_eventos_finalizados = $conn->prepare("SELECT COUNT(*) FROM eventos WHERE organizador = ? AND data < NOW()");
    $stmt_eventos_finalizados->execute([$organizador_nome]);
    $eventos_finalizados = $stmt_eventos_finalizados->fetchColumn();

} catch (PDOException $e) {
    $erro_stats = "Não foi possível carregar as estatísticas.";
}

include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">
<style>
    /* Estilos para manter o layout similar ao do admin */
    .dashboard-container { display: flex; gap: 2rem; }
    .dashboard-sidebar { flex: 0 0 250px; }
    .dashboard-main { flex: 1; }
    .sidebar-card { background-color: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: var(--box-shadow-subtle); }
    .sidebar-card h3 { border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem; }
    .sidebar-card a { display: block; padding: 0.75rem 0; text-decoration: none; color: var(--primary-color); font-weight: 600; border-bottom: 1px solid var(--border-color); }
    .sidebar-card a:last-child { border-bottom: none; }
</style>

<main class="container">
    <h2>Painel do Organizador</h2>
    <div class="dashboard-container">
        
        <aside class="dashboard-sidebar">
            <div class="sidebar-card">
                <h3>Ferramentas</h3>
                <nav>
                    <a href="gerenciar_eventos.php">Gerenciar Meus Eventos</a>
                    <a href="gerenciar_ingressos.php">Gerenciar Ingressos</a>
                    <a href="cadastrar_evento.php">Criar Novo Evento</a>
                </nav>
            </div>
        </aside>

        <section class="dashboard-main">
            <h3>Visão Geral dos Seus Eventos</h3>
             <?php if (isset($erro_stats)): ?>
                <p class="error"><?= htmlspecialchars($erro_stats); ?></p>
            <?php else: ?>
            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="stat-card">
                    <h3>Eventos Criados</h3>
                    <p class="stat-number"><?= $total_eventos ?></p>
                </div>
                <div class="stat-card">
                    <h3>Ingressos Vendidos</h3>
                    <p class="stat-number"><?= $total_ingressos ?></p>
                </div>
                <div class="stat-card">
                    <h3>Eventos Finalizados</h3>
                    <p class="stat-number"><?= $eventos_finalizados ?></p>
                </div>
            </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<?php include_once("templates/footer.php"); ?>