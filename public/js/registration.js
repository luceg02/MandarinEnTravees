// public/js/registration.js

document.addEventListener('DOMContentLoaded', function() {
    const userTypeCards = document.querySelectorAll('.user-type-card');
    const contributeurInput = document.querySelector('#registration_form_userType_0');
    const journalisteInput = document.querySelector('#registration_form_userType_1');
    const journalistFields = document.getElementById('journalist-fields');
    
    // Sélection par défaut (contributeur)
    if (userTypeCards.length > 0) {
        userTypeCards[0].classList.add('selected');
    }
    
    // Gestion des clics sur les cartes
    userTypeCards.forEach(card => {
        card.addEventListener('click', function() {
            // Retirer la sélection de toutes les cartes
            userTypeCards.forEach(c => c.classList.remove('selected'));
            
            // Ajouter la sélection à la carte cliquée
            this.classList.add('selected');
            
            // Mettre à jour le champ radio et afficher/masquer les champs journaliste
            const type = this.getAttribute('data-type');
            updateUserType(type);
        });
    });
    
    // Gestion des changements des radio buttons (au cas où ils seraient modifiés programmatiquement)
    if (contributeurInput) {
        contributeurInput.addEventListener('change', function() {
            if (this.checked) {
                updateUserType('contributeur');
                updateCardSelection('contributeur');
            }
        });
    }
    
    if (journalisteInput) {
        journalisteInput.addEventListener('change', function() {
            if (this.checked) {
                updateUserType('journaliste');
                updateCardSelection('journaliste');
            }
        });
    }
    
    // Fonction pour mettre à jour le type d'utilisateur
    function updateUserType(type) {
        if (type === 'journaliste') {
            if (journalisteInput) journalisteInput.checked = true;
            showJournalistFields();
        } else {
            if (contributeurInput) contributeurInput.checked = true;
            hideJournalistFields();
        }
    }
    
    // Fonction pour mettre à jour la sélection des cartes
    function updateCardSelection(type) {
        userTypeCards.forEach((card, index) => {
            card.classList.remove('selected');
            if (card.getAttribute('data-type') === type) {
                card.classList.add('selected');
            }
        });
    }
    
    // Fonction pour afficher les champs journaliste
    function showJournalistFields() {
        if (journalistFields) {
            journalistFields.style.display = 'block';
            
            // Rendre les champs obligatoires
            const numeroCartePresse = document.getElementById('numeroCartePresse');
            const mediaOrganisation = document.getElementById('mediaOrganisation');
            
            if (numeroCartePresse) {
                numeroCartePresse.setAttribute('required', 'required');
            }
            if (mediaOrganisation) {
                mediaOrganisation.setAttribute('required', 'required');
            }
        }
    }
    
    // Fonction pour masquer les champs journaliste
    function hideJournalistFields() {
        if (journalistFields) {
            journalistFields.style.display = 'none';
            
            // Retirer l'obligation des champs
            const numeroCartePresse = document.getElementById('numeroCartePresse');
            const mediaOrganisation = document.getElementById('mediaOrganisation');
            
            if (numeroCartePresse) {
                numeroCartePresse.removeAttribute('required');
                numeroCartePresse.value = ''; // Vider le champ
            }
            if (mediaOrganisation) {
                mediaOrganisation.removeAttribute('required');
                mediaOrganisation.value = ''; // Vider le champ
            }
        }
    }
    
    // Animation pour les cartes au survol
    userTypeCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = 'translateY(-2px)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = 'translateY(0)';
            }
        });
    });
    
    // Validation du formulaire
    const form = document.querySelector('form[name="registration_form"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const isJournalist = journalisteInput && journalisteInput.checked;
            
            // Validation des mots de passe
            const password = document.querySelector('input[name="registration_form[plainPassword]"]');
            const confirmPassword = document.getElementById('confirmPassword');
            
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
                confirmPassword.focus();
                return false;
            }
            
            // Validation des champs obligatoires
            const nom = document.getElementById('nom');
            const prenom = document.getElementById('prenom');
            
            if (!nom || !nom.value.trim()) {
                e.preventDefault();
                alert('Le nom est obligatoire.');
                if (nom) nom.focus();
                return false;
            }
            
            if (!prenom || !prenom.value.trim()) {
                e.preventDefault();
                alert('Le prénom est obligatoire.');
                if (prenom) prenom.focus();
                return false;
            }
            
            if (isJournalist) {
                const numeroCartePresse = document.getElementById('numeroCartePresse');
                
                if (!numeroCartePresse || !numeroCartePresse.value.trim()) {
                    e.preventDefault();
                    alert('Le numéro de carte de presse est obligatoire pour les journalistes.');
                    if (numeroCartePresse) numeroCartePresse.focus();
                    return false;
                }
            
            }
        });
    }
});