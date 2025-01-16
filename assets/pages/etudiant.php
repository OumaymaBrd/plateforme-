<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/Etudiant.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_post'] !== 'etudiant') {
    header("Location: login.php");
    exit();
}

$matricule = isset($_GET['matricule']) ? htmlspecialchars($_GET['matricule']) : 'N/A';

$database = new Database();
$db = $database->getConnection();

$etudiant = new Etudiant($db);
$categories = $etudiant->getCategories();
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
$courses = $etudiant->getCourses($selectedCategory);
$reservedCourses = $etudiant->getCart($matricule);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Étudiant - Youdemy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
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
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .course-list, .reserved-courses {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .course-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: all 0.3s ease;
        }
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
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
        .btn-disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .btn-disabled:hover {
            background-color: #ccc;
        }
        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .search-container input[type="text"], .search-container select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex-grow: 1;
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
        #message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            display: none;
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
            <h2>Youdemy</h2>
            <div class="menu-item active" onclick="openTab('courses')">Cours disponibles</div>
            <div class="menu-item" onclick="openTab('reserved-courses')">Cours réservés</div>
            <div class="menu-item" onclick="location.href='../../models/logout.php'">Se déconnecter</div>
        </div>
        <div class="main-content">
            <div class="card">
                <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></h1>
                <p>Matricule: <?php echo $matricule; ?></p>
            </div>

            <div id="courses" class="tab-content">
                <div class="card">
                    <h2>Cours disponibles</h2>
                    <div class="search-container">
                        <input type="text" id="searchInput" placeholder="Rechercher un cours...">
                        <select id="categoryFilter">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn" onclick="searchCourses()">Rechercher</button>
                    </div>
                    <div id="course-list" class="course-list">
                        <?php foreach ($courses as $course): ?>
                            <div class="course-card">
                                <h3><?php echo htmlspecialchars($course['titre']); ?></h3>
                                <p><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($course['date_creation']); ?></p>
                                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['prenom'] . ' ' . $course['nom']); ?></p>
                                <button class="btn" onclick="showDetails(<?php echo htmlspecialchars(json_encode($course)); ?>)">Détails</button>
                                <?php if ($etudiant->isCoursReserved($course['titre'], $matricule)): ?>
                                    <button class="btn btn-disabled" disabled>Déjà réservé</button>
                                <?php else: ?>
                                    <button class="btn" onclick="reserveCourse('<?php echo $course['titre']; ?>', '<?php echo $course['matricule_enseignant']; ?>', '<?php echo $matricule; ?>')">Réserver</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div id="reserved-courses" class="tab-content" style="display:none;">
                <div class="card">
                    <h2>Vos cours réservés</h2>
                    <div class="reserved-courses">
                        <?php foreach ($reservedCourses as $course): ?>
                            <div class="course-card">
                                <h3><?php echo htmlspecialchars($course['titre_cours']); ?></h3>
                                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['prenom'] . ' ' . $course['nom']); ?></p> <br> <br>
                                <button class="btn" onclick="showDetails(<?php echo htmlspecialchars(json_encode($course)); ?>)">Détails</button> <br> <br> 
                                <button class="btn" onclick="removeReservation('<?php echo $course['titre_cours']; ?>', '<?php echo $matricule; ?>')">Annuler la réservation</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="courseModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle"></h2>
            <p id="modalDescription"></p>
            <p id="modalType"></p>
            <p id="modalFormat"></p>
            <p id="modalPages"></p>
            <p id="modalCategory"></p>
            <p id="modalTags"></p>
            <a id="modalFileLink" href="#" target="_blank" class="btn">Ouvrir le fichier/vidéo</a>
        </div>
    </div>

    <div id="message"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function openTab(tabName) {
            $('.tab-content').hide();
            $('#' + tabName).show();
            $('.menu-item').removeClass('active');
            $('.menu-item').filter(function() {
                return $(this).text().toLowerCase().includes(tabName.replace('-', ' '));
            }).addClass('active');
        }

        function showDetails(course) {
            $('#modalTitle').text(course.titre || course.titre_cours);
            $('#modalDescription').text(course.description);
            $('#modalType').text('Type: ' + course.type);
            $('#modalFormat').text('Format: ' + course.format);
            $('#modalPages').text(course.nombre_pages ? 'Nombre de pages: ' + course.nombre_pages : '');
            $('#modalCategory').text('Catégorie: ' + course.categorie);
            $('#modalTags').text('Tags: ' + course.tags);
            $('#modalFileLink').attr('href', course.file_path);
            $('#courseModal').show();
        }

        function reserveCourse(titre, matriculeEnseignant, matriculeEtudiant) {
            $.ajax({
                url: 'reserve_course.php',
                type: 'POST',
                data: {
                    titre_cours: titre,
                    matricule_enseignant: matriculeEnseignant,
                    matricule_etudiant: matriculeEtudiant
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    showMessage(result.message, result.success);
                    if (result.success) {
                        location.reload();
                    }
                }
            });
        }

        function removeReservation(titre, matriculeEtudiant) {
            $.ajax({
                url: 'remove_from_cart.php',
                type: 'POST',
                data: {
                    titre_cours: titre,
                    matricule_etudiant: matriculeEtudiant
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    showMessage(result.message, result.success);
                    if (result.success) {
                        location.reload();
                    }
                }
            });
        }

        function searchCourses() {
            var searchTerm = $('#searchInput').val();
            var category = $('#categoryFilter').val();
            $.ajax({
                url: 'search_courses.php',
                type: 'GET',
                data: { search: searchTerm, category: category },
                success: function(response) {
                    $('#course-list').html(response);
                }
            });
        }

        function showMessage(message, isSuccess) {
            var messageElement = $('#message');
            messageElement.text(message);
            messageElement.removeClass('success error');
            messageElement.addClass(isSuccess ? 'success' : 'error');
            messageElement.show();
            setTimeout(function() {
                messageElement.hide();
            }, 3000);
        }

        $(document).ready(function() {
            $('.close').click(function() {
                $('#courseModal').hide();
            });

            $(window).click(function(event) {
                if (event.target == document.getElementById('courseModal')) {
                    $('#courseModal').hide();
                }
            });

            $('#searchInput').on('keyup', function(e) {
                if (e.key === 'Enter') {
                    searchCourses();
                }
            });

            $('#categoryFilter').change(function() {
                searchCourses();
            });
        });
    </script>
</body>
</html>

