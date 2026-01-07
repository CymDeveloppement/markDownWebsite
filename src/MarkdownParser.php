<?php

namespace App;

use Parsedown;

class MarkdownParser
{
    private $parsedown;
    private $pagesPath;

    public function __construct($pagesPath = null)
    {
        $this->parsedown = new Parsedown();
        $this->pagesPath = $pagesPath ?: BASE_PATH . '/pages';
    }

    /**
     * Parse un fichier Markdown et retourne le HTML
     *
     * @param string $filename Nom du fichier (avec ou sans extension .md)
     * @return string HTML généré
     */
    public function parseFile($filename)
    {
        // Ajouter l'extension .md si nécessaire
        if (substr($filename, -3) !== '.md') {
            $filename .= '.md';
        }

        $filePath = $this->pagesPath . '/' . $filename;

        if (!file_exists($filePath)) {
            return '<p>Page introuvable : ' . htmlspecialchars($filename) . '</p>';
        }

        $markdown = file_get_contents($filePath);
        return $this->parsedown->text($markdown);
    }

    /**
     * Parse du contenu Markdown brut
     *
     * @param string $markdown Contenu Markdown
     * @return string HTML généré
     */
    public function parse($markdown)
    {
        return $this->parsedown->text($markdown);
    }
}
