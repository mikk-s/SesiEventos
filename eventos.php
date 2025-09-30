<?php
session_start();
require_once 'conexao.php';
include_once("helpers/url.php");

// --- LÓGICA DE FILTRAGEM ---
$filtro_origem = $_GET['origem'] ?? 'todos';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

$eventos_usuario_inscrito = [];

try {
    $sql = "SELECT eventos.*, COALESCE(SUM(inscricoes.quantidade), 0) AS inscritos 
            FROM eventos 
            LEFT JOIN inscricoes ON eventos.id = inscricoes.id_evento";
    
    $params = [];
    $where_clauses = [];

    // Filtro por origem
    if ($filtro_origem !== 'todos') {
        $where_clauses[] = "origem = ?";
        $params[] = $filtro_origem;
    }
    
    // **NOVO: Filtro por data**
    if (!empty($data_inicio) && !empty($data_fim)) {
        // Adiciona a hora final do dia para incluir todos os eventos do último dia
        $where_clauses[] = "data BETWEEN ? AND ?";
        $params[] = $data_inicio;
        $params[] = $data_fim . ' 23:59:59';
    }

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }

    $sql .= " GROUP BY eventos.id ORDER BY data ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $eventos = $stmt->fetchAll();

    if (isset($_SESSION['usuario_id'])) {
        $stmt_inscrito = $conn->prepare("SELECT id_evento FROM inscricoes WHERE id_usuario = ?");
        $stmt_inscrito->execute([$_SESSION['usuario_id']]);
        $eventos_usuario_inscrito = $stmt_inscrito->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (PDOException $e) {
    $eventos = [];
    echo "Erro ao buscar eventos: " . $e->getMessage();
}
if (isset($_SESSION['mensagem']) || isset($_SESSION['erro'])) {
    $mensagem = $_SESSION['mensagem'] ?? $_SESSION['erro'];
    echo "<script>alert('" . addslashes($mensagem) . "');</script>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['erro']);
}
include_once("templates/header.php");
?>
<main>
<section class="filter-section">
    <h1>Eventos Abertos</h1>
    
    <form method="GET" action="eventos.php" class="filter-form">
        <label for="event-origin">Filtrar por Origem:</label>
        <select id="event-origin" name="origem">
            <option value="todos" <?= ($filtro_origem == 'todos') ? 'selected' : '' ?>>Todos</option>
            <option value="sesi" <?= ($filtro_origem == 'sesi') ? 'selected' : '' ?>>SESI</option>
            <option value="senai" <?= ($filtro_origem == 'senai') ? 'selected' : '' ?>>SENAI</option>
        </select>

        <label for="data_inicio">De:</label>
        <input type="date" name="data_inicio" id="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>">
        <label for="data_fim">Até:</label>
        <input type="date" name="data_fim" id="data_fim" value="<?= htmlspecialchars($data_fim) ?>">

        <button type="submit" class="btn-primary" style="padding: 0.5rem 1rem;">Filtrar</button>
        <a href="eventos.php" style="margin-left: 1rem;">Limpar Filtros</a>
    </form>
    
</section>
    <section class="events-grid container">
    <?php if (!empty($eventos)): ?>
        <?php 
        foreach ($eventos as $evento):
            $vagas_restantes = ($evento['max_pessoas'] > 0) ? $evento['max_pessoas'] - $evento['inscritos'] : PHP_INT_MAX;
            $data_formatada = (new DateTime($evento['data']))->format('d/m/Y, H:i');
            $usuario_logado = isset($_SESSION['usuario_id']);
            $usuario_inscrito = in_array($evento['id'], $eventos_usuario_inscrito);

            include 'templates/event_card.php'; 
        endforeach; 
        ?>
    <?php else: ?>
        <p class="no-events-message">Nenhum evento encontrado com os filtros aplicados.</p>
    <?php endif; ?>
</section>
</main>

<?php
include_once("templates/footer.php");
?>