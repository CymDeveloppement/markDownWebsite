<?php
/**
 * Endpoint de recherche
 */

// Définir le répertoire de base de l'application
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Charger Composer autoloader
require BASE_PATH . '/vendor/autoload.php';

// Charger les variables d'environnement
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

use App\Search;

// Définir le header JSON
header('Content-Type: application/json; charset=utf-8');

// Récupérer le terme de recherche
$query = $_GET['q'] ?? '';

if (empty($query)) {
    echo json_encode(['results' => []]);
    exit;
}

// Parser les langues disponibles pour déterminer le chemin des pages
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
            if (isset($languages[$dir])) {
                $languageDirs[] = $dir;
            }
        }
    }
    
    if (!empty($languageDirs)) {
        $useLanguageFolders = true;
        if (!in_array($currentLanguage, $languageDirs) && !empty($languageDirs)) {
            $currentLanguage = $languageDirs[0];
        }
        $pagesPath = $pagesPath . '/' . $currentLanguage;
    }
}

// Créer une instance de recherche avec le chemin de la langue sélectionnée
// La recherche ne s'effectuera que dans les fichiers de cette langue
$search = new Search($pagesPath);

// Effectuer la recherche
$results = $search->search($query);

// Retourner les résultats en JSON
echo json_encode(['results' => $results], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
