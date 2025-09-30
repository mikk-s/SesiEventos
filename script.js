// script.js

document.addEventListener('DOMContentLoaded', function() {
    
    const modal = document.getElementById('event-modal');
    if (!modal) {
        console.error("Elemento do modal não encontrado.");
        return; 
    }

    const closeButton = modal.querySelector('.close-button');
    const modalImage = document.getElementById('modal-image');
    const modalTitle = document.getElementById('modal-title');
    const modalDate = document.getElementById('modal-date');
    const modalLocation = document.getElementById('modal-location');
    const modalCapacity = document.getElementById('modal-capacity');
    const modalDescription = document.getElementById('modal-description');
    const modalAction = document.getElementById('modal-action');

    // CORREÇÃO PRINCIPAL: O listener de eventos agora é no documento inteiro.
    // Isso garante que ele capture cliques em botões .btn-details em qualquer lugar,
    // seja em carrosséis ou na grade de eventos.
    document.addEventListener('click', function(event) {
        
        // Usamos .closest() para garantir que pegamos o botão, mesmo que o clique seja num ícone dentro dele
        const button = event.target.closest('.btn-details');

        if (button) {
            event.preventDefault(); // Previne qualquer comportamento padrão do link
            
            // Preenche os dados do modal usando os atributos do botão
            modalImage.src = button.dataset.imagem || 'img/placeholder.jpg';
            modalTitle.textContent = button.dataset.nome;
            modalDate.innerHTML = `<strong>Data:</strong> ${button.dataset.data}`;
            modalLocation.innerHTML = `<strong>Local:</strong> ${button.dataset.local}`;
            modalDescription.textContent = button.dataset.descricao;

            const maxPessoas = parseInt(button.dataset.pessoas);
            if (maxPessoas > 0) {
                modalCapacity.innerHTML = `<strong>Vagas Restantes:</strong> ${button.dataset.vagasRestantes} de ${button.dataset.pessoas}`;
            } else {
                modalCapacity.innerHTML = `<strong>Vagas:</strong> Ilimitadas`;
            }

            const eventoId = button.dataset.eventoId;
            const vagasRestantes = parseInt(button.dataset.vagasRestantes);
            const isUsuarioLogado = button.dataset.usuarioLogado === 'true';
            const isUsuarioInscrito = button.dataset.usuarioInscrito === 'true';

            let actionHtml = ''; 
            if (isUsuarioLogado) {
                if (isUsuarioInscrito) {
                    actionHtml = '<a href="meus_ingressos.php" class="submit-button">Ver Meus Ingressos</a>';
                } else if (vagasRestantes <= 0) {
                    actionHtml = '<button class="submit-button" disabled>Ingressos Esgotados</button>';
                } else {
                    actionHtml = `<a href="adquirir_ingresso.php?id_evento=${eventoId}" class="submit-button" style="text-decoration: none;">Adquirir Ingresso</a>`;
                }
            } else {
                actionHtml = '<a href="login.php" class="submit-button" style="text-decoration: none;">Faça login para adquirir</a>';
            }

            modalAction.innerHTML = actionHtml;
            modal.style.display = 'flex'; // Usamos 'flex' para centralizar o conteúdo
        }
    });

    // Função para fechar o modal
    function fecharModal() {
        modal.style.display = 'none';
        modalAction.innerHTML = '';
    }

    if (closeButton) {
        closeButton.addEventListener('click', fecharModal);
    }

    // Fecha o modal se o usuário clicar fora da caixa de conteúdo
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            fecharModal();
        }
    });

    // Fecha o modal se o usuário pressionar a tecla 'Escape'
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'flex') {
            fecharModal();
        }
    });
});