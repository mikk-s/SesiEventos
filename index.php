<?php
session_start();
require_once 'conexao.php';

// --- LÓGICA DE FILTRAGEM E DADOS ---
$filtro_origem = $_GET['origem'] ?? 'todos';
$eventos_usuario_inscrito = [];

try {
    $sql = "SELECT eventos.*, COUNT(inscricoes.id) AS inscritos 
            FROM eventos 
            LEFT JOIN inscricoes ON eventos.id = inscricoes.id_evento";
    
    $params = [];
    if ($filtro_origem !== 'todos') {
        $sql .= " WHERE origem = ?";
        $params[] = $filtro_origem;
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


// Exibe mensagens de sucesso ou erro vindas de outras páginas
if (isset($_SESSION['mensagem']) || isset($_SESSION['erro'])) {
    $mensagem = $_SESSION['mensagem'] ?? $_SESSION['erro'];
    echo "<script>alert('" . addslashes($mensagem) . "');</script>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['erro']);
}
?>
<main>
<section class="filter-section">
    <h1>Eventos Abertos</h1>
    
    <form method="GET" action="index.php" class="filter-form">
        <label for="event-origin">Filtrar por Origem:</label>
        <select id="event-origin" name="origem" onchange="this.form.submit()">
            <option value="todos" <?= ($filtro_origem == 'todos') ? 'selected' : '' ?>>Todos</option>
            <option value="sesi" <?= ($filtro_origem == 'sesi') ? 'selected' : '' ?>>SESI</option>
            <option value="senai" <?= ($filtro_origem == 'senai') ? 'selected' : '' ?>>SENAI</option>
        </select>
    </form>
    
</section>
    <section class="events-grid container">
    <?php if (!empty($eventos)): ?>
        <?php foreach ($eventos as $evento):
            $vagas_restantes = $evento['max_pessoas'] - $evento['inscritos'];
            $data_formatada = (new DateTime($evento['data']))->format('d/m/Y, H:i');
        ?>
            <div class="event-card card">
                <div class="event-card-image"></div> 
                
                <div class="event-card-content">
                    <h3><?= htmlspecialchars($evento['nome']) ?></h3>
                    <p class="event-info">Data: <?= $data_formatada ?></p>
                    <p class="event-info">Local: <?= htmlspecialchars($evento['local']) ?></p>
                    <p class="event-info">Vagas: <?= $vagas_restantes ?> de <?= htmlspecialchars($evento['max_pessoas']) ?></p>
                    
                    <a href="#" class="btn-primary btn-details"
                       data-nome="<?= htmlspecialchars($evento['nome']) ?>"
                       data-data="<?= $data_formatada ?>"
                       data-local="<?= htmlspecialchars($evento['local']) ?>"
                       data-pessoas="<?= htmlspecialchars($evento['max_pessoas']) ?>"
                       data-descricao="<?= htmlspecialchars($evento['descricao_completa']) ?>"
                       data-evento-id="<?= $evento['id'] ?>"
                       data-vagas-restantes="<?= $vagas_restantes ?>"
                       data-usuario-logado="<?= isset($_SESSION['usuario_id']) ? 'true' : 'false' ?>"
                       data-usuario-inscrito="<?= in_array($evento['id'], $eventos_usuario_inscrito) ? 'true' : 'false' ?>">
                       Saiba Mais
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-events-message">Sem eventos no momento.</p>
    <?php endif; ?>
</section>
</main>

<?php
include_once("templates/footer.php");
?>