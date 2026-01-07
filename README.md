# Application PHP

Architecture minimale pour une application PHP avec séparation public/privé.

## Structure

```
.
├── public/          # Dossier public (point d'entrée web)
│   ├── index.php    # Point d'entrée de l'application
│   └── .htaccess    # Configuration Apache (réécriture d'URL)
├── src/             # Code source de l'application (à créer)
├── .gitignore       # Fichiers à ignorer par Git
└── README.md        # Ce fichier
```

## Configuration

Le serveur web doit pointer vers le dossier `public/` comme racine documentaire.

### Apache

Configurez votre VirtualHost pour utiliser `/public` comme DocumentRoot :

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /chemin/vers/markdown-website/public
    <Directory /chemin/vers/markdown-website/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Serveur de développement PHP

Depuis le dossier `public/` :

```bash
php -S localhost:8000
```

Ou depuis la racine du projet :

```bash
php -S localhost:8000 -t public/
```

## Développement

1. Créez vos classes dans le dossier `src/`
2. L'autoloader est configuré pour charger automatiquement les classes
3. Tous les fichiers accessibles publiquement doivent être dans `public/`

## Documentation

Pour plus d'informations sur :
- La configuration avec le fichier `.env`
- L'organisation des pages et la génération du menu
- Le système de tri personnalisé

Consultez le fichier **[DOCUMENTATION.md](DOCUMENTATION.md)**
