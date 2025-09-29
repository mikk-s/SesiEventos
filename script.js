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
    const modalAction = document.getElementById('modal-action');

    eventsGrid.addEventListener('click', function(event) {
        if (event.target.classList.contains('btn-details')) {
            event.preventDefault();
            
            const button = event.target;
            
            modalTitle.textContent = button.dataset.nome;
            modalDate.innerHTML = `<strong>Data e Horário:</strong> ${button.dataset.data}`;
            modalLocation.innerHTML = `<strong>Local:</strong> ${button.dataset.local}`;
            modalCapacity.innerHTML = `<strong>Vagas Restantes:</strong> ${button.dataset.vagasRestantes} de ${button.dataset.pessoas}`;
            modalDescription.textContent = button.dataset.descricao;

            const eventoId = button.dataset.eventoId;
            const vagasRestantes = parseInt(button.dataset.vagasRestantes);
            const isUsuarioLogado = button.dataset.usuarioLogado === 'true';
            const isUsuarioInscrito = button.dataset.usuarioInscrito === 'true';

            let actionHtml = ''; 

            if (isUsuarioLogado) {
                if (isUsuarioInscrito) {
                    // Se o usuário já está inscrito, o botão fica desabilitado
                    actionHtml = '<a href="meus_ingressos.php" class="submit-button">Você já está inscrito</a>';
                } else if (vagasRestantes <= 0) {
                    // Se não há vagas, o botão fica desabilitado
                    actionHtml = '<button class="submit-button" disabled>Ingressos Esgotados</button>';
                } else {
                    // **CORREÇÃO PRINCIPAL AQUI**
                    // Se o usuário está logado e há vagas, o botão se torna um LINK para a página de adquirir ingresso.
                    actionHtml = `<a href="adquirir_ingresso.php?id_evento=${eventoId}" class="submit-button" style="text-decoration: none;">Adquirir Ingresso</a>`;
                }
            } else {
                // Se o usuário não está logado, o botão leva para a página de login.
                actionHtml = '<a href="login.php" class="submit-button" style="text-decoration: none;">Faça login para adquirir</a>';
            }

            modalAction.innerHTML = actionHtml;
            modal.style.display = 'block';
        }
    });

    function fecharModal() {
        modal.style.display = 'none';
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