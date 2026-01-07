<?php

namespace App;

class Search
{
    private $pagesPath;
    private $basePath;

    public function __construct($pagesPath = null)
    {
        $this->pagesPath = $pagesPath ?: BASE_PATH . '/pages';
        $this->basePath = BASE_PATH;
    }

    /**
     * Recherche un terme dans tous les fichiers Markdown
     *
     * @param string $query Terme de recherche
     * @return array Résultats de la recherche
     */
    public function search($query)
    {
        if (empty($query) || strlen(trim($query)) < 2) {
            return [];
        }

        $query = trim($query);
        $results = [];
        $this->scanDirectory($this->pagesPath, $query, $results, '');

        // Trier les résultats par pertinence (nombre d'occurrences)
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $results;
    }

    /**
     * Scan récursif d'un répertoire pour rechercher dans les fichiers Markdown
     *
     * @param string $dir Répertoire à scanner
     * @param string $query Terme de recherche
     * @param array &$results Tableau de résultats (passé par référence)
     * @param string $relativePath Chemin relatif depuis le dossier pages
     */
    private function scanDirectory($dir, $query, &$results, $relativePath = '')
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        $queryLower = mb_strtolower($query);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $dir . '/' . $item;
            $currentRelativePath = $relativePath ? $relativePath . '/' . $item : $item;

            if (is_dir($fullPath)) {
                // Récurrence dans les sous-dossiers
                $this->scanDirectory($fullPath, $query, $results, $currentRelativePath);
            } elseif (is_file($fullPath) && substr($item, -3) === '.md') {
                // C'est un fichier Markdown, rechercher dedans
                $this->searchInFile($fullPath, $query, $queryLower, $results, $currentRelativePath);
            }
        }
    }

    /**
     * Recherche dans un fichier Markdown
     *
     * @param string $filePath Chemin complet du fichier
     * @param string $query Terme de recherche (original)
     * @param string $queryLower Terme de recherche (minuscule)
     * @param array &$results Tableau de résultats (passé par référence)
     * @param string $relativePath Chemin relatif du fichier
     */
    private function searchInFile($filePath, $query, $queryLower, &$results, $relativePath)
    {
        if (!file_exists($filePath)) {
            return;
        }

        $content = file_get_contents($filePath);
        $contentLower = mb_strtolower($content);

        // Compter les occurrences
        $count = substr_count($contentLower, $queryLower);
        if ($count === 0) {
            return;
        }

        // Extraire le titre
        $title = $this->extractTitle($content);
        if (empty($title)) {
            $fileName = basename($relativePath, '.md');
            $title = $this->formatLabel($fileName);
        }

        // Extraire un extrait (contexte autour de la première occurrence)
        $excerpt = $this->extractExcerpt($content, $queryLower, 200);

        // Créer le chemin de la page (sans extension .md)
        $pagePath = str_replace('.md', '', $relativePath);

        $results[] = [
            'title' => $title,
            'path' => $pagePath,
            'excerpt' => $excerpt,
            'score' => $count,
            'matches' => $count
        ];
    }

    /**
     * Extrait le titre depuis le contenu Markdown
     *
     * @param string $content Contenu Markdown
     * @return string Titre extrait
     */
    private function extractTitle($content)
    {
        $lines = explode("\n", $content, 10);
        
        foreach ($lines as $line) {
            $line = trim($line);
            // Chercher un titre Markdown (# Titre)
            if (preg_match('/^#+\s+(.+)$/', $line, $matches)) {
                $title = trim($matches[1]);
                // Enlever le séparateur | si présent (prendre la partie droite)
                if (strpos($title, '|') !== false) {
                    $parts = explode('|', $title, 2);
                    $title = trim($parts[1]);
                }
                // Remplacer les underscores par des espaces
                $title = str_replace('_', ' ', $title);
                return $title;
            }
        }

        return '';
    }

    /**
     * Extrait un extrait du contenu autour d'une occurrence
     *
     * @param string $content Contenu complet
     * @param string $query Terme de recherche (en minuscules)
     * @param int $length Longueur souhaitée de l'extrait
     * @return string Extrait formaté
     */
    private function extractExcerpt($content, $query, $length = 200)
    {
        $contentLower = mb_strtolower($content);
        $pos = mb_strpos($contentLower, $query);

        if ($pos === false) {
            // Prendre le début du contenu
            $excerpt = mb_substr($content, 0, $length);
        } else {
            // Prendre un contexte autour de l'occurrence
            $start = max(0, $pos - ($length / 2));
            $excerpt = mb_substr($content, $start, $length);
        }

        // Nettoyer l'extrait (enlever les sauts de ligne multiples, markdown basique)
        $excerpt = preg_replace('/\n{3,}/', "\n\n", $excerpt);
        $excerpt = preg_replace('/#{1,6}\s+/', '', $excerpt); // Enlever les titres markdown
        $excerpt = trim($excerpt);

        // Tronquer si nécessaire et ajouter des ellipses
        if (mb_strlen($excerpt) > $length) {
            $excerpt = mb_substr($excerpt, 0, $length);
            $lastSpace = mb_strrpos($excerpt, ' ');
            if ($lastSpace !== false) {
                $excerpt = mb_substr($excerpt, 0, $lastSpace);
            }
            $excerpt .= '...';
        }

        // Mettre en évidence le terme recherché
        $excerpt = $this->highlightQuery($excerpt, $query);

        return $excerpt;
    }

    /**
     * Met en évidence le terme recherché dans l'extrait
     *
     * @param string $text Texte
     * @param string $query Terme de recherche (en minuscules)
     * @return string Texte avec mise en évidence
     */
    private function highlightQuery($text, $query)
    {
        // Rechercher le terme (insensible à la casse)
        $textLower = mb_strtolower($text);
        $pos = mb_strpos($textLower, $query);

        if ($pos === false) {
            return htmlspecialchars($text);
        }

        // Extraire la partie correspondante (avec la casse originale)
        $matched = mb_substr($text, $pos, mb_strlen($query));
        $before = mb_substr($text, 0, $pos);
        $after = mb_substr($text, $pos + mb_strlen($query));

        return htmlspecialchars($before) . 
               '<mark>' . htmlspecialchars($matched) . '</mark>' . 
               htmlspecialchars($after);
    }

    /**
     * Formate un nom de fichier en label lisible
     *
     * @param string $name Nom à formater
     * @return string Label formaté
     */
    private function formatLabel($name)
    {
        // Enlever le séparateur | si présent (prendre la partie droite)
        if (strpos($name, '|') !== false) {
            $parts = explode('|', $name, 2);
            $name = trim($parts[1]);
        }
        
        // Remplacer les tirets et underscores par des espaces
        $name = str_replace(['-', '_'], ' ', $name);
        // Mettre en majuscule la première lettre de chaque mot
        return ucwords($name);
    }
}
