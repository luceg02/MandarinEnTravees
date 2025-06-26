// État de connexion basé sur Twig (sera initialisé dans le template)
// Cette variable sera définie dans le template HTML via Twig

// Gestion du menu mobile
const burgerMenu = document.getElementById('burgerMenu');
const mobileMenu = document.getElementById('mobileMenu');
const mobileOverlay = document.getElementById('mobileOverlay');

function toggleMobileMenu() {
    burgerMenu.classList.toggle('active');
    mobileMenu.classList.toggle('active');
    mobileOverlay.classList.toggle('active');
    
    // Empêcher le scroll du body quand le menu est ouvert
    if (mobileMenu.classList.contains('active')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

function closeMobileMenu() {
    burgerMenu.classList.remove('active');
    mobileMenu.classList.remove('active');
    mobileOverlay.classList.remove('active');
    document.body.style.overflow = '';
}

// Event listeners pour le menu mobile
if (burgerMenu) {
    burgerMenu.addEventListener('click', toggleMobileMenu);
}

if (mobileOverlay) {
    mobileOverlay.addEventListener('click', closeMobileMenu);
}

// Fermer le menu mobile lors du clic sur un lien
const mobileNavItems = document.querySelectorAll('.mobile-nav-item');
mobileNavItems.forEach(item => {
    item.addEventListener('click', () => {
        setTimeout(closeMobileMenu, 100);
    });
});

// Fermer le menu mobile sur redimensionnement
window.addEventListener('resize', () => {
    if (window.innerWidth >= 992) {
        closeMobileMenu();
    }
});

// Initialisation de l'affichage selon l'état
function initUserInterface() {
    const notLoggedIn = document.getElementById('notLoggedIn');
    const loggedInUser = document.getElementById('loggedInUser');
    const loggedInAdmin = document.getElementById('loggedInAdmin');
    
    // Masquer tous les éléments
    if (notLoggedIn) notLoggedIn.style.display = 'none';
    if (loggedInUser) loggedInUser.style.display = 'none';
    if (loggedInAdmin) loggedInAdmin.style.display = 'none';
    
    // Afficher selon l'état (userState est défini dans le template)
    if (typeof userState !== 'undefined') {
        if (userState === 'notLoggedIn' && notLoggedIn) {
            notLoggedIn.style.display = 'block';
        } else if (userState === 'user' && loggedInUser) {
            loggedInUser.style.display = 'block';
        } else if (userState === 'admin' && loggedInAdmin) {
            loggedInAdmin.style.display = 'block';
        }
    }
}

// Toggle dropdown menu
function toggleUserDropdown(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const dropdown = (typeof userState !== 'undefined' && userState === 'admin') ? 
        document.getElementById('adminDropdownMenu') : 
        document.getElementById('userDropdownMenu');
    
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Fermer le dropdown si on clique ailleurs
document.addEventListener('click', function(event) {
    const dropdownMenus = document.querySelectorAll('.user-dropdown-menu');
    dropdownMenus.forEach(menu => {
        if (!menu.closest('.user-dropdown').contains(event.target)) {
            menu.classList.remove('show');
        }
    });
});

// Gestion des touches clavier
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeMobileMenu();
        // Fermer aussi les dropdowns
        const dropdownMenus = document.querySelectorAll('.user-dropdown-menu');
        dropdownMenus.forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', function() {
    initUserInterface();
});

// Fonction globale pour le toggle (utilisée dans le template)
window.toggleUserDropdown = toggleUserDropdown;