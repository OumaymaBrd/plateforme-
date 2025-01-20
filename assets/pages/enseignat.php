<?php
// Augmenter la limite de taille de fichier à 500 Mo
ini_set('upload_max_filesize', '500M');
ini_set('post_max_size', '500M');
ini_set('memory_limit', '512M');
set_time_limit(300); // 5 minutes

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../db/Database.php';
require_once '../../models/user.php';
require_once '../../models/Cours.php';
require_once '../../models/document.php';
require_once '../../models/coursvideos.php';
require_once '../../models/Tags_courses.php';

// Vérification de l'authentification et des autorisations
if (!isset($_SESSION['user_id']) || $_SESSION['user_post'] !== 'enseignant' || $_SESSION['user_status'] !== 'accepter') {
    header("Location: login.php");
    exit();
}

$matricule = isset($_GET['matricule']) ? htmlspecialchars($_GET['matricule']) : $_SESSION['user_matricule'];

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->id = $_SESSION['user_id'];
$user->matricule = $_SESSION['user_matricule'];

// Instead of instantiating Cours directly, we'll use it for static method calls
$coursHelper = new CoursDocument($db); // Using CoursDocument as a helper, but we could use CoursVideo too
$tags_courses = new Tags_courses($db);

$courses = $coursHelper->getCoursesForEnseignant($matricule);

$categories = $coursHelper->getCategories();
$tags = $coursHelper->getTags();

// Convert categories and tags to JSON for use in JavaScript
$categoriesJson = json_encode($categories);
$tagsJson = json_encode($tags);

$message = '';

// Fetch course enrollments
$enrollments = $coursHelper->getEnrolledCourses($matricule);

// Fetch statistics
$enrolledStudentsCount = $coursHelper->getEnrolledStudentsCount($matricule);
$coursesCount = $coursHelper->getCoursesCount($matricule);

// Fonction pour gérer l'upload de fichier
function handleFileUpload($file, $allowedExtensions) {
    $uploadDir = '../../uploads/';
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        return [false, "Format de fichier non autorisé. Formats acceptés : " . implode(', ', $allowedExtensions)];
    }

    $maxFileSize = 500 * 1024 * 1024; // 500 Mo en bytes
    if ($file['size'] > $maxFileSize) {
        return [false, "Le fichier est trop volumineux. La taille maximale est de 500 Mo."];
    }

    $uniqueFilename = uniqid() . '.' . $fileExtension;
    $uploadFile = $uploadDir . $uniqueFilename;

    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
        return [true, $uploadFile];
    } else {
        return [false, "Erreur lors du téléchargement du fichier. Code d'erreur : " . $file['error']];
    }
}

// Traitement de l'ajout d'un cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_course') {
    $format = $_POST['format'];
    $newCours = ($format === 'pdf' || $format === 'txt') ? new CoursDocument($db) : new CoursVideo($db);

    $newCours->titre = $_POST['titre'];
    $newCours->description = $_POST['description'];
    $newCours->format = $format;
    $newCours->categorie = $_POST['categorie'];
    $newCours->matricule_enseignant = $matricule;

    if ($format === 'pdf' || $format === 'txt') {
        $newCours->nombre_pages = $_POST['nombre_pages'];
        $allowedExtensions = ['pdf', 'txt'];
    } else {
        $newCours->duree_minutes = $_POST['duree_minutes'];
        $allowedExtensions = ['mp4'];
    }

    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
        list($uploadSuccess, $uploadResult) = handleFileUpload($_FILES['file_upload'], $allowedExtensions);
        if ($uploadSuccess) {
            $newCours->file_path = $uploadResult;
        } else {
            $message = $uploadResult;
        }
    } else {
        $message = "Aucun fichier n'a été uploadé ou une erreur s'est produite. Code d'erreur : " . $_FILES['file_upload']['error'];
    }

    if (empty($message)) {
        if ($newCours->ajouterCours()) {
            $coursId = $newCours->id;
            $tags_courses = new Tags_courses($db);
            foreach ($_POST['tags'] as $tagId) {
                $tags_courses->addTagToCourse($coursId, $tagId);
            }
            $message = "Cours ajouté avec succès";
            $courses = $coursHelper->getCoursesForEnseignant($matricule); // Refresh the course list
        } else {
            $message = "Erreur lors de l'ajout du cours";
        }
    }

    // Répondre avec un JSON pour les requêtes AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        try {
            // Assurez-vous que toute sortie précédente est effacée
            ob_clean();
            
            echo json_encode(['success' => ($message === "Cours ajouté avec succès"), 'message' => $message]);
        } catch (Exception $e) {
            error_log("Erreur lors de la génération de la réponse JSON: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => "Une erreur est survenue lors du traitement de la requête."]);
        }
        exit;
    }
}

// Traitement de la modification d'un cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_course') {
    $format = $_POST['format'];
    $editCours = ($format === 'pdf' || $format === 'txt') ? new CoursDocument($db) : new CoursVideo($db);

    $editCours->id = $_POST['id'];
    $editCours->titre = $_POST['titre'];
    $editCours->description = $_POST['description'];
    $editCours->format = $format;
    $editCours->categorie = $_POST['categorie'];
    $editCours->tags = implode(', ', $_POST['tags']);

    // Récupérer les informations existantes du cours
    $existingCours = ($format === 'pdf' || $format === 'txt') ? new CoursDocument($db) : new CoursVideo($db);
    $existingCours->id = $_POST['id'];
    $existingCours->getCoursById();

    if ($format === 'pdf' || $format === 'txt') {
        $editCours->nombre_pages = $_POST['nombre_pages'];
        $allowedExtensions = ['pdf', 'txt'];
    } else {
        $editCours->duree_minutes = $_POST['duree_minutes'];
        $allowedExtensions = ['mp4'];
    }

    // Gestion du fichier
    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
        list($uploadSuccess, $uploadResult) = handleFileUpload($_FILES['file_upload'], $allowedExtensions);
        if ($uploadSuccess) {
            $editCours->file_path = $uploadResult;
            // Supprimer l'ancien fichier si un nouveau est uploadé
            if (file_exists($existingCours->file_path)) {
                unlink($existingCours->file_path);
            }
        } else {
            $message = $uploadResult;
        }
    } else {
        // Conserver l'ancien chemin de fichier si aucun nouveau fichier n'est uploadé
        $editCours->file_path = $existingCours->file_path;
    }

    $editCours->matricule_enseignant = $existingCours->matricule_enseignant;

    if (empty($message)) {
        if ($editCours->modifierCours()) {
            $tags_courses = new Tags_courses($db);
            $tags_courses->removeTagsFromCourse($editCours->id);
            foreach ($_POST['tags'] as $tagId) {
                $tags_courses->addTagToCourse($editCours->id, $tagId);
            }
            $message = "Cours modifié avec succès";
            $courses = $coursHelper->getCoursesForEnseignant($matricule); // Refresh the course list
        } else {
            $message = "Erreur lors de la modification du cours";
        }
    }

    // Répondre avec un JSON pour les requêtes AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        try {
            // Assurez-vous que toute sortie précédente est effacée
            ob_clean();
            
            echo json_encode(['success' => ($message === "Cours modifié avec succès"), 'message' => $message]);
        } catch (Exception $e) {
            error_log("Erreur lors de la génération de la réponse JSON: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => "Une erreur est survenue lors du traitement de la requête."]);
        }
        exit;
    }
}

// Traitement de la suppression d'un cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_course') {
    $deleteCours = new CoursDocument($db); // We can use either CoursDocument or CoursVideo here
    $deleteCours->id = $_POST['id'];

    if ($deleteCours->supprimerCours()) {
        $message = "Cours supprimé avec succès";
        $courses = $coursHelper->getCoursesForEnseignant($matricule); // Refresh the course list
    } else {
        $message = "Erreur lors de la suppression du cours";
    }

    // Répondre avec un JSON pour les requêtes AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        try {
            // Assurez-vous que toute sortie précédente est effacée
            ob_clean();
            
            echo json_encode(['success' => ($message === "Cours supprimé avec succès"), 'message' => $message]);
        } catch (Exception $e) {
            error_log("Erreur lors de la génération de la réponse JSON: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => "Une erreur est survenue lors du traitement de la requête."]);
        }
        exit;
    }
}

// Traitement de la suppression d'une inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_enrollment') {
    $matricule_etudiant = $_POST['matricule_etudiant'];
    $titre_cours = $_POST['titre_cours'];

    if ($coursHelper->supprimerInscription($matricule_etudiant, $titre_cours)) {
        $message = "Inscription supprimée avec succès";
        $enrollments = $coursHelper->getEnrolledCourses($matricule); // Refresh the enrollments list
    } else {
        $message = "Erreur lors de la suppression de l'inscription";
    }

    // Répondre avec un JSON pour les requêtes AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        try {
            ob_clean();
            echo json_encode(['success' => ($message === "Inscription supprimée avec succès"), 'message' => $message]);
        } catch (Exception $e) {
            error_log("Erreur lors de la génération de la réponse JSON: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => "Une erreur est survenue lors du traitement de la requête."]);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Enseignant - Youdemy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="../style/style_enseignat.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Dashboard Enseignant</h2>
            <div class="menu-item active" onclick="toggleSubMenu('course-management')">Gestion des cours</div>
            <div class="sub-menu" id="course-management-sub">
                <div class="menu-item" onclick="openTab('course-list')">Liste des cours</div>
                <div class="menu-item" onclick="openTab('enrollments')">Liste des inscriptions</div>
            </div>
            <div class="menu-item" onclick="openTab('add-course')">Ajout de cours</div>
            <div class="menu-item" onclick="location.href='../../models/logout.php'">Se déconnecter</div>
        </div>
        <div class="main-content">
            <div class="card">
                <h1>Bienvenue, <?php echo isset($_SESSION['user_prenom']) && isset($_SESSION['user_nom']) ? htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) : ''; ?></h1>
                <p>Matricule: <?php echo isset($matricule) ? $matricule : ''; ?></p>
            </div>

            <div id="message"></div>

            <div class="statistics">
                <div class="statistic-item">
                    <div class="statistic-value"><?php echo isset($enrolledStudentsCount) ? $enrolledStudentsCount : ''; ?></div>
                    <div>Étudiants inscrits</div>
                </div>
                <div class="statistic-item">
                    <div class="statistic-value"><?php echo isset($coursesCount) ? $coursesCount : ''; ?></div>
                    <div>Cours créés</div>
                </div>
            </div>

            <div id="course-list" class="tab-content active">
                <div class="card">
                    <h2>Liste des cours</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Format</th>
                                <th>Catégorie</th>
                                <th>Tags</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?php echo isset($course['titre']) ? htmlspecialchars($course['titre']) : ''; ?></td>
                                    <td><?php echo isset($course['format']) ? htmlspecialchars($course['format']) : ''; ?></td>
                                    <td><?php echo isset($course['categorie']) ? htmlspecialchars($course['categorie']) : ''; ?></td>
                                    <td><?php echo isset($course['tags']) ? htmlspecialchars($course['tags']) : ''; ?></td>
                                    <td>
                                        <button class="btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($course)); ?>)">Modifier</button>
                                        <button class="btn" onclick="deleteCourse(<?php echo $course['id']; ?>)">Supprimer</button>
                                        <a href="<?php echo isset($course['file_path']) ? htmlspecialchars($course['file_path']) : ''; ?>" target="_blank" class="btn">Afficher</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="enrollments" class="tab-content">
                <div class="card">
                    <h2>Liste des inscriptions</h2>
                    <?php if (empty($enrollments)): ?>
                        <p>Aucune inscription aux cours pour le moment.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Titre du cours</th>
                                    <th>Matricule étudiant</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments as $enrollment): ?>
                                    <tr>
                                        <td><?php echo isset($enrollment['nom']) ? htmlspecialchars($enrollment['nom']) : ''; ?></td>
                                        <td><?php echo isset($enrollment['prenom']) ? htmlspecialchars($enrollment['prenom']) : ''; ?></td>
                                        <td><?php echo isset($enrollment['titre_cours']) ? htmlspecialchars($enrollment['titre_cours']) : ''; ?></td>
                                        <td><?php echo isset($enrollment['matricule_etudiant']) ? htmlspecialchars($enrollment['matricule_etudiant']) : ''; ?></td>
                                        <td>
                                            <button class="btn" onclick="deleteEnrollment('<?php echo isset($enrollment['matricule_etudiant']) ? htmlspecialchars($enrollment['matricule_etudiant']) : ''; ?>', '<?php echo isset($enrollment['titre_cours']) ? htmlspecialchars($enrollment['titre_cours']) : ''; ?>')">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div id="add-course" class="tab-content">
                <div class="card">
                    <h2>Ajouter un nouveau cours</h2>
                    <form id="addCourseForm" action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_course">
                        <div class="form-group">
                            <label for="titre">Titre du cours:</label>
                            <input type="text" id="titre" name="titre" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea id="description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="format">Format:</label>
                            <select id="format" name="format" required onchange="toggleFormatFields()">
                                <option value="pdf">PDF</option>
                                <option value="txt">TXT</option>
                                <option value="mp4">MP4</option>
                            </select>
                        </div>
                        <div class="form-group" id="file_upload_group">
                            <label for="file_upload">Fichier:</label>
                            <input type="file" id="file_upload" name="file_upload" required>
                        </div>
                        <div class="form-group format-specific" id="nombre_pages_group">
                            <label for="nombre_pages">Nombre de pages:</label>
                            <input type="number" id="nombre_pages" name="nombre_pages">
                        </div>
                        <div class="form-group format-specific" id="duree_minutes_group" style="display:none;">
                            <label for="duree_minutes">Durée (en minutes):</label>
                            <input type="number" id="duree_minutes" name="duree_minutes">
                        </div>
                        <div class="form-group">
                            <label for="categorie">Catégorie:</label>
                            <select id="categorie" name="categorie" required>
                                <?php foreach ($categories as $categorie): ?>
                                    <option value="<?php echo isset($categorie) ? htmlspecialchars($categorie) : ''; ?>"><?php echo isset($categorie) ? htmlspecialchars($categorie) : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tags">Tags:</label>
                            <select id="tags" name="tags[]" multiple required>
                                <?php foreach ($tags as $tag): ?>
                                    <option value="<?php echo $tag['id']; ?>"><?php echo htmlspecialchars($tag['nom_tag']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn">Ajouter le cours</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour modifier un cours -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Modifier le cours</h2>
            <form id="editCourseForm" action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_course">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_titre">Titre du cours:</label>
                    <input type="text" id="edit_titre" name="titre" required>
                </div>
                <div class="form-group">
                    <label for="edit_description">Description:</label>
                    <textarea id="edit_description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_format">Format:</label>
                    <select id="edit_format" name="format" required onchange="toggleEditFormatFields()">
                        <option value="pdf">PDF</option>
                        <option value="txt">TXT</option>
                        <option value="mp4">MP4</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_file_upload">Nouveau fichier (optionnel):</label>
                    <input type="file" id="edit_file_upload" name="file_upload">
                </div>
                <div class="form-group format-specific" id="edit_nombre_pages_group">
                    <label for="edit_nombre_pages">Nombre de pages:</label>
                    <input type="number" id="edit_nombre_pages" name="nombre_pages">
                </div>
                <div class="form-group format-specific" id="edit_duree_minutes_group" style="display:none;">
                    <label for="edit_duree_minutes">Durée (en minutes):</label>
                    <input type="number" id="edit_duree_minutes" name="duree_minutes">
                </div>
                <div class="form-group">
                    <label for="edit_categorie">Catégorie:</label>
                    <select id="edit_categorie" name="categorie" required>
                        <?php foreach ($categories as $categorie): ?>
                            <option value="<?php echo isset($categorie) ? htmlspecialchars($categorie) : ''; ?>"><?php echo isset($categorie) ? htmlspecialchars($categorie) : ''; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_tags">Tags:</label>
                    <select id="edit_tags" name="tags[]" multiple required>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?php echo $tag['id']; ?>"><?php echo htmlspecialchars($tag['nom_tag']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Sauvegarder les modifications</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Parse the PHP variables
        var categories = <?php echo $categoriesJson; ?>;
        var tags = <?php echo $tagsJson; ?>;

        $(document).ready(function() {
            toggleFormatFields();
            toggleEditFormatFields();

            $('#addCourseForm, #editCourseForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
    
                // Vérifier la taille du fichier
                var fileInput = $(this).find('input[type="file"]')[0];
                if (fileInput && fileInput.files[0]) {
                    var fileSize = fileInput.files[0].size; // en bytes
                    var maxSize = 500 * 1024 * 1024; // 500 Mo en bytes
                    if (fileSize > maxSize) {
                        showMessage("Le fichier est trop volumineux. La taille maximale est de 500 Mo.", false);
                        return;
                    }
                }

                $.ajax({
                    url: '',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        try {
                            var jsonResponse = JSON.parse(response);
                            showMessage(jsonResponse.message, jsonResponse.success);
                            if (jsonResponse.success) {
                                location.reload();
                            }
                        } catch (e) {
                            console.error("Erreur lors du parsing de la réponse JSON:", e);
                            showMessage("Erreur lors du traitement de la réponse du serveur. Veuillez réessayer.", false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Erreur AJAX:", status, error);
                        if (xhr.status === 413) {
                            showMessage("Le fichier est trop volumineux. La taille maximale est de 500 Mo.", false);
                        } else {
                            showMessage("Une erreur s'est produite lors de la communication avec le serveur. Veuillez réessayer.", false);
                        }
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });
        });

        function openTab(tabName) {
            $('.tab-content').removeClass('active');
            $('#' + tabName).addClass('active');
        }

        function toggleSubMenu(menuId) {
            $('#' + menuId + '-sub').toggleClass('active');
        }

        function toggleFormatFields() {
            var format = $('#format').val();
            $('.format-specific').hide();
            if (format === 'pdf' || format === 'txt') {
                $('#nombre_pages_group').show();
            } else if (format === 'mp4') {
                $('#duree_minutes_group').show();
            }
        }

        function toggleEditFormatFields() {
            var format = $('#edit_format').val();
            $('.format-specific').hide();
            if (format === 'pdf' || format === 'txt') {
                $('#edit_nombre_pages_group').show();
            } else if (format === 'mp4') {
                $('#edit_duree_minutes_group').show();
            }
        }

        function openEditModal(course) {
            $('#edit_id').val(course.id);
            $('#edit_titre').val(course.titre);
            $('#edit_description').val(course.description);
            $('#edit_format').val(course.format).change();
            $('#edit_categorie').val(course.categorie);
            
            if (course.format === 'pdf' || course.format === 'txt') {
                $('#edit_nombre_pages').val(course.nombre_pages);
            } else {
                $('#edit_duree_minutes').val(course.duree_minutes);
            }

            // Update this part to handle tag IDs
            var courseTags = course.tags ? course.tags.split(',') : [];
            $('#edit_tags').val(courseTags);
            
            $('#editModal').show();
            toggleEditFormatFields();
        }

        function deleteCourse(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce cours ?')) {
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: { action: 'delete_course', id: id },
                    success: function(response) {
                        try {
                            var jsonResponse = JSON.parse(response);
                            showMessage(jsonResponse.message, jsonResponse.success);
                            if (jsonResponse.success) {
                                location.reload();
                            }
                        } catch (e) {
                            showMessage("Erreur lors du traitement de la réponse du serveur.", false);
                        }
                    },
                    error: function(xhr, status, error) {
                        showMessage("Une erreur s'est produite lors de la suppression: " + error, false);
                    }
                });
            }
        }

        function deleteEnrollment(matricule_etudiant, titre_cours) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette inscription ?')) {
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: { 
                        action: 'delete_enrollment', 
                        matricule_etudiant: matricule_etudiant,
                        titre_cours: titre_cours
                    },
                    success: function(response) {
                        try {
                            var jsonResponse = JSON.parse(response);
                            showMessage(jsonResponse.message, jsonResponse.success);
                            if (jsonResponse.success) {
                                location.reload();
                            }
                        } catch (e) {
                            showMessage("Erreur lors du traitement de la réponse du serveur.", false);
                        }
                    },
                    error: function(xhr, status, error) {
                        showMessage("Une erreur s'est produite lors de la suppression de l'inscription: " + error, false);
                    }
                });
            }
        }

        function showMessage(message, isSuccess) {
            var messageElement = $('#message');
            messageElement.text(message);
            messageElement.removeClass('success error');
            messageElement.addClass(isSuccess ? 'success' : 'error');
            messageElement.show();
            setTimeout(function() {
                messageElement.fadeOut();
            }, 5000);
        }

        $('.close').click(function() {
            $('#editModal').hide();
        });

        $(window).click(function(event) {
            if (event.target == document.getElementById('editModal')) {
                $('#editModal').hide();
            }
        });

        <?php if (!empty($message)): ?>
        showMessage("<?php echo addslashes($message); ?>", <?php echo $message === "Cours ajouté avec succès" || $message === "Cours modifié avec succès" || $message === "Cours supprimé avec succès" || $message === "Inscription supprimée avec succès" ? 'true' : 'false'; ?>);
        <?php endif; ?>
    </script>
</body>
</html>

