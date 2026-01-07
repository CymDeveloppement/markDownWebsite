# Documentation - Application PHP Markdown

Cette documentation explique comment utiliser et configurer l'application.

## Table des matières

1. [Configuration avec le fichier .env](#configuration-avec-le-fichier-env)
2. [Organisation des pages et menu](#organisation-des-pages-et-menu)
3. [Format Markdown](#format-markdown)
4. [Système de tri personnalisé](#système-de-tri-personnalisé)

---

## Configuration avec le fichier .env

Le fichier `.env` permet de configurer l'application sans modifier le code.

### Installation

1. Copiez le fichier `.env.example` vers `.env` :
   ```bash
   cp .env.example .env
   ```

2. Le fichier `.env` est déjà ignoré par Git (dans `.gitignore`), vous pouvez le modifier librement sans risque.

### Variables disponibles

#### APP_NAME
Nom de l'application affiché dans le header et le footer.

**Exemple :**
```
APP_NAME="Mon Site Web"
```

#### APP_ENV
Environnement de l'application (development, production, etc.)

**Exemple :**
```
APP_ENV=development
```

#### LANGUAGES
Langues disponibles pour le site. Format : `code:nom` séparés par des virgules.

**Format :**
```
LANGUAGES="code1:Nom1,code2:Nom2,code3:Nom3"
```

**Exemples :**
```
# Français uniquement
LANGUAGES="fr:Français"

# Français et Anglais
LANGUAGES="fr:Français,en:English"

# Multi-langues
LANGUAGES="fr:Français,en:English,es:Español,de:Deutsch"
```

**Codes de langues :** Utilisez les codes ISO 639-1 (2 lettres), par exemple :
- `fr` pour Français
- `en` pour English
- `es` pour Español
- `de` pour Deutsch
- `it` pour Italiano
- etc.

#### DEFAULT_LANGUAGE
Langue par défaut du site (code de la langue).

**Exemple :**
```
DEFAULT_LANGUAGE="fr"
```

**Note :** Le code doit correspondre à une langue définie dans `LANGUAGES`.

### Ajouter de nouvelles variables

1. Ajoutez votre variable dans `.env` :
   ```
   MA_VARIABLE="ma valeur"
   ```

2. Utilisez-la dans votre code PHP :
   ```php
   $maVariable = $_ENV['MA_VARIABLE'];
   ```

3. N'oubliez pas d'ajouter un exemple dans `.env.example` pour les autres développeurs.

---

## Organisation des pages et menu

### Structure de base

Les pages sont organisées dans le dossier `pages/`. Le menu est généré automatiquement à partir de cette structure.

### Pages à la racine

Les fichiers Markdown à la racine de `pages/` apparaissent comme des liens directs dans le menu.

**Structure :**
```
pages/
├── accueil.md          → Lien "Accueil" dans le menu
├── services.md         → Lien "Services" dans le menu
└── contact.md          → Lien "Contact" dans le menu
```

**Résultat dans le menu :**
- Accueil
- Services
- Contact

### Sous-menus (dossiers)

Les dossiers dans `pages/` créent automatiquement des sous-menus avec les fichiers qu'ils contiennent.

**Structure :**
```
pages/
├── apropos/
│   ├── equipe.md       → "Notre Équipe" dans le sous-menu "À propos"
│   └── histoire.md     → "Notre Histoire" dans le sous-menu "À propos"
└── solutions/
    ├── web.md          → "Web" dans le sous-menu "Solutions"
    └── mobile.md       → "Mobile" dans le sous-menu "Solutions"
```

**Résultat dans le menu :**
- À propos ▼
  - Notre Équipe
  - Notre Histoire
- Solutions ▼
  - Web
  - Mobile

### Nommage des fichiers

- **Fichiers** : Utilisez des noms en minuscules avec des tirets ou underscores
  - ✅ `ma-page.md`
  - ✅ `ma_page.md`
  - ❌ `MaPage.md` (sera converti mais moins pratique)

- **Dossiers** : Même convention que les fichiers
  - ✅ `mon-dossier/`
  - ✅ `mon_dossier/`

### Titres des pages

Le titre affiché dans le menu est extrait automatiquement :

1. **Depuis le fichier Markdown** : La première ligne avec `# Titre` est utilisée
   ```markdown
   # Mon Titre de Page
   ```

2. **Depuis le nom du fichier** : Si aucun titre n'est trouvé, le nom du fichier est formaté
   - `ma-page.md` → "Ma Page"
   - `mon_article.md` → "Mon Article"

---

## Format Markdown

Les fichiers doivent être au format Markdown (`.md`). Le système utilise Parsedown pour convertir le Markdown en HTML.

### Exemple de page

```markdown
# Titre de la page

## Sous-titre

Voici du **texte en gras** et du *texte en italique*.

### Liste à puces

- Élément 1
- Élément 2
- Élément 3

### Liste numérotée

1. Premier élément
2. Deuxième élément
3. Troisième élément
```

### Fonctionnalités Markdown supportées

- Titres (`#`, `##`, `###`, etc.)
- Texte en gras (`**texte**`)
- Texte en italique (`*texte*`)
- Listes à puces et numérotées
- Liens (`[texte](url)`)
- Images (`![alt](url)`)
- Code inline (`` `code` ``)
- Blocs de code (``` ``` ```)

---

## Système de tri personnalisé

Vous pouvez contrôler l'ordre d'affichage des éléments du menu en utilisant le caractère `|` comme séparateur.

### Principe

- **Partie gauche** (avant `|`) : Utilisée pour le tri (invisible)
- **Partie droite** (après `|`) : Affichée dans le menu

### Pour les dossiers

**Structure :**
```
pages/
├── 01|accueil/
│   └── presentation.md
├── 02|services/
│   └── web.md
└── 03|contact/
    └── formulaire.md
```

**Résultat :** Les dossiers apparaissent dans l'ordre : Accueil, Services, Contact (triés par 01, 02, 03)

### Pour les fichiers

**Option 1 : Dans le nom du fichier**
```
pages/
├── 01|accueil.md
├── 02|services.md
└── 03|contact.md
```

**Option 2 : Dans le titre Markdown**
```markdown
# 01|Bienvenue sur notre site
```

Dans les deux cas, seul "Bienvenue sur notre site" sera affiché, mais le tri utilisera "01".

### Exemples pratiques

#### Tri numérique
```
01|Première page.md
02|Deuxième page.md
03|Troisième page.md
```

#### Tri alphabétique contrôlé
```
A|Accueil.md
B|Blog.md
C|Contact.md
```

#### Tri mixte
```
1|Accueil.md
2|Services/
3|À propos/
Z|Contact.md
```

### Notes importantes

- Le séparateur `|` n'apparaît jamais dans le menu (seule la partie droite est affichée)
- Le tri est alphabétique/numérique sur la partie gauche
- Vous pouvez utiliser des nombres, lettres ou combinaisons
- Les underscores (`_`) dans les titres sont automatiquement remplacés par des espaces

---

## Conseils et bonnes pratiques

### Organisation recommandée

```
pages/
├── 01|accueil.md
├── 02|services/
│   ├── 01|developpement-web.md
│   ├── 02|e-commerce.md
│   └── 03|conseil.md
├── 03|apropos/
│   ├── 01|equipe.md
│   ├── 02|histoire.md
│   └── 03|valeurs.md
└── 99|contact.md
```

### Nommage cohérent

- Utilisez des noms descriptifs et cohérents
- Préférez les minuscules et les tirets
- Évitez les espaces dans les noms de fichiers
- Utilisez le système de tri pour un ordre logique

### Structure modulaire

- Groupez les pages liées dans des dossiers
- Utilisez des sous-menus pour améliorer la navigation
- Ne créez pas trop de niveaux (2 niveaux max recommandés)

---

## Exemples complets

### Exemple 1 : Site d'entreprise simple

```
pages/
├── accueil.md
├── services.md
├── apropos/
│   ├── equipe.md
│   └── histoire.md
└── contact.md
```

### Exemple 2 : Site avec tri personnalisé

```
pages/
├── 01|accueil.md
├── 02|produits/
│   ├── 01|produit-a.md
│   ├── 02|produit-b.md
│   └── 03|produit-c.md
├── 03|support/
│   ├── 01|faq.md
│   └── 02|contact.md
└── 99|mentions-legales.md
```

---

## Résolution de problèmes

### Le menu ne se met pas à jour

- Vérifiez que les fichiers ont l'extension `.md`
- Vérifiez que les dossiers contiennent bien des fichiers `.md`
- Videz le cache du navigateur

### Un titre n'apparaît pas correctement

- Vérifiez que la première ligne du fichier est un titre Markdown (`# Titre`)
- Vérifiez qu'il n'y a pas d'espace avant le `#`
- Si vous utilisez `|`, vérifiez que la partie droite contient le texte à afficher

### Le tri ne fonctionne pas

- Vérifiez que vous utilisez bien le caractère `|` (pipe)
- Vérifiez qu'il n'y a pas d'espaces autour du `|`
- Le tri est insensible à la casse

---

## Support

Pour toute question ou problème, consultez la documentation ou le code source de l'application.
