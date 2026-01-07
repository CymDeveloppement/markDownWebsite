<?php
/**
 * Point d'entrée de l'application
 */

// Mesure du temps de chargement
$startTime = microtime(true);

// Définir le répertoire de base de l'application
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Charger Composer autoloader
require BASE_PATH . '/vendor/autoload.php';

// Charger les variables d'environnement
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

use App\MarkdownParser;
use App\MenuGenerator;

// Parser les langues disponibles
$languages = [];
$languagesStr = $_ENV['LANGUAGES'] ?? 'fr:Français';
$defaultLanguage = $_ENV['DEFAULT_LANGUAGE'] ?? 'fr';

if (!empty($languagesStr)) {
    $langParts = explode(',', $languagesStr);
    foreach ($langParts as $langPart) {
        $parts = explode(':', trim($langPart));
        if (count($parts) === 2) {
            $code = trim($parts[0]);
            $languages[$code] = trim($parts[1]);
        }
    }
}

// Vérifier si une langue est sauvegardée dans un cookie
if (isset($_COOKIE['selectedLanguage'])) {
    $cookieLanguage = $_COOKIE['selectedLanguage'];
    // Vérifier que la langue du cookie est valide (présente dans les langues disponibles)
    if (isset($languages[$cookieLanguage])) {
        $defaultLanguage = $cookieLanguage;
    }
}

// Vérifier si une langue est définie dans les codes de langues disponibles
$availableCodes = array_keys($languages);
if (!in_array($defaultLanguage, $availableCodes) && !empty($availableCodes)) {
    $defaultLanguage = $availableCodes[0];
}

// Détecter la structure multilingue
$pagesPath = BASE_PATH . '/pages';
$currentLanguage = $defaultLanguage;
$useLanguageFolders = false;

// Vérifier si des dossiers de langues existent
if (!empty($languages) && is_dir($pagesPath)) {
    $dirs = scandir($pagesPath);
    $languageDirs = [];
    foreach ($dirs as $dir) {
        if ($dir !== '.' && $dir !== '..' && is_dir($pagesPath . '/' . $dir)) {
            // Vérifier si c'est un dossier de langue (correspond à un code de langue)
            if (isset($languages[$dir])) {
                $languageDirs[] = $dir;
            }
        }
    }
    
    // Si au moins un dossier de langue existe, utiliser la structure multilingue
    if (!empty($languageDirs)) {
        $useLanguageFolders = true;
        // Vérifier que la langue courante a un dossier
        if (!in_array($currentLanguage, $languageDirs) && !empty($languageDirs)) {
            // Utiliser le premier dossier de langue disponible
            $currentLanguage = $languageDirs[0];
        }
        $pagesPath = $pagesPath . '/' . $currentLanguage;
    }
}

// Déterminer la page d'accueil : première page par ordre alphabétique
$homePage = 'accueil'; // Par défaut
if (is_dir($pagesPath)) {
    $files = [];
    $items = scandir($pagesPath);
    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..' && is_file($pagesPath . '/' . $item) && substr($item, -3) === '.md') {
            $files[] = basename($item, '.md');
        }
    }
    if (!empty($files)) {
        sort($files, SORT_STRING | SORT_FLAG_CASE);
        $homePage = $files[0];
    }
}

// Récupérer la page demandée (par défaut: première page par ordre alphabétique)
$page = $_GET['page'] ?? $homePage;

// Générer le menu
$menuGenerator = new MenuGenerator($pagesPath);
$menu = $menuGenerator->render($page);

// Créer une instance du parser Markdown
$parser = new MarkdownParser($pagesPath);

// Parser le fichier Markdown
$content = $parser->parseFile($page);

// Titre de la page (par défaut)
$pageTitle = 'Application PHP';
if ($page === 'accueil') {
    $pageTitle = 'Accueil - Application PHP';
}

// Calculer le temps de chargement
$loadTime = microtime(true) - $startTime;

// Les variables $languages et $defaultLanguage sont déjà disponibles pour le template
// (elles sont dans la portée globale)
$defaultLanguage = $currentLanguage; // Pour le template

// Inclure le template
require BASE_PATH . '/src/view/layout.php';
