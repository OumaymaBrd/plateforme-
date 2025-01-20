# 🎓 Youdemy - Plateforme de Cours en Ligne

## 📁 Structure du Projet

```
youdemy/
│
├── assets/              # Ressources statiques
│   ├── js/             # Scripts JavaScript
│   ├── pages/          # Pages statiques
│   └── style/          # Fichiers CSS
│
├── db/                 # Configuration et scripts de base de données
│
├── models/             # Modèles de l'application
│   ├── admin.php       # Gestion des administrateurs
│   ├── categorie.php   # Gestion des catégories
│   ├── cours.php       # Gestion des cours
│   ├── coursvideo.php  # Gestion des vidéos de cours
│   ├── document.php    # Gestion des documents
│   ├── etudiant.php    # Gestion des étudiants
│   ├── inscris_cours.php # Gestion des inscriptions aux cours
│   ├── logout.php      # Gestion de la déconnexion
│   ├── tags_courses.php # Relations tags-cours
│   ├── tags.php        # Gestion des tags
│   └── user.php        # Gestion des utilisateurs
│
├── script/             # Scripts utilitaires
│
├── UML/                # Documentation UML
│
├── uploads/            # Fichiers uploadés par les utilisateurs
│
├── .gitignore         # Configuration Git
├── home.php           # Page d'accueil
├── index.php          # Point d'entrée de l'application
└── README.md          # Documentation du projet
```

## 📚 Description des Composants

### 📂 Assets
- `js/` : Contient tous les scripts JavaScript pour l'interactivité côté client
- `pages/` : Pages statiques de l'application
- `style/` : Feuilles de style CSS pour le design

### 💾 Models
- `admin.php` : Gestion des fonctionnalités administrateur
- `categorie.php` : CRUD des catégories de cours
- `cours.php` : Gestion complète des cours
- `coursvideo.php` : Gestion des contenus vidéo
- `document.php` : Gestion des documents pédagogiques
- `etudiant.php` : Gestion des comptes étudiants
- `inscris_cours.php` : Gestion des inscriptions aux cours
- `tags.php` & `tags_courses.php` : Système de tags et relations

### 🗃️ Base de Données
Le dossier `db/` contient les configurations et scripts de base de données.

### 📤 Uploads
Le dossier `uploads/` stocke tous les fichiers uploadés par les utilisateurs (documents, vidéos, etc.).

## 🛠️ Configuration Requise

- PHP 8.2.13
- MySQL 8.2.0
- Serveur Web (Apache)
- Extensions PHP requises:
  - PDO
  - MySQL
  - FileInfo
  - Session

## 📥 Installation

1. Clonez le dépôt
```bash
git clone https://github.com/OumaymaBrd/plateforme-
```

2. Configurez votre base de données dans `db/Database.php`
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'youdemy');
define('DB_USER', 'root');
define('DB_PASS', '');
```

3. Importez la structure de la base de données
```bash
mysql -u root -p youdemy < 
```

4. Configurez les permissions des dossiers
```bash
chmod 755 uploads/
chmod 755 assets/
```

## 🔐 Sécurité

- Validation des données utilisateur
- Protection contre les injections SQL via PDO
- Contrôle d'accès basé sur les rôles
- Hashage sécurisé des mots de passe
- Protection des uploads de fichiers

## 👥 Rôles Utilisateurs

1. **Visiteur**
   - Consultation du catalogue
   - Recherche de cours
   - Création de compte

2. **Étudiant**
   - Inscription aux cours
   - Accès aux contenus
   - Suivi de progression

3. **Enseignant**
   - Création de cours
   - Gestion des contenus
   - Suivi des étudiants

4. **Administrateur**
   - Gestion des utilisateurs
   - Validation des cours
   - Administration système

## 📊 Fonctionnalités Principales

- Système d'authentification
- Gestion des cours (CRUD)
- Système de tags
- Upload de fichiers
- Statistiques et rapports
- Interface responsive

## 🤝 Contribution

1. Fork le projet
2. Créez votre branche (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 📫 Contact

Votre Nom - [@twitter_handle](https://twitter.com/twitter_handle) - email@example.com

Lien du projet: [https://github.com/votre-username/youdemy](https://github.com/votre-username/youdemy)

---

© 2025 Youdemy. Tous droits réservés.