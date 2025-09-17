<?php
session_start();
require_once 'conexao.php';

// 1. Verifica se o usuário está logado. Se não, redireciona para o login.
if (!isset($_SESSION["usuario_id"])) {
    $_SESSION['erro'] = "Você precisa estar logado para ver seus ingressos.";
    header("Location: login.php");
    exit();
}

$meus_eventos = [];
$id_usuario = $_SESSION['usuario_id'];

// 2. Busca no banco de dados todos os eventos associados ao ID do usuário logado.
try {
    // A consulta SQL une as tabelas 'inscricoes' e 'eventos' para pegar os detalhes dos eventos
    // em que o usuário está inscrito.
    $sql = "SELECT 
                e.nome, 
                e.data, 
                e.local, 
                e.origem,
                i.data_inscricao
            FROM inscricoes AS i
            JOIN eventos AS e ON i.id_evento = e.id
            WHERE i.id_usuario = ?
            ORDER BY e.data ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_usuario]);
    $meus_eventos = $stmt->fetchAll();

} catch (PDOException $e) {
    // Em caso de erro, define $meus_eventos como um array vazio e exibe uma mensagem
    $meus_eventos = [];
    echo "Erro ao buscar seus ingressos: " . $e->getMessage();
}

// Inclui o cabeçalho da página
include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">
<main class="form-container">
    <div class="form-card" style="max-width: 900px;"> <h2>Meus Ingressos</h2>

        <?php if (count($meus_eventos) > 0): ?>
            <p>Aqui estão todos os eventos para os quais você adquiriu ingresso.</p>
            
            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Evento</th>
                            <th>Data do Evento</th>
                            <th>Local</th>
                            <th>Origem</th>
                            <th>Data da Inscrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meus_eventos as $evento): ?>
                            <tr>
                                <td><?= htmlspecialchars($evento['nome']) ?></td>
                                <td><?= (new DateTime($evento['data']))->format('d/m/Y, H:i') ?></td>
                                <td><?= htmlspecialchars($evento['local']) ?></td>
                                <td><?= htmlspecialchars($evento['origem']) ?></td>
                                <td><?= (new DateTime($evento['data_inscricao']))->format('d/m/Y') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <p class="no-events-message" style="grid-column: 1; margin-top: 1rem;">Você ainda não adquiriu nenhum ingresso.</p>
        <?php endif; ?>
    </div>
</main>
<?php
// Inclui o rodapé da página
include_once("templates/footer.php");
?>