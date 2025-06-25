// JavaScript pour la page détail avec système de fact-checking

// ========================================================================
// INITIALISATION AU CHARGEMENT DE LA PAGE
// ========================================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initialisation du système de fact-checking');
    
    // Initialiser tous les systèmes
    initVotesUtilite();
    initVotesVeracite();
    initFormulaires();
    initTooltips();
    initScrollActions();
    initGestionSuppressions();
    
    console.log('✅ Tous les systèmes initialisés');
});

// ========================================================================
// SYSTÈME 1 : VOTES D'UTILITÉ SUR LES RÉPONSES
// ========================================================================

/**
 * Initialise les boutons de vote d'utilité
 */
function initVotesUtilite() {
    const boutons = document.querySelectorAll('.vote-btn[data-reponse-id]');
    
    boutons.forEach(function(bouton) {
        bouton.addEventListener('click', function() {
            const reponseId = this.dataset.reponseId;
            const typeVote = this.dataset.type;
            
            // Vérifier que les données sont présentes
            if (!reponseId || !typeVote) {
                console.error('Données manquantes pour le vote:', {reponseId, typeVote});
                return;
            }
            
            voterReponse(reponseId, typeVote);
        });
    });
    
    console.log(`📊 ${boutons.length} boutons de vote d'utilité initialisés`);
}

/**
 * Effectue un vote d'utilité sur une réponse (AJAX)
 */
function voterReponse(reponseId, typeVote) {
    console.log(`🗳️ Vote d'utilité: ${typeVote} pour réponse ${reponseId}`);
    
    // Désactiver temporairement les boutons pour éviter les double-clics
    const boutons = document.querySelectorAll(`[data-reponse-id="${reponseId}"]`);
    boutons.forEach(btn => btn.disabled = true);

    fetch(`/vote/reponse/${reponseId}/${typeVote}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('📊 Réponse vote utilité:', data);
        
        if (data.success) {
            // Mettre à jour les compteurs dans l'interface
            mettreAJourCompteursVotes(reponseId, data);
            
            // Afficher un message de succès
            afficherNotification('success', data.message);
            
            // Effet visuel sur le bouton cliqué
            ajouterEffetVisuelVote(reponseId, typeVote);
            
        } else {
            afficherNotification('error', data.error || 'Erreur lors du vote');
        }
    })
    .catch(error => {
        console.error('❌ Erreur vote utilité:', error);
        afficherNotification('error', 'Erreur de connexion lors du vote');
    })
    .finally(() => {
        // Réactiver les boutons
        boutons.forEach(btn => btn.disabled = false);
    });
}

/**
 * Met à jour les compteurs de votes dans l'interface
 */
function mettreAJourCompteursVotes(reponseId, data) {
    const compteurPositif = document.getElementById(`votes-positifs-${reponseId}`);
    const compteurNegatif = document.getElementById(`votes-negatifs-${reponseId}`);
    
    if (compteurPositif) {
        compteurPositif.textContent = data.nbVotesPositifs;
        compteurPositif.classList.add('animate-pulse');
        setTimeout(() => compteurPositif.classList.remove('animate-pulse'), 1000);
    }
    
    if (compteurNegatif) {
        compteurNegatif.textContent = data.nbVotesNegatifs;
        compteurNegatif.classList.add('animate-pulse');
        setTimeout(() => compteurNegatif.classList.remove('animate-pulse'), 1000);
    }
}

/**
 * Ajoute un effet visuel au bouton voté
 */
function ajouterEffetVisuelVote(reponseId, typeVote) {
    const bouton = document.querySelector(`[data-reponse-id="${reponseId}"][data-type="${typeVote}"]`);
    if (bouton) {
        bouton.classList.add('voted');
        bouton.style.transform = 'scale(0.95)';
        setTimeout(() => {
            bouton.style.transform = '';
        }, 150);
    }
}

// ========================================================================
// SYSTÈME 2 : VOTES DE VÉRACITÉ SUR LES DEMANDES
// ========================================================================

/**
 * Initialise le système de votes de véracité dans le formulaire
 */
function initVotesVeracite() {
    // Gestion des options de vote dans le formulaire
    const optionsVote = document.querySelectorAll('.vote-option input[type="radio"]');
    
    optionsVote.forEach(function(radio) {
        radio.addEventListener('change', function() {
            // Retirer la classe selected de toutes les options
            document.querySelectorAll('.vote-option').forEach(opt => 
                opt.classList.remove('selected'));
            
            // Ajouter la classe selected à l'option choisie
            if (this.checked) {
                this.closest('.vote-option').classList.add('selected');
            }
        });
    });
    
    console.log(`🏛️ ${optionsVote.length} options de vote de véracité initialisées`);
}

// ========================================================================
// SYSTÈME 3 : GESTION DES SUPPRESSIONS
// ========================================================================

/**
 * Initialise la gestion des suppressions (réponses et demandes)
 */
function initGestionSuppressions() {
    initSuppressionReponses();
    initSuppressionDemandes();
    console.log('🗑️ Système de suppressions initialisé');
}

/**
 * Gestion de la suppression des réponses
 */
function initSuppressionReponses() {
    const modal = document.getElementById('modalSuppressionReponse');
    const apercuElement = document.getElementById('apercu-commentaire-modal');
    const confirmerBtn = document.getElementById('confirmer-suppression-btn');
    const form = document.getElementById('form-suppression-reponse');
    const tokenInput = document.getElementById('csrf-token-suppression');
    
    let reponseIdASupprimer = null;
    let csrfTokenAUtiliser = null;
    
    // Événement d'ouverture de la modal
    if (modal) {
        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // Récupérer les données du bouton
            reponseIdASupprimer = button.getAttribute('data-reponse-id');
            const apercu = button.getAttribute('data-reponse-apercu');
            csrfTokenAUtiliser = button.getAttribute('data-csrf-token');
            
            // Mettre à jour l'aperçu
            if (apercuElement) {
                apercuElement.textContent = apercu;
            }
            
            console.log('📝 Modal suppression réponse ouverte:', {reponseIdASupprimer, apercu});
        });
    }
    
    // Événement de confirmation
    if (confirmerBtn) {
        confirmerBtn.addEventListener('click', function() {
            if (!reponseIdASupprimer || !csrfTokenAUtiliser) {
                alert('Erreur : Données manquantes pour la suppression.');
                return;
            }
            
            // Désactiver le bouton pour éviter les doubles clics
            confirmerBtn.disabled = true;
            confirmerBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Suppression...';
            
            // Configurer et soumettre le formulaire
            if (form) {
                form.action = '/demande/reponse/' + reponseIdASupprimer + '/supprimer';
                if (tokenInput) {
                    tokenInput.value = csrfTokenAUtiliser;
                }
                form.submit();
            }
            
            console.log('🗑️ Suppression réponse en cours:', reponseIdASupprimer);
        });
    }
    
    // Réinitialiser la modal à la fermeture
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            reponseIdASupprimer = null;
            csrfTokenAUtiliser = null;
            if (confirmerBtn) {
                confirmerBtn.disabled = false;
                confirmerBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Supprimer définitivement';
            }
            console.log('🔄 Modal suppression réponse réinitialisée');
        });
    }
}

/**
 * Gestion de la suppression des demandes
 */
function initSuppressionDemandes() {
    const modalDemande = document.getElementById('modalSuppressionDemande');
    const titreElement = document.getElementById('titre-demande-modal');
    const nbReponsesElement = document.getElementById('nb-reponses-modal');
    const nbVotesElement = document.getElementById('nb-votes-modal');
    const confirmerBtnDemande = document.getElementById('confirmer-suppression-demande-btn');
    const formDemande = document.getElementById('form-suppression-demande');
    const tokenInputDemande = document.getElementById('csrf-token-suppression-demande');
    const checkboxConfirmation = document.getElementById('confirmeComprehension');
    
    let demandeIdASupprimer = null;
    let csrfTokenDemandeAUtiliser = null;
    
    // Événement d'ouverture de la modal
    if (modalDemande) {
        modalDemande.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // Récupérer les données du bouton
            demandeIdASupprimer = button.getAttribute('data-demande-id');
            const titreDemande = button.getAttribute('data-demande-titre');
            const nbReponses = button.getAttribute('data-nb-reponses');
            const nbVotes = button.getAttribute('data-nb-votes');
            csrfTokenDemandeAUtiliser = button.getAttribute('data-csrf-token');
            
            // Mettre à jour les informations
            if (titreElement) titreElement.textContent = titreDemande;
            if (nbReponsesElement) nbReponsesElement.textContent = nbReponses;
            if (nbVotesElement) nbVotesElement.textContent = nbVotes;
            
            // Réinitialiser la checkbox
            if (checkboxConfirmation) checkboxConfirmation.checked = false;
            if (confirmerBtnDemande) confirmerBtnDemande.disabled = true;
            
            console.log('🏢 Modal suppression demande ouverte:', {
                demandeIdASupprimer, 
                titreDemande, 
                nbReponses, 
                nbVotes
            });
        });
    }
    
    // Gestion de la checkbox de confirmation
    if (checkboxConfirmation) {
        checkboxConfirmation.addEventListener('change', function() {
            if (confirmerBtnDemande) {
                confirmerBtnDemande.disabled = !this.checked;
            }
        });
    }
    
    // Événement de confirmation
    if (confirmerBtnDemande) {
        confirmerBtnDemande.addEventListener('click', function() {
            if (!demandeIdASupprimer || !csrfTokenDemandeAUtiliser) {
                alert('Erreur : Données manquantes pour la suppression.');
                return;
            }
            
            if (!checkboxConfirmation || !checkboxConfirmation.checked) {
                alert('Veuillez confirmer que vous comprenez les conséquences de cette action.');
                return;
            }
            
            // Désactiver le bouton pour éviter les doubles clics
            confirmerBtnDemande.disabled = true;
            confirmerBtnDemande.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Suppression en cours...';
            
            // Configurer et soumettre le formulaire
            if (formDemande) {
                formDemande.action = '/demande/' + demandeIdASupprimer + '/supprimer';
                if (tokenInputDemande) {
                    tokenInputDemande.value = csrfTokenDemandeAUtiliser;
                }
                formDemande.submit();
            }
            
            console.log('🗑️ Suppression demande en cours:', demandeIdASupprimer);
        });
    }
    
    // Réinitialiser la modal à la fermeture
    if (modalDemande) {
        modalDemande.addEventListener('hidden.bs.modal', function() {
            demandeIdASupprimer = null;
            csrfTokenDemandeAUtiliser = null;
            if (checkboxConfirmation) checkboxConfirmation.checked = false;
            if (confirmerBtnDemande) {
                confirmerBtnDemande.disabled = true;
                confirmerBtnDemande.innerHTML = '<i class="fas fa-trash me-1"></i>Supprimer définitivement';
            }
            console.log('🔄 Modal suppression demande réinitialisée');
        });
    }
}

// ========================================================================
// GESTION DES FORMULAIRES
// ========================================================================

/**
 * Initialise la validation et les interactions des formulaires
 */
function initFormulaires() {
    const formContribution = document.getElementById('form-contribution');
    
    if (formContribution) {
        formContribution.addEventListener('submit', function(e) {
            // Validation côté client
            if (!validerFormulaireContribution(this)) {
                e.preventDefault();
                return false;
            }
            
            // Afficher un indicateur de chargement
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const textOriginal = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Envoi en cours...';
                submitBtn.disabled = true;
                
                // Réactiver le bouton après un délai (au cas où)
                setTimeout(() => {
                    submitBtn.innerHTML = textOriginal;
                    submitBtn.disabled = false;
                }, 10000);
            }
        });
        
        console.log('📝 Formulaire de contribution initialisé');
    }
}

/**
 * Valide le formulaire de contribution côté client
 */
function validerFormulaireContribution(form) {
    console.log('🔍 Validation du formulaire');
    
    // Chercher le champ contenu avec les bons sélecteurs
    let contenu = form.querySelector('textarea[name*="contenu"]') ||
                  form.querySelector('textarea') ||
                  form.querySelector('[id*="contenu"]');
    
    console.log('🔍 Champ contenu trouvé:', contenu);
    
    // Vérifier le contenu
    if (!contenu || !contenu.value.trim()) {
        afficherNotification('error', 'Le contenu de votre contribution est obligatoire.');
        contenu?.focus();
        return false;
    }
    
    // Vérifier le vote de véracité
    const voteVeracite = form.querySelector('input[name="type_veracite"]:checked');
    if (!voteVeracite) {
        afficherNotification('error', 'Vous devez évaluer la véracité de cette information.');
        
        // Scroll vers la section de vote
        const sectionVote = form.querySelector('.vote-option');
        if (sectionVote) {
            sectionVote.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return false;
    }
    
    console.log('✅ Validation réussie');
    return true;
}

// ========================================================================
// SYSTÈME DE NOTIFICATIONS
// ========================================================================

/**
 * Affiche une notification toast
 */
function afficherNotification(type, message, duree = 4000) {
    console.log(`📢 Notification ${type}: ${message}`);
    
    // 🚨 NE PAS AFFICHER LES MESSAGES DE VERDICT DANS LES NOTIFICATIONS
    if (message.includes('Verdict de la communauté') || message.includes('Information trompeuse')) {
        console.log('🔇 Message de verdict ignoré dans les notifications');
        return;
    }
    
    // Supprimer les anciennes notifications
    const anciennesNotifs = document.querySelectorAll('.toast-notification');
    anciennesNotifs.forEach(notif => notif.remove());
    
    // Créer la nouvelle notification
    const notification = document.createElement('div');
    notification.className = `toast-notification alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed shadow-lg`;
    notification.style.cssText = `
        top: 20px; 
        right: 20px; 
        z-index: 9999; 
        min-width: 300px; 
        max-width: 500px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    `;
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ms-2" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Suppression automatique
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }
    }, duree);
}

// ========================================================================
// FONCTIONS UTILITAIRES
// ========================================================================

/**
 * Copie l'URL dans le presse-papier pour le partage
 */
function partagerDemande() {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href)
            .then(() => afficherNotification('success', 'Lien copié dans le presse-papier !'))
            .catch(() => afficherNotification('error', 'Impossible de copier le lien'));
    } else {
        // Fallback pour les navigateurs plus anciens
        const textarea = document.createElement('textarea');
        textarea.value = window.location.href;
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            afficherNotification('success', 'Lien copié dans le presse-papier !');
        } catch (err) {
            afficherNotification('error', 'Impossible de copier le lien');
        }
        
        document.body.removeChild(textarea);
    }
}

/**
 * Initialise les tooltips Bootstrap
 */
function initTooltips() {
    // Vérifier si Bootstrap est disponible
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        console.log(`💡 ${tooltipTriggerList.length} tooltips initialisés`);
    }
}

/**
 * Initialise les actions de scroll
 */
function initScrollActions() {
    // Scroll vers le formulaire
    const liensFormulaire = document.querySelectorAll('.scroll-to-form, a[href="#form-contribution"]');
    
    liensFormulaire.forEach(function(lien) {
        lien.addEventListener('click', function(e) {
            e.preventDefault();
            
            const formulaire = document.getElementById('form-contribution');
            if (formulaire) {
                formulaire.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Focus sur le premier champ
                setTimeout(() => {
                    const premierChamp = formulaire.querySelector('textarea, input');
                    if (premierChamp) {
                        premierChamp.focus();
                    }
                }, 500);
            }
        });
    });
    
    console.log(`🔗 ${liensFormulaire.length} liens de scroll initialisés`);
}

// ========================================================================
// FONCTIONS DE DEBUG (à supprimer en production)
// ========================================================================

/**
 * Debug du formulaire (fonction temporaire)
 */
function debugFormulaire() {
    const form = document.getElementById('form-contribution');
    if (!form) {
        console.error('❌ Formulaire non trouvé !');
        return;
    }
    
    console.log('🔍 DEBUG FORMULAIRE :');
    
    // 1. Vérifier le contenu
    let contenu = form.querySelector('textarea[name*="contenu"]') ||
                  form.querySelector('textarea') ||
                  form.querySelector('[id*="contenu"]');
    console.log('📝 Champ contenu trouvé:', contenu);
    console.log('📝 Valeur contenu:', contenu?.value);
    
    // 2. Vérifier le vote de véracité
    const voteVeracite = form.querySelector('input[name="type_veracite"]:checked');
    console.log('🗳️ Vote véracité sélectionné:', voteVeracite);
    console.log('🗳️ Valeur vote:', voteVeracite?.value);
    
    // 3. Lister tous les champs du formulaire
    const allInputs = form.querySelectorAll('input, textarea, select');
    console.log('📋 Tous les champs du formulaire:');
    allInputs.forEach(input => {
        console.log(`  - ${input.name || input.id}: ${input.value}`);
    });
    
    // 4. Vérifier les erreurs potentielles
    const errors = form.querySelectorAll('.invalid-feedback, .form-error');
    if (errors.length > 0) {
        console.log('❌ Erreurs trouvées:', errors);
    }
    
    return {contenu, voteVeracite, allInputs, errors};
}

// ========================================================================
// FONCTIONS GLOBALES (accessibles depuis le HTML)
// ========================================================================

// Exposer les fonctions principales pour usage depuis le HTML
window.partagerDemande = partagerDemande;
window.afficherNotification = afficherNotification;
window.debugFormulaire = debugFormulaire; // À supprimer en production

console.log('🎯 Système de fact-checking complet prêt !');

// Ajouter un bouton de debug temporaire (à supprimer en production)
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-contribution');
    if (form && window.location.hostname === 'localhost') { // Seulement en développement
        const debugBtn = document.createElement('button');
        debugBtn.type = 'button';
        debugBtn.className = 'btn btn-warning btn-sm mt-2';
        debugBtn.innerHTML = '🐛 Debug Form';
        debugBtn.onclick = debugFormulaire;
        form.appendChild(debugBtn);
    }
});