<?php

namespace App;

class GitUpdate
{
    private $basePath;
    private $pagesPath;
    private $errors = [];
    private $messages = [];
    private $sshKeyPath = null;

    public function __construct($basePath = null)
    {
        $this->basePath = $basePath ?: BASE_PATH;
        $this->pagesPath = $this->basePath . '/pages';
        $this->detectSshKey();
    }

    /**
     * Mettre à jour depuis un dépôt Git
     *
     * @param string $scope Scope de la mise à jour : 'pages' ou 'full'
     * @param string $repository URL du dépôt Git (optionnel, utilise le dépôt actuel si vide)
     * @return array Résultat de la mise à jour avec 'success', 'messages', 'errors'
     */
    public function update($scope = 'pages', $repository = null)
    {
        $this->errors = [];
        $this->messages = [];

        // Valider le scope
        if (!in_array($scope, ['pages', 'full'])) {
            $this->errors[] = "Scope invalide. Utilisez 'pages' ou 'full'.";
            return $this->getResult();
        }

        // Vérifier que Git est disponible
        if (!$this->isGitAvailable()) {
            $this->errors[] = "Git n'est pas disponible sur ce serveur.";
            return $this->getResult();
        }

        try {
            if ($scope === 'pages') {
                return $this->updatePages($repository);
            } else {
                return $this->updateFull($repository);
            }
        } catch (\Exception $e) {
            $this->errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
            return $this->getResult();
        }
    }

    /**
     * Mettre à jour uniquement le dossier pages
     *
     * @param string|null $repository URL du dépôt Git pour les pages
     * @return array Résultat de la mise à jour
     */
    private function updatePages($repository = null)
    {
        // Si un dépôt est fourni, cloner ou mettre à jour
        if ($repository) {
            return $this->updateFromRepository($this->pagesPath, $repository);
        }

        // Sinon, mettre à jour depuis le dépôt Git actuel (si pages est un sous-module ou un dépôt séparé)
        if (!$this->isGitRepository($this->pagesPath)) {
            $this->errors[] = "Le dossier pages n'est pas un dépôt Git. Veuillez fournir une URL de dépôt.";
            return $this->getResult();
        }

        // Mettre à jour le dépôt pages
        $this->messages[] = "Mise à jour du dossier pages depuis Git...";
        $output = $this->executeGitCommand($this->pagesPath, 'pull');
        
        if ($output['success']) {
            $this->messages[] = "Pages mises à jour avec succès.";
            $this->messages[] = $output['output'];
        } else {
            $this->errors[] = "Erreur lors de la mise à jour des pages : " . $output['error'];
        }

        return $this->getResult();
    }

    /**
     * Mettre à jour l'application complète
     *
     * @param string|null $repository URL du dépôt Git pour l'application
     * @return array Résultat de la mise à jour
     */
    private function updateFull($repository = null)
    {
        // Vérifier que la racine est un dépôt Git
        if (!$this->isGitRepository($this->basePath)) {
            $this->errors[] = "La racine de l'application n'est pas un dépôt Git.";
            return $this->getResult();
        }

        $this->messages[] = "Mise à jour de l'application complète depuis Git...";
        
        // Récupérer les changements
        $fetchOutput = $this->executeGitCommand($this->basePath, 'fetch');
        if (!$fetchOutput['success']) {
            $this->errors[] = "Erreur lors de la récupération des changements : " . $fetchOutput['error'];
            return $this->getResult();
        }

        // Mettre à jour
        $pullOutput = $this->executeGitCommand($this->basePath, 'pull');
        
        if ($pullOutput['success']) {
            $this->messages[] = "Application mise à jour avec succès.";
            $this->messages[] = $pullOutput['output'];
            
            // Si Composer est utilisé, mettre à jour les dépendances
            if (file_exists($this->basePath . '/composer.json')) {
                $this->messages[] = "Mise à jour des dépendances Composer...";
                $composerOutput = $this->executeComposerUpdate();
                if ($composerOutput['success']) {
                    $this->messages[] = "Dépendances mises à jour.";
                } else {
                    $this->messages[] = "Attention : Erreur lors de la mise à jour des dépendances : " . $composerOutput['error'];
                }
            }
        } else {
            $this->errors[] = "Erreur lors de la mise à jour : " . $pullOutput['error'];
        }

        return $this->getResult();
    }

    /**
     * Mettre à jour depuis un dépôt Git spécifique
     *
     * @param string $targetPath Chemin cible
     * @param string $repository URL du dépôt Git
     * @return array Résultat de la mise à jour
     */
    private function updateFromRepository($targetPath, $repository)
    {
        if ($this->isGitRepository($targetPath)) {
            // Définir le remote si nécessaire et mettre à jour
            $this->messages[] = "Mise à jour depuis le dépôt : $repository";
            $output = $this->executeGitCommand($targetPath, 'pull');
            
            if ($output['success']) {
                $this->messages[] = "Mise à jour réussie.";
                $this->messages[] = $output['output'];
            } else {
                $this->errors[] = "Erreur : " . $output['error'];
            }
        } else {
            // Cloner le dépôt
            $this->messages[] = "Clonage du dépôt : $repository";
            
            // Sauvegarder le contenu existant si nécessaire
            if (is_dir($targetPath) && count(scandir($targetPath)) > 2) {
                $backupPath = $targetPath . '.backup.' . date('Y-m-d_H-i-s');
                if (rename($targetPath, $backupPath)) {
                    $this->messages[] = "Sauvegarde créée : " . basename($backupPath);
                }
            }
            
            // Construire la commande clone avec support de la clé SSH
            $cloneCommand = "git clone " . escapeshellarg($repository) . " " . escapeshellarg($targetPath);
            
            // Ajouter la configuration SSH si une clé est disponible
            if ($this->sshKeyPath) {
                $sshCommand = 'ssh -i ' . escapeshellarg($this->sshKeyPath) . 
                             ' -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null';
                $cloneCommand = 'GIT_SSH_COMMAND=' . escapeshellarg($sshCommand) . ' ' . $cloneCommand;
            }
            
            $cloneOutput = $this->executeCommand($cloneCommand);
            
            if ($cloneOutput['success']) {
                $this->messages[] = "Dépôt cloné avec succès.";
            } else {
                $this->errors[] = "Erreur lors du clonage : " . $cloneOutput['error'];
                // Restaurer la sauvegarde en cas d'erreur
                if (isset($backupPath) && is_dir($backupPath)) {
                    rename($backupPath, $targetPath);
                    $this->messages[] = "Sauvegarde restaurée.";
                }
            }
        }

        return $this->getResult();
    }

    /**
     * Vérifier si Git est disponible
     *
     * @return bool
     */
    private function isGitAvailable()
    {
        $result = $this->executeCommand('git --version');
        return $result['success'];
    }

    /**
     * Vérifier si un répertoire est un dépôt Git
     *
     * @param string $path Chemin à vérifier
     * @return bool
     */
    private function isGitRepository($path)
    {
        return is_dir($path . '/.git');
    }

    /**
     * Détecter la clé SSH à la racine de l'application
     */
    private function detectSshKey()
    {
        // Chercher des clés SSH communes
        $possibleKeys = [
            'id_rsa',
            'id_ed25519',
            'id_ecdsa',
            'git_key',
            'ssh_key',
            'deploy_key'
        ];

        foreach ($possibleKeys as $keyName) {
            $keyPath = $this->basePath . '/' . $keyName;
            if (file_exists($keyPath) && is_readable($keyPath)) {
                $this->sshKeyPath = $keyPath;
                $this->messages[] = "Clé SSH détectée : " . basename($keyPath);
                return;
            }
        }
    }

    /**
     * Exécuter une commande Git avec support de la clé SSH
     *
     * @param string $path Chemin du dépôt
     * @param string $command Commande Git (sans 'git')
     * @return array Résultat avec 'success', 'output', 'error'
     */
    private function executeGitCommand($path, $command)
    {
        $fullCommand = 'cd ' . escapeshellarg($path);
        
        // Ajouter la configuration SSH si une clé est disponible
        if ($this->sshKeyPath) {
            $sshCommand = 'ssh -i ' . escapeshellarg($this->sshKeyPath) . 
                         ' -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null';
            $fullCommand .= ' && GIT_SSH_COMMAND=' . escapeshellarg($sshCommand);
        }
        
        $fullCommand .= ' && git ' . $command . ' 2>&1';
        return $this->executeCommand($fullCommand);
    }

    /**
     * Mettre à jour les dépendances Composer
     *
     * @return array Résultat de la commande
     */
    private function executeComposerUpdate()
    {
        $command = 'cd ' . escapeshellarg($this->basePath) . ' && composer install --no-dev --optimize-autoloader 2>&1';
        return $this->executeCommand($command);
    }

    /**
     * Exécuter une commande shell
     *
     * @param string $command Commande à exécuter
     * @return array Résultat avec 'success', 'output', 'error'
     */
    private function executeCommand($command)
    {
        $output = [];
        $returnVar = 0;
        
        exec($command, $output, $returnVar);
        
        $outputString = implode("\n", $output);
        
        return [
            'success' => $returnVar === 0,
            'output' => $outputString,
            'error' => $returnVar !== 0 ? $outputString : null
        ];
    }

    /**
     * Obtenir le résultat formaté
     *
     * @return array Résultat avec 'success', 'messages', 'errors'
     */
    private function getResult()
    {
        return [
            'success' => empty($this->errors),
            'messages' => $this->messages,
            'errors' => $this->errors
        ];
    }
}
