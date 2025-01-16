<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/User.php';
require_once '../../models/Cours.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_post'] !== 'enseignant' || $_SESSION['user_status'] !== 'accepter') {
    header("Location: login.php");
    exit();
}

$matricule = isset($_GET['matricule']) ? htmlspecialchars($_GET['matricule']) : 'N/A';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->id = $_SESSION['user_id'];
$user->matricule = $_SESSION['user_matricule'];

$cours = new Cours($db);
$courses = $cours->getCoursesForEnseignant($user->matricule);

// Récupération des catégories et des tags depuis la base de données
$categories = $cours->getCategories();
$tags = $cours->getTags();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Enseignant - Youdemy</title>
  
</head>
<body>
    <section class="container">
        <div class="login-container">
            <div class="circle circle-one"></div>
            <div class="form-container">
                <h1 class="opacity">Espace Enseignant</h1>
                <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></p>
                <p>Votre matricule: <?php echo $matricule; ?></p>
                
                <h2>Ajouter un nouveau cours</h2>
                <form action="ajouter_cours.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titre">Titre du cours:</label>
                        <input type="text" id="titre" name="titre" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="type">Type de cours:</label>
                        <select id="type" name="type" required>
                            <option value="document">Document</option>
                            <option value="video">Vidéo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="format">Format:</label>
                        <select id="format" name="format" required>
                            <option value="pdf">PDF</option>
                            <option value="mp4">MP4</option>
                            <option value="avi">AVI</option>
                            <option value="mov">MOV</option>
                        </select>
                    </div>
                    <div class="form-group" id="file_upload_group">
                        <label for="file_upload">Fichier:</label>
                        <input type="file" id="file_upload" name="file_upload" required>
                    </div>
                    <div class="form-group" id="nombre_pages_group">
                        <label for="nombre_pages">Nombre de pages:</label>
                        <input type="number" id="nombre_pages" name="nombre_pages">
                    </div>
                    <div class="form-group" id="duree_minutes_group" style="display:none;">
                        <label for="duree_minutes">Durée (en minutes):</label>
                        <input type="number" id="duree_minutes" name="duree_minutes">
                    </div>
                    <div class="form-group">
                        <label for="categorie">Catégorie:</label>
                        <select id="categorie" name="categorie" required>
                            <?php foreach ($categories as $categorie): ?>
                                <option value="<?php echo htmlspecialchars($categorie); ?>"><?php echo htmlspecialchars($categorie); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tags">Tags:</label>
                        <select id="tags" name="tags[]" multiple required>
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?php echo htmlspecialchars($tag); ?>"><?php echo htmlspecialchars($tag); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" name="matricule_enseignant" value="<?php echo $matricule; ?>">
                    <button type="submit">Ajouter le cours</button>
                </form>
                
                <h2>Mes cours</h2>
                <div class="course-list">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-item">
                            <h3><?php echo htmlspecialchars($course['titre']); ?></h3>
                            <p>Type: <?php echo htmlspecialchars($course['type']); ?></p>
                            <p>Format: <?php echo htmlspecialchars($course['format']); ?></p>
                            <?php if ($course['type'] === 'document'): ?>
                                <p>Nombre de pages: <?php echo htmlspecialchars($course['nombre_pages']); ?></p>
                            <?php else: ?>
                                <p>Durée: <?php echo htmlspecialchars($course['duree_minutes']); ?> minutes</p>
                            <?php endif; ?>
                            <p>Catégorie: <?php echo htmlspecialchars($course['categorie']); ?></p>
                            <p>Tags: <?php echo htmlspecialchars($course['tags']); ?></p>
                            <p>Fichier: <a href="<?php echo htmlspecialchars($course['file_path']); ?>" target="_blank">Voir le fichier</a></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <a href="../../models/logout.php" class="opacity">Se déconnecter</a>
            </div>
            <div class="circle circle-two"></div>
        </div>
    </section>
    <script>
        document.getElementById('type').addEventListener('change', function() {
            var nombrePagesGroup = document.getElementById('nombre_pages_group');
            var dureeMinutesGroup = document.getElementById('duree_minutes_group');
            var formatSelect = document.getElementById('format');
            if (this.value === 'document') {
                nombrePagesGroup.style.display = 'block';
                dureeMinutesGroup.style.display = 'none';
                formatSelect.innerHTML = '<option value="pdf">PDF</option>';
            } else {
                nombrePagesGroup.style.display = 'none';
                dureeMinutesGroup.style.display = 'block';
                formatSelect.innerHTML = '<option value="mp4">MP4</option><option value="avi">AVI</option><option value="mov">MOV</option>';
            }
        });
    </script>
</body>
</html>

