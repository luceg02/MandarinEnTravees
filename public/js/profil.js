document.addEventListener('DOMContentLoaded', function() {
    
    // Animation des éléments au scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    // Observer tous les éléments du feed
    const feedItems = document.querySelectorAll('.feed-item');
    feedItems.forEach(item => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(item);
    });


    // Animation des métriques au chargement
    const metrics = document.querySelectorAll('.metric-number, .stat-number');
    metrics.forEach(metric => {
        const finalValue = parseInt(metric.textContent);
        let currentValue = 0;
        const increment = Math.ceil(finalValue / 30);
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                currentValue = finalValue;
                clearInterval(timer);
            }
            
            if (metric.classList.contains('stat-number')) {
                metric.textContent = currentValue + '%';
            } else {
                metric.textContent = currentValue;
            }
        }, 50);
    });

    // Gestion du scroll pour le sidebar sticky sur mobile
    function handleSidebarSticky() {
        const sidebar = document.querySelector('.profil-sidebar');
        const container = document.querySelector('.profil-container');
        
        if (window.innerWidth <= 768) {
            sidebar.style.position = 'static';
        } else {
            sidebar.style.position = 'sticky';
        }
    }

    // Appeler au chargement et au redimensionnement
    handleSidebarSticky();
    window.addEventListener('resize', handleSidebarSticky);

    // Gestion des onglets avec animation
    const onglets = document.querySelectorAll('.onglet');
    onglets.forEach(onglet => {
        onglet.addEventListener('click', function(e) {
            // Animation de transition
            const feed = document.querySelector('.profil-feed');
            feed.style.opacity = '0.5';
            feed.style.transform = 'translateY(10px)';
            
            setTimeout(() => {
                feed.style.opacity = '1';
                feed.style.transform = 'translateY(0)';
            }, 300);
        });
    });

    // Tooltip pour les badges
    const badges = document.querySelectorAll('.item-badge');
    badges.forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            let tooltipText = '';
            if (this.classList.contains('badge-vrai')) {
                tooltipText = 'Information vérifiée et confirmée';
            } else if (this.classList.contains('badge-faux')) {
                tooltipText = 'Information vérifiée et infirmée';
            } else if (this.classList.contains('badge-attente')) {
                tooltipText = 'En attente de vérification';
            } else if (this.classList.contains('badge-verifie')) {
                tooltipText = 'Réponse vérifiée par un expert';
            }
            
            if (tooltipText) {
                this.setAttribute('title', tooltipText);
            }
        });
    });

    // Animation de hover pour les feed items
    feedItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.borderColor = '#1e5a8a';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.borderColor = '#e5e5e5';
        });
    });

    // Gestion du lazy loading pour le contenu
    let page = 1;
    const loadMoreTrigger = document.createElement('div');
    loadMoreTrigger.style.height = '20px';
    loadMoreTrigger.style.margin = '2rem 0';
    
    const feed = document.querySelector('.profil-feed');
    if (feed && feedItems.length >= 10) {
        feed.appendChild(loadMoreTrigger);
        
        const loadMoreObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadMoreContent();
                }
            });
        });
        
        loadMoreObserver.observe(loadMoreTrigger);
    }

    function loadMoreContent() {

        console.log('Chargement de plus de contenu...');
        
        // Simulation d'un chargement
        loadMoreTrigger.innerHTML = '<div style="text-align: center; color: #666;">Chargement...</div>';
        
        setTimeout(() => {
            // Simuler l'ajout de nouveau contenu
            page++;
            loadMoreTrigger.innerHTML = '';
            
            // Si plus de contenu disponible, cacher le trigger
            if (page > 3) {
                loadMoreTrigger.style.display = 'none';
            }
        }, 1000);
    }

    // Gestion des interactions avec les éléments
    document.addEventListener('click', function(e) {
        // Si clic sur un item du feed, naviguer vers le détail
        const feedItem = e.target.closest('.feed-item');
        if (feedItem && !e.target.closest('.item-footer')) {
            // Ici vous pouvez ajouter la navigation vers la page de détail
            console.log('Navigation vers le détail de l\'item');
            
            // Animation de feedback
            feedItem.style.transform = 'scale(0.98)';
            setTimeout(() => {
                feedItem.style.transform = 'scale(1)';
            }, 150);
        }
    });

    // Animation d'apparition progressive pour les statistiques
    const statItems = document.querySelectorAll('.stat-item, .metric');
    statItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 200 + (index * 100));
    });

    // Gestion des erreurs d'images d'avatar
    const avatars = document.querySelectorAll('.avatar-circle img');
    avatars.forEach(avatar => {
        avatar.addEventListener('error', function() {
            this.style.display = 'none';
            this.parentElement.innerHTML = '<i class="fas fa-user"></i>';
        });
    });

    // Fonction utilitaire pour formater les nombres
    function formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'k';
        }
        return num.toString();
    }

    // Appliquer le formatage aux grands nombres
    const numberElements = document.querySelectorAll('.metric-number');
    numberElements.forEach(element => {
        const originalNumber = parseInt(element.textContent);
        if (originalNumber >= 1000) {
            element.setAttribute('data-original', originalNumber);
            element.textContent = formatNumber(originalNumber);
        }
    });
});