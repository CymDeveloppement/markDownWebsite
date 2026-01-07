<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Application PHP'; ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="/" class="logo">
                <?php 
                // Vérifier si logo.png existe à la racine du projet
                $logoPath = BASE_PATH . '/logo.png';
                if (file_exists($logoPath)): ?>
                    <img src="/logo.png" alt="Logo" class="logo-image">
                <?php else: ?>
                    <div class="logo-icon">🚀</div>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($_ENV['APP_NAME'] ?? 'Mon Application'); ?></span>
            </a>
            
            <nav class="nav">
                <?php echo isset($menu) ? $menu : ''; ?>
                
                <button class="search-button" id="search-button" aria-label="Rechercher">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
                
                <?php if (isset($languages) && !empty($languages) && count($languages) > 1): ?>
                <div class="language-selector">
                    <select id="language-select" class="language-select">
                        <?php 
                        // Fonction pour obtenir l'emoji du drapeau
                        $getFlag = function($code) {
                            $flags = [
                                'fr' => '🇫🇷', 'en' => '🇬🇧', 'es' => '🇪🇸', 'de' => '🇩🇪',
                                'it' => '🇮🇹', 'pt' => '🇵🇹', 'nl' => '🇳🇱', 'ru' => '🇷🇺',
                                'zh' => '🇨🇳', 'ja' => '🇯🇵', 'ko' => '🇰🇷', 'ar' => '🇸🇦',
                                'pl' => '🇵🇱', 'sv' => '🇸🇪', 'da' => '🇩🇰', 'fi' => '🇫🇮',
                                'no' => '🇳🇴', 'cs' => '🇨🇿', 'tr' => '🇹🇷', 'el' => '🇬🇷',
                                'he' => '🇮🇱', 'hi' => '🇮🇳', 'th' => '🇹🇭', 'vi' => '🇻🇳',
                                'id' => '🇮🇩', 'uk' => '🇺🇦',
                            ];
                            return $flags[strtolower($code)] ?? '🌐';
                        };
                        
                        foreach ($languages as $code => $name): 
                            $flag = $getFlag($code);
                        ?>
                            <option value="<?php echo htmlspecialchars($code); ?>" <?php echo (isset($defaultLanguage) && $code === $defaultLanguage) ? 'selected' : ''; ?>>
                                <?php echo $flag . ' ' . htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="content-area">
            <?php if (isset($content)): ?>
                <?php echo $content; ?>
            <?php else: ?>
                <div class="content-placeholder">
                    <h2>Espace de contenu</h2>
                    <p>Cette zone est réservée au contenu HTML de votre application.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <small>
            &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($_ENV['APP_NAME'] ?? 'Mon Application'); ?>. Tous droits réservés.
            <?php if (isset($loadTime)): ?>
                | Temps de chargement : <?php echo number_format($loadTime * 1000, 2); ?> ms
            <?php endif; ?>
        </small>
    </footer>

    <!-- Modal de recherche -->
    <div id="search-modal" class="search-modal">
        <div class="search-modal-content">
            <div class="search-modal-header">
                <h2>Rechercher</h2>
                <button class="search-modal-close" id="search-modal-close" aria-label="Fermer">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="search-modal-body">
                <input type="text" id="search-input" class="search-input" placeholder="Tapez votre recherche..." autofocus>
                <div id="search-results" class="search-results"></div>
            </div>
        </div>
    </div>

    <script>
        // Fonctions utilitaires pour gérer les cookies
        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + date.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/";
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        // Gestion du sélecteur de langue
        (function() {
            const languageSelect = document.getElementById('language-select');
            if (!languageSelect) return;

            const COOKIE_NAME = 'selectedLanguage';
            const COOKIE_EXPIRY_DAYS = 365;

            // Récupérer la langue sauvegardée depuis le cookie
            const savedLanguage = getCookie(COOKIE_NAME);
            if (savedLanguage) {
                languageSelect.value = savedLanguage;
            }

            // Sauvegarder la langue dans un cookie lors du changement et rediriger vers l'accueil
            languageSelect.addEventListener('change', function() {
                const selectedLanguage = this.value;
                setCookie(COOKIE_NAME, selectedLanguage, COOKIE_EXPIRY_DAYS);
                // Rediriger vers la page d'accueil (qui sera dans la nouvelle langue grâce au cookie)
                window.location.href = '/';
            });
        })();

        // Gestion de la modal de recherche
        (function() {
            const searchButton = document.getElementById('search-button');
            const searchModal = document.getElementById('search-modal');
            const searchModalClose = document.getElementById('search-modal-close');
            const searchInput = document.getElementById('search-input');
            const searchResults = document.getElementById('search-results');

            if (!searchButton || !searchModal) return;

            // Ouvrir la modal
            searchButton.addEventListener('click', function() {
                searchModal.classList.add('active');
                searchInput.focus();
            });

            // Fermer la modal
            function closeModal() {
                searchModal.classList.remove('active');
                searchInput.value = '';
                searchResults.innerHTML = '';
            }

            searchModalClose.addEventListener('click', closeModal);

            // Fermer en cliquant sur le fond
            searchModal.addEventListener('click', function(e) {
                if (e.target === searchModal) {
                    closeModal();
                }
            });

            // Fermer avec la touche Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && searchModal.classList.contains('active')) {
                    closeModal();
                }
            });

            // Fonction de recherche
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                // Annuler la recherche précédente
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    searchResults.innerHTML = '';
                    return;
                }

                // Délai pour éviter trop de requêtes
                searchTimeout = setTimeout(function() {
                    performSearch(query);
                }, 300);
            });

            function performSearch(query) {
                searchResults.innerHTML = '<p class="search-placeholder">Recherche en cours...</p>';
                
                fetch('/search.php?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        displayResults(data.results || []);
                    })
                    .catch(error => {
                        console.error('Erreur de recherche:', error);
                        searchResults.innerHTML = '<p class="search-placeholder">Erreur lors de la recherche</p>';
                    });
            }

            function displayResults(results) {
                if (results.length === 0) {
                    searchResults.innerHTML = '<p class="search-placeholder">Aucun résultat trouvé</p>';
                    return;
                }

                let html = '<div class="search-results-list">';
                results.forEach(function(result) {
                    html += '<div class="search-result-item">';
                    html += '<a href="?page=' + encodeURIComponent(result.path) + '" class="search-result-link">';
                    html += '<h3 class="search-result-title">' + escapeHtml(result.title) + '</h3>';
                    html += '<p class="search-result-excerpt">' + result.excerpt + '</p>';
                    html += '</a>';
                    html += '</div>';
                });
                html += '</div>';
                
                searchResults.innerHTML = html;
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        })();
    </script>
</body>
</html>
