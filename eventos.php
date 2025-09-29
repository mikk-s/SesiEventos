<?php
session_start();
require_once 'conexao.php';
include_once("helpers/url.php");

// --- LÓGICA DE FILTRAGEM E DADOS ---
$filtro_origem = $_GET['origem'] ?? 'todos';
$eventos_usuario_inscrito = [];

try {
    // CORREÇÃO: A consulta agora SOMA a quantidade de ingressos em vez de contar as linhas de inscrição.
    $sql = "SELECT 
                eventos.*, 
                COALESCE(SUM(inscricoes.quantidade), 0) AS inscritos 
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
    
    <form method="GET" action="eventos.php" class="filter-form">
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
        <?php 
        // Itera sobre os eventos para poder passar os dados corretos para o template do card
        foreach ($eventos as $evento):
            $vagas_restantes = $evento['max_pessoas'] - $evento['inscritos'];
            $data_formatada = (new DateTime($evento['data']))->format('d/m/Y, H:i');
            $usuario_logado = isset($_SESSION['usuario_id']);
            $usuario_inscrito = in_array($evento['id'], $eventos_usuario_inscrito);

            // Inclui o template do card, que agora terá acesso às variáveis acima
            include 'templates/event_card.php'; 
        endforeach; 
        ?>
    <?php else: ?>
        <p class="no-events-message">Sem eventos no momento.</p>
    <?php endif; ?>
</section>
</main>

<?php
include_once("templates/footer.php");
?>