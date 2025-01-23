# 🎓 Youdemy - Plateforme de Cours en Ligne

## 📚 Aperçu du Projet

Youdemy est une plateforme de cours en ligne innovante, conçue pour révolutionner l'apprentissage en offrant une expérience interactive et personnalisée aux étudiants et aux enseignants. Le projet est construit sur les principes de la programmation orientée objet (OOP) en PHP natif, garantissant une architecture modulaire, claire et extensible.

## 🌟 Fonctionnalités Principales

### 🖥️ Front Office

#### 👥 Visiteur
- 📚 Accès au catalogue des cours avec pagination
- 🔍 Recherche de cours par mots-clés
- 📝 Création d'un compte (Étudiant ou Enseignant)

#### 🎓 Étudiant
- 👀 Visualisation du catalogue des cours
- 🔎 Recherche et consultation détaillée des cours
- ✅ Inscription aux cours après authentification
- 📊 Accès à la section "Mes cours"

#### 👨‍🏫 Enseignant
- ➕ Ajout de nouveaux cours (titre, description, contenu, tags, catégorie)
- 🛠️ Gestion des cours (modification, suppression, consultation des inscriptions)
- 📈 Accès aux statistiques des cours

### ⚙️ Back Office

#### 👑 Administrateur
- ✔️ Validation des comptes enseignants
- 👥 Gestion des utilisateurs (activation, suspension, suppression)
- 🗂️ Gestion des contenus (cours, catégories, tags)
- 🏷️ Insertion en masse de tags
- 📊 Accès aux statistiques globales

## 🔄 Fonctionnalités Transversales

- 🏷️ Relations many-to-many pour les tags des cours
- 🔄 Polymorphisme pour l'ajout et l'affichage des cours
- 🔐 Système d'authentification et d'autorisation
- 🚦 Contrôle d'accès basé sur les rôles

## 💻 Exigences Techniques

- 🧱 Principes OOP (encapsulation, héritage, polymorphisme)
- 🗃️ Base de données relationnelle (one-to-many, many-to-many)
- 🔑 Sessions PHP pour la gestion des utilisateurs
- 🛡️ Validation des données utilisateur

## 🛠️ Technologies Utilisées

- 🐘 PHP (OOP)
- 🗄️ MySQL
- 🌐 HTML5
- 🎨 CSS3
- 🖥️ JavaScript
- 📱 Design Responsive

## 🚀 Installation et Configuration

1. Clonez le dépôt :
   \`\`\`
   git clone https://github.com/votre-nom/youdemy.git
   \`\`\`

2. Configurez votre serveur web pour pointer vers le dossier du projet.

3. Importez la base de données à partir du fichier SQL fourni.

4. Configurez les paramètres de connexion à la base de données dans le fichier \`config.php\`.

5. Lancez l'application dans votre navigateur.

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou à soumettre une pull request.

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

