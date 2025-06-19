// JavaScript pour la page détail

// Copier l'URL dans le presse-papier pour le partage
function partagerDemande() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        alert('Lien copié dans le presse-papier !');
    });
}

// Fonction pour voter sur une réponse
function voterReponse(reponseId, typeVote) {
    // Vérifier si l'utilisateur est connecté côté client
    const userConnected = document.body.dataset.userConnected === 'true';
    
    if (!userConnected) {
        alert('Vous devez être connecté pour voter');
        return;
    }

    fetch(`/vote/reponse/${reponseId}/${typeVote}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour les compteurs
            document.getElementById(`votes-positifs-${reponseId}`).textContent = data.nbVotesPositifs;
            document.getElementById(`votes-negatifs-${reponseId}`).textContent = data.nbVotesNegatifs;
            
            // Afficher un message de succès
            showMessage('success', data.message);
        } else {
            showMessage('error', data.error);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showMessage('error', 'Erreur lors du vote');
    });
}

// Fonction pour afficher les messages
function showMessage(type, message) {
    // TODO: Implémenter des notifications plus élégantes
    alert(message);
}

// Scroll vers le formulaire quand on clique sur "Contribuer"
document.addEventListener('DOMContentLoaded', function() {
    const liens = document.querySelectorAll('.scroll-to-form');
    liens.forEach(function(lien) {
        lien.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.querySelector('.comment-form');
            if (form) {
                form.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});