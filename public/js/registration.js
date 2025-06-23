document.addEventListener('DOMContentLoaded', function() {
    const userTypeCards = document.querySelectorAll('.user-type-card');
    const journalistFields = document.querySelector('#journalist-fields');

    // Trouver les radio buttons générés par Symfony
    const contributeurRadio = document.querySelector('input[value="contributeur"]');
    const journalisteRadio = document.querySelector('input[value="journaliste"]');

    console.log('Contributeur radio:', contributeurRadio);
    console.log('Journaliste radio:', journalisteRadio);

    userTypeCards.forEach(card => {
        card.addEventListener('click', function() {
            // Retirer la sélection de toutes les cartes
            userTypeCards.forEach(c => c.classList.remove('selected'));
            
            // Ajouter la sélection à la carte cliquée
            this.classList.add('selected');
            
            const selectedType = this.getAttribute('data-type');
            console.log('Type sélectionné:', selectedType);
            
            // Cocher le bon radio button
            if (selectedType === 'contributeur' && contributeurRadio) {
                contributeurRadio.checked = true;
                journalisteRadio.checked = false;
            } else if (selectedType === 'journaliste' && journalisteRadio) {
                journalisteRadio.checked = true;
                contributeurRadio.checked = false;
            }
            
            // Afficher/masquer les champs journaliste
            if (journalistFields) {
                journalistFields.style.display = selectedType === 'journaliste' ? 'block' : 'none';
            }
        });
    });

    // Initialiser avec contributeur par défaut
    if (contributeurRadio) {
        contributeurRadio.checked = true;
        const contributeurCard = document.querySelector('[data-type="contributeur"]');
        if (contributeurCard) {
            contributeurCard.classList.add('selected');
        }
    }

    // Masquer les champs journaliste par défaut
    if (journalistFields) {
        journalistFields.style.display = 'none';
    }

    // Validation des mots de passe
    const passwordInput = document.querySelector('#registration_form_plainPassword');
    const confirmPasswordInput = document.querySelector('#confirmPassword');
    const submitButton = document.querySelector('.btn-primary');

    if (submitButton && passwordInput && confirmPasswordInput) {
        submitButton.addEventListener('click', function(e) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
                return false;
            }

            // Vérifier qu'un type d'utilisateur est sélectionné
            const isContributeurSelected = contributeurRadio && contributeurRadio.checked;
            const isJournalisteSelected = journalisteRadio && journalisteRadio.checked;
            
            if (!isContributeurSelected && !isJournalisteSelected) {
                e.preventDefault();
                alert('Veuillez sélectionner un type d\'utilisateur.');
                return false;
            }

            // Validation spécifique pour les journalistes
            if (isJournalisteSelected) {
                const numeroCartePresse = document.querySelector('#numeroCartePresse');
                if (numeroCartePresse && !numeroCartePresse.value.trim()) {
                    e.preventDefault();
                    alert('Veuillez entrer votre numéro de carte de presse.');
                    numeroCartePresse.focus();
                    return false;
                }
            }
        });
    }
});