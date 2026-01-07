<?php
/**
 * Endpoint de mise à jour Git
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

use App\GitUpdate;

// Définir le header JSON
header('Content-Type: application/json; charset=utf-8');

// Récupérer les paramètres GET
$scope = $_GET['scope'] ?? 'pages'; // 'pages' ou 'full'
$repository = $_GET['repository'] ?? null; // URL optionnelle du dépôt Git

// Valider le scope
if (!in_array($scope, ['pages', 'full'])) {
    echo json_encode([
        'success' => false,
        'errors' => ["Paramètre 'scope' invalide. Utilisez 'pages' ou 'full'."],
        'messages' => []
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Créer une instance de GitUpdate
$gitUpdate = new GitUpdate(BASE_PATH);

// Effectuer la mise à jour
$result = $gitUpdate->update($scope, $repository);

// Retourner le résultat en JSON
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
