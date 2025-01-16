<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/user.php';
require_once '../../models/document.php';
require_once '../../models/coursvideos.php';

// Vérification de l'authentification et des autorisations
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

$coursDocument = new CoursDocument($db);
$coursVideo = new CoursVideo($db);

$coursesDocument = $coursDocument->getCoursesForEnseignant($matricule);
$coursesVideo = $coursVideo->getCoursesForEnseignant($matricule);
$courses = array_merge($coursesDocument, $coursesVideo);

$categories = $coursDocument->getCategories();
$tags = $coursDocument->getTags();

$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';

// Fetch course enrollments
$enrollments = $coursDocument->getEnrolledCourses($matricule);

// Fetch statistics
$enrolledStudentsCount = $coursDocument->getEnrolledStudentsCount($matricule);
$coursesCount = $coursDocument->getCoursesCount($matricule);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Enseignant - Youdemy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a1a2e;
            --secondary-color: #16213e;
            --accent-color: #0f3460;
            --text-color: #e94560;
            --light-color: #f1f1f1;
            --dark-color: #121212;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
            line-height: 1.6;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: var(--light-color);
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
        }
        h1, h2, h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        .sidebar h2 {
            color: var(--light-color);
            margin-bottom: 20px;
        }
        .menu-item {
            padding: 10px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .menu-item:hover {
            background-color: var(--secondary-color);
        }
        .menu-item.active {
            background-color: var(--accent-color);
        }
        .sub-menu {
            padding-left: 20px;
            display: none;
        }
        .sub-menu.active {
            display: block;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .statistics {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .statistic-item {
            flex: 1;
            text-align: center;
            padding: 20px;
            background-color: var(--primary-color);
            color: var(--light-color);
            border-radius: 8px;
            margin: 0 10px;
        }
        .statistic-value {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: var(--primary-color);
            color: var(--light-color);
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--accent-color);
            color: var(--light-color);
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: var(--secondary-color);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: var(--primary-color);
        }
        input[type="text"], input[type="number"], textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        #message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            display: none;
            text-align: center;
        }
        #message.success {
            background-color: #4CAF50;
        }
        #message.error {
            background-color: #f44336;
        }
    </style>
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
                <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></h1>
                <p>Matricule: <?php echo $matricule; ?></p>
            </div>

            <div id="message"></div>

            <div class="statistics">
                <div class="statistic-item">
                    <div class="statistic-value"><?php echo $enrolledStudentsCount; ?></div>
                    <div>Étudiants inscrits</div>
                </div>
                <div class="statistic-item">
                    <div class="statistic-value"><?php echo $coursesCount; ?></div>
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
                                <th>Type</th>
                                <th>Format</th>
                                <th>Catégorie</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['titre']); ?></td>
                                    <td><?php echo htmlspecialchars($course['type']); ?></td>
                                    <td><?php echo htmlspecialchars($course['format']); ?></td>
                                    <td><?php echo htmlspecialchars($course['categorie']); ?></td>
                                    <td>
                                        <button class="btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($course)); ?>)">Modifier</button>
                                        <button class="btn" onclick="deleteCourse(<?php echo $course['id']; ?>, '<?php echo $course['type']; ?>')">Supprimer</button>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments as $enrollment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($enrollment['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['titre_cours']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['matricule_etudiant']); ?></td>
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
                    <form id="addCourseForm" action="ajouter_cours.php" method="POST" enctype="multipart/form-data">
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
            <form id="editCourseForm" enctype="multipart/form-data">
                <input type="hidden" id="edit_id" name="id">
                <input type="hidden" id="edit_type" name="type">
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
                    <select id="edit_format" name="format" required>
                        <option value="pdf">PDF</option>
                        <option value="mp4">MP4</option>
                        <option value="avi">AVI</option>
                        <option value="mov">MOV</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_file_upload">Nouveau fichier (optionnel):</label>
                    <input type="file" id="edit_file_upload" name="file_upload">
                </div>
                <div class="form-group" id="edit_nombre_pages_group">
                    <label for="edit_nombre_pages">Nombre de pages:</label>
                    <input type="number" id="edit_nombre_pages" name="nombre_pages">
                </div>
                <div class="form-group" id="edit_duree_minutes_group">
                    <label for="edit_duree_minutes">Durée (en minutes):</label>
                    <input type="number" id="edit_duree_minutes" name="duree_minutes">
                </div>
                <div class="form-group">
                    <label for="edit_categorie">Catégorie:</label>
                    <select id="edit_categorie" name="categorie" required>
                        <?php foreach ($categories as $categorie): ?>
                            <option value="<?php echo htmlspecialchars($categorie); ?>"><?php echo htmlspecialchars($categorie); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_tags">Tags:</label>
                    <select id="edit_tags" name="tags[]" multiple required>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?php echo htmlspecialchars($tag); ?>"><?php echo htmlspecialchars($tag); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Sauvegarder les modifications</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#type').change(function() {
                var nombrePagesGroup = $('#nombre_pages_group');
                var dureeMinutesGroup = $('#duree_minutes_group');
                var formatSelect = $('#format');
                if (this.value === 'document') {
                    nombrePagesGroup.show();
                    dureeMinutesGroup.hide();
                    formatSelect.html('<option value="pdf">PDF</option>');
                } else {
                    nombrePagesGroup.hide();
                    dureeMinutesGroup.show();
                    formatSelect.html('<option value="mp4">MP4</option><option value="avi">AVI</option><option value="mov">MOV</option>');
                }
            });

            $('#addCourseForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: 'ajouter_cours.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        var result = JSON.parse(response);
                        showMessage(result.message, result.success);
                        if (result.success) {
                            $('#addCourseForm')[0].reset();
                            location.reload();
                        }
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $('#editCourseForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: 'modifier_cours.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        var result = JSON.parse(response);
                        showMessage(result.message, result.success);
                        if (result.success) {
                            $('#editModal').hide();
                            location.reload();
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

        function openEditModal(course) {
            $('#edit_id').val(course.id);
            $('#edit_type').val(course.type);
            $('#edit_titre').val(course.titre);
            $('#edit_description').val(course.description);
            $('#edit_format').val(course.format);
            $('#edit_categorie').val(course.categorie);
            
            if (course.type === 'document') {
                $('#edit_nombre_pages_group').show();
                $('#edit_duree_minutes_group').hide();
                $('#edit_nombre_pages').val(course.nombre_pages);
            } else {
                $('#edit_nombre_pages_group').hide();
                $('#edit_duree_minutes_group').show();
                $('#edit_duree_minutes').val(course.duree_minutes);
            }

            $('#edit_tags').val(course.tags ? course.tags.split(', ') : []);
            
            $('#editModal').show();
        }

        function deleteCourse(id, type) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce cours ?')) {
                $.ajax({
                    url: 'supprimer_cours.php',
                    type: 'POST',
                    data: { id: id, type: type },
                    success: function(response) {
                        var result = JSON.parse(response);
                        showMessage(result.message, result.success);
                        if (result.success) {
                            location.reload();
                        }
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
            }, 120000); // 2 minutes
        }

        $('.close').click(function() {
            $('#editModal').hide();
        });

        $(window).click(function(event) {
            if (event.target == document.getElementById('editModal')) {
                $('#editModal').hide();
            }
        });
    </script>
</body>
</html>

