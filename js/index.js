document.addEventListener('DOMContentLoaded', () => {
    // Essas variáveis virão do PHP injetadas no HTML (via <script> no final)
    const isLoggedIn = window.isLoggedIn || false;
    const csrfToken = window.csrfToken || '';

    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!isLoggedIn) {
                alert('Você precisa estar logado para curtir.');
                return;
            }

            // Evita spam de cliques
            if (btn.disabled) return;
            btn.disabled = true;

            const questionId = btn.closest('.question-item').dataset.questionId;
            const likeCountSpan = btn.querySelector('.like-count');
            const icon = btn.querySelector('i');
            const isLiked = btn.dataset.liked === 'true';

            // Feedback visual rápido
            icon.classList.add('pulse');
            icon.className = isLiked ? 'far fa-heart' : 'fas fa-heart';
            likeCountSpan.textContent = parseInt(likeCountSpan.textContent) + (isLiked ? -1 : 1);

            try {
                const response = await fetch('src/actions/like.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        action: isLiked ? 'unlike' : 'like'
                    })
                });

                const result = await response.json();

                if (result.success) {
                    likeCountSpan.textContent = result.new_count;
                    btn.dataset.liked = isLiked ? 'false' : 'true';
                    icon.className = isLiked ? 'far fa-heart' : 'fas fa-heart';
                } else {
                    alert('Erro: ' + (result.message || 'Ação inválida.'));
                    // Reverte se falhar
                    likeCountSpan.textContent = parseInt(likeCountSpan.textContent) + (isLiked ? 1 : -1);
                    icon.className = isLiked ? 'fas fa-heart' : 'far fa-heart';
                }
            } catch (err) {
                console.error('Erro no like:', err);
                alert('Erro ao curtir. Tente novamente.');
                likeCountSpan.textContent = parseInt(likeCountSpan.textContent) + (isLiked ? 1 : -1);
                icon.className = isLiked ? 'fas fa-heart' : 'far fa-heart';
            } finally {
                btn.disabled = false;
                icon.classList.remove('pulse');
            }
        });
    });
});

// Efeito visual para curtidas
const style = document.createElement('style');
style.textContent = `
@keyframes pulseLike {
  0% { transform: scale(1); color: inherit; }
  50% { transform: scale(1.3); color: #ff4f81; }
  100% { transform: scale(1); color: inherit; }
}
.pulse {
  animation: pulseLike 0.3s ease;
}
`;
document.head.appendChild(style);
