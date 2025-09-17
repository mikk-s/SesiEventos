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
            <div class="filter-options">
                <label for="event-origin">Filtrar por Origem:</label>
                <select id="event-origin">
                    <option value="todos">Todos</option>
                    <option value="sesi">SESI</option>
                    <option value="senai">SENAI</option>
                </select>
            </div>
        </section>

    <section class="events-grid">
        <?php if (count($eventos) > 0): ?>
            <?php foreach ($eventos as $evento): ?>
                <?php
                    $data_formatada = (new DateTime($evento['data']))->format('d/m/Y, H:i');
                    $vagas_restantes = $evento['max_pessoas'] - $evento['inscritos'];
                ?>
                <div class="event-card">
                    <h2><?= htmlspecialchars($evento['nome']) ?></h2>
                    <p class="event-info"><strong>Data:</strong> <?= $data_formatada ?></p>
                    <p class="event-info"><strong>Local:</strong> <?= htmlspecialchars($evento['local']) ?></p>
                    <p class="event-info"><strong>Vagas Restantes:</strong> <?= $vagas_restantes ?></p>
                    <p class="event-origin">Origem: <?= htmlspecialchars($evento['origem']) ?></p>
                    
                    <a href="#" class="btn-details"
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
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-events-message">Sem eventos no momento.</p>
        <?php endif; ?>
    </section>
</main>

<?php
include_once("templates/footer.php");
?>