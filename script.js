document.addEventListener('DOMContentLoaded', function() {
    
    const modal = document.getElementById('event-modal');
    const eventsGrid = document.querySelector('.events-grid');

    if (!modal || !eventsGrid) {
        return; 
    }

    const closeButton = document.querySelector('.close-button');
    const modalTitle = document.getElementById('modal-title');
    const modalDate = document.getElementById('modal-date');
    const modalLocation = document.getElementById('modal-location');
    const modalCapacity = document.getElementById('modal-capacity');
    const modalDescription = document.getElementById('modal-description');
    // Pega o novo container do botão
    const modalAction = document.getElementById('modal-action');

    eventsGrid.addEventListener('click', function(event) {
        if (event.target.classList.contains('btn-details')) {
            event.preventDefault();
            
            const button = event.target;
            
            // 1. Preenche as informações do evento (como antes)
            modalTitle.textContent = button.dataset.nome;
            modalDate.innerHTML = `<strong>Data e Horário:</strong> ${button.dataset.data}`;
            modalLocation.innerHTML = `<strong>Local:</strong> ${button.dataset.local}`;
            // Atualiza a capacidade para usar as vagas restantes
            modalCapacity.innerHTML = `<strong>Vagas Restantes:</strong> ${button.dataset.vagasRestantes} de ${button.dataset.pessoas}`;
            modalDescription.textContent = button.dataset.descricao;

            // 2. LÊ OS NOVOS DADOS E CRIA O BOTÃO DE AÇÃO
            const eventoId = button.dataset.eventoId;
            const vagasRestantes = parseInt(button.dataset.vagasRestantes);
            const isUsuarioLogado = button.dataset.usuarioLogado === 'true';
            const isUsuarioInscrito = button.dataset.usuarioInscrito === 'true';

            let actionHtml = ''; // Variável para guardar o HTML do botão

            if (isUsuarioLogado) {
                if (isUsuarioInscrito) {
                    actionHtml = '<button class="btn-inscrito" disabled>Você já está inscrito</button>';
                } else if (vagasRestantes <= 0) {
                    actionHtml = '<button class="btn-esgotado" disabled>Ingressos Esgotados</button>';
                } else {
                    actionHtml = `
                        <form method="POST" action="adquirir_ingresso.php" style="margin: 0;">
                            <input type="hidden" name="id_evento" value="${eventoId}">
                            <button type="submit" class="btn-adquirir">Adquirir Ingresso</button>
                        </form>
                    `;
                }
            } else {
                actionHtml = '<a href="login.php" class="btn-login-adquirir">Faça login para adquirir</a>';
            }

            // 3. Insere o HTML do botão no container do modal
            modalAction.innerHTML = actionHtml;

            // 4. Exibe o modal
            modal.style.display = 'block';
        }
    });

    function fecharModal() {
        modal.style.display = 'none';
        // Limpa o botão de ação para não aparecer no próximo modal
        modalAction.innerHTML = '';
    }

    if (closeButton) {
        closeButton.addEventListener('click', fecharModal);
    }

    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            fecharModal();
        }
    });
});