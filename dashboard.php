<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['perm']) || $_SESSION['perm'] != 'Administrador') {
    $_SESSION['erro'] = "Acesso negado.";
    header("Location: index.php");
    exit();
}

try {
    $total_eventos = $conn->query("SELECT COUNT(*) FROM eventos")->fetchColumn();
    $total_usuarios = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $total_salas = $conn->query("SELECT COUNT(*) FROM locais")->fetchColumn();
    $total_inscricoes = $conn->query("SELECT SUM(quantidade) FROM inscricoes")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $erro_stats = "Não foi possível carregar as estatísticas.";
}

include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">
<style>
    .dashboard-container {
        display: flex;
        gap: 2rem;
    }
    .dashboard-sidebar {
        flex: 0 0 250px; /* Não cresce, não encolhe, base de 250px */
    }
    .dashboard-main {
        flex: 1; /* Ocupa o resto do espaço */
    }
    .sidebar-card {
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: var(--box-shadow-subtle);
    }
    .sidebar-card h3 {
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 0.5rem;
    }
    .sidebar-card a {
        display: block;
        padding: 0.75rem 0;
        text-decoration: none;
        color: var(--primary-color);
        font-weight: 600;
        border-bottom: 1px solid var(--border-color);
    }
    .sidebar-card a:last-child {
        border-bottom: none;
    }
    .sidebar-card a:hover {
        text-decoration: underline;
    }
</style>

<main class="container">
    <h2>Painel do Administrador</h2>
    <div class="dashboard-container">
        
        <aside class="dashboard-sidebar">
            <div class="sidebar-card">
                <h3>Ferramentas</h3>
                <nav>
                    <a href="gerenciar_eventos.php">Gerenciar Eventos</a>
                    <a href="gerenciar_usuarios.php">Gerenciar Usuários</a>
                    <a href="gerenciar_locais.php">Gerenciar Salas/Locais</a>
                    <a href="gerenciar_ingressos.php">Gerenciar Ingressos</a>
                </nav>
            </div>
        </aside>

        <section class="dashboard-main">
            <h3>Visão Geral do Sistema</h3>
             <?php if (isset($erro_stats)): ?>
                <p><?= htmlspecialchars($erro_stats); ?></p>
            <?php else: ?>
            <div class="dashboard-grid" style="grid-template-columns: repeat(2, 1fr);">
                <div class="stat-card">
                    <h3>Total de Eventos</h3>
                    <p class="stat-number"><?= $total_eventos ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total de Usuários</h3>
                    <p class="stat-number"><?= $total_usuarios ?></p>
                </div>
                <div class="stat-card">
                    <h3>Salas Cadastradas</h3>
                    <p class="stat-number"><?= $total_salas ?></p>
                </div>
                <div class="stat-card">
                    <h3>Ingressos Adquiridos</h3>
                    <p class="stat-number"><?= $total_inscricoes ?></p>
                </div>
            </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<?php include_once("templates/footer.php"); ?>