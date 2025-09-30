<?php
session_start();
require_once 'conexao.php';
include_once("helpers/url.php");

// --- LÓGICA DE FILTRAGEM (sem alterações) ---
$filtro_origem = $_GET['origem'] ?? 'todos';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$search_term = $_GET['search_term'] ?? '';

$eventos_usuario_inscrito = [];

try {
    $sql = "SELECT eventos.*, COALESCE(SUM(inscricoes.quantidade), 0) AS inscritos 
            FROM eventos 
            LEFT JOIN inscricoes ON eventos.id = inscricoes.id_evento";
    
    $params = [];
    $where_clauses = [];

    if (!empty($search_term)) {
        $where_clauses[] = "eventos.nome LIKE ?";
        $params[] = "%" . $search_term . "%";
    }

    if ($filtro_origem !== 'todos') {
        $where_clauses[] = "origem = ?";
        $params[] = $filtro_origem;
    }
    
    if (!empty($data_inicio) && !empty($data_fim)) {
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

include_once("templates/header.php");
// Exibe mensagens de feedback
if (isset($_SESSION['mensagem']) || isset($_SESSION['erro'])) {
    $mensagem = $_SESSION['mensagem'] ?? $_SESSION['erro'];
    echo "<script>alert('" . addslashes($mensagem) . "');</script>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['erro']);
}
?>
<main>
    <section class="search-and-filter-section" style="padding: 2rem 1rem;">
        <div class="container">
            <h2>Eventos Abertos</h2>
            <form action="eventos.php" method="GET">
                <div class="search-bar">
                    <input type="search" name="search_term" placeholder="Digite o nome de um evento..." value="<?= htmlspecialchars($search_term) ?>">
                    <button type="submit">Pesquisar</button>
                </div>
                
                <div class="filters-container">
                    <div class="filter-group">
                        <label for="event-origin">Origem:</label>
                        <select id="event-origin" name="origem" onchange="this.form.submit()">
                            <option value="todos" <?= ($filtro_origem == 'todos') ? 'selected' : '' ?>>Todos</option>
                            <option value="SESI" <?= ($filtro_origem == 'SESI') ? 'selected' : '' ?>>SESI</option>
                            <option value="SENAI" <?= ($filtro_origem == 'SENAI') ? 'selected' : '' ?>>SENAI</option>
                        </select>
                    </div>
                    <div class="filter-group date-filter-group">
                        <label for="data_inicio">Período:</label>
                        <input type="date" name="data_inicio" id="data_inicio" title="Data de início" value="<?= htmlspecialchars($data_inicio) ?>">
                        <span>até</span>
                        <input type="date" name="data_fim" id="data_fim" title="Data final" value="<?= htmlspecialchars($data_fim) ?>">
                    </div>
                    <button type="submit" class="btn-primary" style="padding: 0.6rem 1.5rem;">Aplicar Filtros</button>
                </div>
            </form>
        </div>
    </section>

    <div class="container" style="padding-top: 2rem;">
        <h2></h2>
        <section class="events-grid">
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
                <p class="no-events-message" style="grid-column: 1 / -1; text-align: center;">Nenhum evento encontrado com os filtros aplicados.</p>
            <?php endif; ?>
        </section>
    </div>
</main>
 <script src="script.js" ></script>
<?php
include_once("templates/footer.php");
?>