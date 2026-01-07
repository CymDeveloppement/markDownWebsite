# Application PHP - Site Web Markdown Multi-langue

Application PHP moderne pour créer des sites web basés sur des fichiers Markdown avec support multi-langue, recherche intégrée et mise à jour automatique via Git.

## 🚀 Fonctionnalités

### Contenu et Navigation
- **Génération dynamique du menu** à partir de la structure des fichiers Markdown
- **Support Markdown complet** avec le parser Parsedown
- **Tri personnalisé** des éléments de menu avec le système de séparation `|`
- **Structure hiérarchique** : pages à la racine et sous-menus via dossiers

### Multi-langue
- **Support multi-langue** complet basé sur la structure de dossiers
- **Sélecteur de langue** avec drapeaux emoji
- **Persistance de la langue** via cookies
- **Redirection automatique** vers la page d'accueil de la langue sélectionnée

### Recherche
- **Recherche en temps réel** dans tous les fichiers Markdown
- **Recherche limitée à la langue courante**
- **Mise en évidence** des termes recherchés dans les résultats
- **Interface modale** élégante pour la recherche

### Mise à jour Git
- **Mise à jour automatique** depuis un dépôt Git
- **Deux modes** : mise à jour des pages uniquement ou de l'application complète
- **Support des clés SSH** pour l'authentification
- **Détection automatique** de clés SSH à la racine de l'application

### Autres fonctionnalités
- **Logo personnalisable** (logo.png à la racine)
- **Mesure du temps de chargement** affichée dans le footer
- **Interface moderne** et responsive
- **Configuration via .env** pour une personnalisation facile

## 📁 Structure du projet

```
.
├── public/              # Dossier public (racine web)
│   ├── index.php       # Point d'entrée principal
│   ├── search.php      # Endpoint de recherche
│   ├── update.php      # Endpoint de mise à jour Git
│   ├── css/
│   │   └── style.css   # Feuille de style
│   └── .htaccess       # Configuration Apache
├── src/                # Code source de l'application
│   ├── GitUpdate.php   # Classe de mise à jour Git
│   ├── MarkdownParser.php  # Parser Markdown
│   ├── MenuGenerator.php   # Générateur de menu
│   ├── Search.php          # Moteur de recherche
│   └── view/
│       └── layout.php      # Template principal
├── pages/              # Contenu Markdown
│   ├── fr/            # Pages en français
│   ├── en/            # Pages en anglais
│   └── uk/            # Pages en ukrainien
├── vendor/            # Dépendances Composer
├── .env               # Configuration (à créer depuis .env.example)
├── .env.example       # Exemple de configuration
├── composer.json      # Dépendances PHP
└── README.md          # Ce fichier
```

## 🔧 Installation

### Prérequis
- PHP >= 7.4
- Composer
- Git (pour les mises à jour)
- Serveur web (Apache avec mod_rewrite ou Nginx)

### Étapes d'installation

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/CymDeveloppement/markDownWebsite.git
   cd markDownWebsite
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   ```

3. **Configurer l'environnement**
   ```bash
   cp .env.example .env
   # Éditer .env selon vos besoins
   ```

4. **Configurer le serveur web**
   
   **Apache** : Configurez votre VirtualHost pour pointer vers le dossier `public/`
   
   **Nginx** : Configurez le serveur pour utiliser `public/` comme racine documentaire

   **Serveur de développement PHP** :
   ```bash
   php -S localhost:8000 -t public/
   ```

## ⚙️ Configuration

### Fichier .env

Copiez `.env.example` vers `.env` et configurez :

```env
APP_NAME="Mon Site Web"
APP_ENV=production
LANGUAGES="fr:Français,en:English,uk:Українська"
DEFAULT_LANGUAGE="fr"
```

- **APP_NAME** : Nom de l'application (affiché dans le header et footer)
- **APP_ENV** : Environnement (development, production)
- **LANGUAGES** : Langues disponibles au format `code:Nom`
- **DEFAULT_LANGUAGE** : Langue par défaut (doit être dans LANGUAGES)

## 📝 Organisation des pages

### Pages à la racine
Les fichiers Markdown à la racine de `pages/[langue]/` apparaissent comme des liens directs dans le menu.

**Exemple :**
```
pages/fr/
├── accueil.md     → Lien "Accueil" dans le menu
├── services.md    → Lien "Services" dans le menu
└── contact.md     → Lien "Contact" dans le menu
```

### Sous-menus (dossiers)
Les dossiers créent automatiquement des sous-menus avec leurs fichiers.

**Exemple :**
```
pages/fr/
├── solutions/
│   ├── web.md     → "Web" dans le sous-menu "Solutions"
│   └── mobile.md  → "Mobile" dans le sous-menu "Solutions"
```

### Tri personnalisé
Utilisez le séparateur `|` pour contrôler l'ordre d'affichage :
- Format : `clé_tri|Nom d'affichage`
- La partie gauche (clé_tri) est utilisée pour le tri
- La partie droite est affichée dans le menu

**Exemple :**
```
z|À propos/        → Tri comme "z" mais affiché "À propos"
12|Services        → Tri comme "12" mais affiché "Services"
```

### Titres des pages
Les titres sont extraits automatiquement :
1. Depuis le Markdown : première ligne avec `# Titre`
2. Sinon depuis le nom du fichier : formaté automatiquement

## 🔍 Recherche

La recherche permet de trouver du contenu dans tous les fichiers Markdown de la langue courante.

- **Accès** : Cliquez sur l'icône de recherche dans le header
- **Recherche en temps réel** : Les résultats s'affichent automatiquement
- **Mise en évidence** : Les termes recherchés sont surlignés

**Endpoint API :** `/search.php?q=terme`

## 🔄 Mise à jour Git

L'application peut se mettre à jour automatiquement depuis un dépôt Git.

### Mise à jour des pages uniquement
```
/update.php?scope=pages
/update.php?scope=pages&repository=https://github.com/user/repo-pages.git
```

### Mise à jour de l'application complète
```
/update.php?scope=full
```

### Authentification SSH
Placez une clé SSH privée à la racine de l'application avec l'un de ces noms :
- `id_rsa`
- `id_ed25519`
- `id_ecdsa`
- `git_key`
- `ssh_key`
- `deploy_key`

La clé sera automatiquement détectée et utilisée pour les opérations Git.

**Important** : Assurez-vous que la clé a les bonnes permissions (chmod 600).

## 🎨 Personnalisation

### Logo
Placez un fichier `logo.png` à la racine de l'application pour remplacer l'emoji par défaut.

### Styles
Modifiez `public/css/style.css` pour personnaliser l'apparence.

### Langues
Ajoutez des langues dans `.env` et créez les dossiers correspondants dans `pages/`.

## 📚 Documentation

Pour plus de détails, consultez **[DOCUMENTATION.md](DOCUMENTATION.md)**.

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou une pull request.

## 📄 Licence

Consultez le fichier [LICENSE](LICENSE) pour plus d'informations.

## 🛠️ Développement

### Classes principales

- **`App\MarkdownParser`** : Parse les fichiers Markdown en HTML
- **`App\MenuGenerator`** : Génère dynamiquement le menu de navigation
- **`App\Search`** : Moteur de recherche dans les fichiers Markdown
- **`App\GitUpdate`** : Gestion des mises à jour depuis Git

### Dépendances

- **erusev/parsedown** : Parser Markdown rapide et léger
- **vlucas/phpdotenv** : Gestion des variables d'environnement

## 📞 Support

Pour toute question ou problème, ouvrez une issue sur GitHub.

---

**Développé avec ❤️ pour créer des sites web simples et efficaces**
