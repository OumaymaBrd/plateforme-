<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/Etudiant.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_post'] !== 'etudiant') {
    header("Location: login.php");
    exit();
}

$matricule = isset($_SESSION['user_matricule']) ? $_SESSION['user_matricule'] : 'N/A';

$database = new Database();
$db = $database->getConnection();

$etudiant = new Etudiant($db);
$categories = $etudiant->getCategories();
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 4;

$courses = $etudiant->getCourses($selectedCategory, $currentPage, $itemsPerPage, $searchTerm);
$totalCourses = $etudiant->getTotalCourses($selectedCategory, $searchTerm);

$totalPages = ceil($totalCourses / $itemsPerPage);
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
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        .sidebar {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
        }
        .sidebar h2 {
            margin-bottom: 30px;
        }
        .menu-item {
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .menu-item:hover, .menu-item.active {
            background-color: #34495e;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .course-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .course-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px;
        }
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .search-container {
            margin-bottom: 20px;
        }
        .search-container input, .search-container select {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
            margin-bottom: 10px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .pagination a {
            color: #3498db;
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color 0.3s;
            border: 1px solid #ddd;
            margin: 0 4px 8px 4px;
        }
        .pagination a.active {
            background-color: #3498db;
            color: white;
            border: 1px solid #3498db;
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
            max-width: 500px;
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
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        #message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }
        .success {
            background-color: #2ecc71;
            color: white;
        }
        .error {
            background-color: #e74c3c;
            color: white;
        }
        @media (min-width: 768px) {
            .container {
                flex-direction: row;
            }
            .sidebar {
                width: 250px;
            }
            .search-container input, .search-container select {
                width: auto;
                margin-bottom: 0;
            }
        }
        @media (max-width: 767px) {
            .sidebar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px;
            }
            .sidebar h2 {
                margin-bottom: 0;
            }
            .menu-items {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: #2c3e50;
                z-index: 1000;
            }
            .menu-items.show {
                display: block;
            }
            .menu-toggle {
                display: block;
                font-size: 24px;
                cursor: pointer;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Youdemy</h2>
            <div class="menu-toggle">&#9776;</div>
            <div class="menu-items">
                <div class="menu-item active" onclick="openTab('courses')">Cours disponibles</div>
                <div class="menu-item" onclick="openTab('reserved-courses')">Cours réservés</div>
                <div class="menu-item" onclick="location.href='../../models/logout.php'">Se déconnecter</div>
            </div>
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
                        <form action="" method="GET" id="searchForm">
                            <input type="text" id="searchInput" name="search" placeholder="Rechercher un cours..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                            <select id="categoryFilter" name="category">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $selectedCategory === $category ? 'selected' : ''; ?>><?php echo htmlspecialchars($category); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn">Rechercher</button>
                        </form>
                    </div>
                    <div id="course-list" class="course-list">
                        <?php if (empty($courses)): ?>
                            <p id="no-results" style="display: none;">Aucun résultat trouvé pour cette catégorie.</p>
                        <?php endif; ?>
                        <?php foreach ($courses as $course): ?>
                            <div class="course-card">
                                <h3><?php echo htmlspecialchars($course['titre']); ?></h3>
                                <p><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($course['date_creation']); ?></p>
                                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['prenom'] . ' ' . $course['nom']); ?></p>
                                <p><i class="fas fa-folder"></i> <?php echo htmlspecialchars($course['categorie']); ?></p>
                                <button class="btn" onclick="showDetails(<?php echo htmlspecialchars(json_encode($course)); ?>)">Détails</button>
                                <?php if ($etudiant->isCoursReserved($course['titre'], $matricule)): ?>
                                    <button class="btn btn-disabled" disabled>Déjà réservé</button>
                                <?php else: ?>
                                    <button class="btn" onclick="reserveCourse('<?php echo $course['titre']; ?>', '<?php echo $course['matricule_enseignant']; ?>', '<?php echo $matricule; ?>')">Réserver</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>&category=<?php echo urlencode($selectedCategory); ?>" <?php echo $currentPage == $i ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div id="reserved-courses" class="tab-content" style="display:none;">
                <div class="card">
                    <h2>Vos cours réservés</h2>
                    <div class="reserved-courses course-list">
                        <?php foreach ($reservedCourses as $course): ?>
                            <div class="course-card">
                                <h3><?php echo htmlspecialchars($course['titre_cours']); ?></h3>
                                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['prenom'] . ' ' . $course['nom']); ?></p>
                                <p><i class="fas fa-folder"></i> <?php echo isset($course['categorie']) ? htmlspecialchars($course['categorie']) : 'Non catégorisé'; ?></p>
                                <button class="btn" onclick="showDetails(<?php echo htmlspecialchars(json_encode($course)); ?>)">Détails</button>
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
            if ($(window).width() <= 767) {
                $('.menu-items').removeClass('show');
            }
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

            $('.menu-toggle').click(function() {
                $('.menu-items').toggleClass('show');
            });

            $(window).resize(function() {
                if ($(window).width() > 767) {
                    $('.menu-items').removeClass('show');
                }
            });

            $('#searchForm').submit(function(e) {
                e.preventDefault();
                var searchTerm = $('#searchInput').val();
                var category = $('#categoryFilter').val();
                window.location.href = '?search=' + encodeURIComponent(searchTerm) + '&category=' + encodeURIComponent(category);
            });

            if ($('.course-card').length === 0) {
                $('#no-results').show();
                setTimeout(function() {
                    $('#no-results').hide();
                }, 3000);
            }
        });
    </script>
    <!--  -->
</body>
</html>

