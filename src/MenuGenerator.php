<?php

namespace App;

class MenuGenerator
{
    private $pagesPath;
    private $menuItems = [];

    public function __construct($pagesPath = null)
    {
        $this->pagesPath = $pagesPath ?: BASE_PATH . '/pages';
    }

    /**
     * Génère la structure du menu à partir des fichiers Markdown
     *
     * @return array Structure du menu
     */
    public function generate()
    {
        $this->menuItems = [];
        $this->scanDirectory($this->pagesPath);
        
        // Trier les éléments par ordre alphabétique (utiliser sortKey si disponible)
        uasort($this->menuItems, function($a, $b) {
            $keyA = isset($a['sortKey']) ? $a['sortKey'] : $a['label'];
            $keyB = isset($b['sortKey']) ? $b['sortKey'] : $b['label'];
            return strcasecmp($keyA, $keyB);
        });

        // Trier aussi les enfants de chaque dossier
        foreach ($this->menuItems as &$item) {
            if ($item['type'] === 'folder' && !empty($item['children'])) {
                usort($item['children'], function($a, $b) {
                    $keyA = isset($a['sortKey']) ? $a['sortKey'] : $a['label'];
                    $keyB = isset($b['sortKey']) ? $b['sortKey'] : $b['label'];
                    return strcasecmp($keyA, $keyB);
                });
            }
        }

        return $this->menuItems;
    }

    /**
     * Scan un répertoire de manière récursive
     *
     * @param string $dir Chemin du répertoire
     * @param string $prefix Préfixe pour le chemin (pour les sous-dossiers)
     */
    private function scanDirectory($dir, $prefix = '')
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $dir . '/' . $item;
            $relativePath = $prefix ? $prefix . '/' . $item : $item;

            if (is_dir($fullPath)) {
                // C'est un dossier, créer un sous-menu
                $folderName = $this->formatLabel($item);
                $sortKey = $this->extractSortKey($item);
                $this->menuItems[$relativePath] = [
                    'type' => 'folder',
                    'label' => $folderName,
                    'sortKey' => $sortKey,
                    'path' => $relativePath,
                    'children' => []
                ];

                // Scanner les fichiers dans ce dossier
                $subItems = scandir($fullPath);
                foreach ($subItems as $subItem) {
                    if ($subItem === '.' || $subItem === '..') {
                        continue;
                    }
                    
                    $subPath = $fullPath . '/' . $subItem;
                    if (is_file($subPath) && substr($subItem, -3) === '.md') {
                        $fileName = basename($subItem, '.md');
                        $title = $this->extractTitle($subPath);
                        $sortKey = $this->extractSortKey($fileName);
                        // Si le titre contient aussi un |, extraire la partie droite
                        if (strpos($title, '|') !== false) {
                            $titleParts = explode('|', $title, 2);
                            $title = trim($titleParts[1]);
                            $sortKey = trim($titleParts[0]);
                        }
                        $this->menuItems[$relativePath]['children'][] = [
                            'type' => 'file',
                            'label' => $title,
                            'sortKey' => $sortKey,
                            'path' => $relativePath . '/' . $fileName,
                            'file' => $relativePath . '/' . $subItem
                        ];
                    }
                }
            } elseif (is_file($fullPath) && substr($item, -3) === '.md') {
                // C'est un fichier Markdown à la racine
                $fileName = basename($item, '.md');
                $title = $this->extractTitle($fullPath);
                $sortKey = $this->extractSortKey($fileName);
                // Si le titre contient aussi un |, extraire la partie droite
                if (strpos($title, '|') !== false) {
                    $titleParts = explode('|', $title, 2);
                    $title = trim($titleParts[1]);
                    $sortKey = trim($titleParts[0]);
                }
                $this->menuItems[$relativePath] = [
                    'type' => 'file',
                    'label' => $title,
                    'sortKey' => $sortKey,
                    'path' => $fileName,
                    'file' => $relativePath
                ];
            }
        }
    }

    /**
     * Extrait le titre depuis un fichier Markdown (première ligne avec #)
     *
     * @param string $filePath Chemin du fichier
     * @return string Titre extrait ou nom du fichier formaté
     */
    private function extractTitle($filePath)
    {
        if (!file_exists($filePath)) {
            return $this->formatLabel(basename($filePath, '.md'));
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content, 2);
        
        if (!empty($lines[0])) {
            $firstLine = trim($lines[0]);
            // Vérifier si c'est un titre Markdown (# Titre)
            if (preg_match('/^#+\s+(.+)$/', $firstLine, $matches)) {
                $title = trim($matches[1]);
                // Remplacer les underscores par des espaces
                $title = str_replace('_', ' ', $title);
                // Ne pas traiter le | ici, c'est fait dans scanDirectory
                return $title;
            }
        }

        // Si pas de titre trouvé, utiliser le nom du fichier
        return $this->formatLabel(basename($filePath, '.md'));
    }

    /**
     * Extrait la partie droite après le séparateur | (pour l'affichage)
     *
     * @param string $name Nom à traiter
     * @return string Partie droite ou nom complet si pas de séparateur
     */
    private function extractDisplayLabel($name)
    {
        if (strpos($name, '|') !== false) {
            $parts = explode('|', $name, 2);
            return trim($parts[1]);
        }
        return $name;
    }

    /**
     * Extrait la partie gauche avant le séparateur | (pour le tri)
     *
     * @param string $name Nom à traiter
     * @return string Partie gauche ou nom complet si pas de séparateur
     */
    private function extractSortKey($name)
    {
        if (strpos($name, '|') !== false) {
            $parts = explode('|', $name, 2);
            return trim($parts[0]);
        }
        return $name;
    }

    /**
     * Formate un nom de fichier/dossier en label lisible
     *
     * @param string $name Nom à formater
     * @return string Label formaté
     */
    private function formatLabel($name)
    {
        // Extraire la partie d'affichage (après |)
        $displayName = $this->extractDisplayLabel($name);
        
        // Remplacer les tirets et underscores par des espaces
        $displayName = str_replace(['-', '_'], ' ', $displayName);
        // Mettre en majuscule la première lettre de chaque mot
        return ucwords($displayName);
    }

    /**
     * Génère le HTML du menu
     *
     * @param string $currentPage Page actuelle (pour marquer l'élément actif)
     * @return string HTML du menu
     */
    public function render($currentPage = '')
    {
        $html = '';
        $menuItems = $this->generate();

        foreach ($menuItems as $item) {
            if ($item['type'] === 'folder') {
                // Dossier = sous-menu
                $hasChildren = !empty($item['children']);
                $html .= '<div class="nav-item">';
                $html .= '<a href="#" class="nav-link' . ($hasChildren ? ' has-submenu' : '') . '">';
                $html .= htmlspecialchars($item['label']);
                $html .= '</a>';
                
                if ($hasChildren) {
                    $html .= '<ul class="submenu">';
                    foreach ($item['children'] as $child) {
                        $isActive = ($currentPage === $child['path']);
                        $html .= '<li class="submenu-item">';
                        $html .= '<a href="?page=' . urlencode($child['path']) . '" class="submenu-link' . ($isActive ? ' active' : '') . '">';
                        $html .= htmlspecialchars($child['label']);
                        $html .= '</a>';
                        $html .= '</li>';
                    }
                    $html .= '</ul>';
                }
                $html .= '</div>';
            } else {
                // Fichier = lien simple
                $isActive = ($currentPage === $item['path']);
                $html .= '<div class="nav-item">';
                $html .= '<a href="?page=' . urlencode($item['path']) . '" class="nav-link' . ($isActive ? ' active' : '') . '">';
                $html .= htmlspecialchars($item['label']);
                $html .= '</a>';
                $html .= '</div>';
            }
        }

        return $html;
    }
}
