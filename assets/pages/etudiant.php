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
    <link href="../style/style_etudiant.css" rel="stylesheet">
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
                                <p><i class="fas fa-tags"></i> 
                                    <?php 
                                    if (!empty($course['tags'])) {
                                        echo implode(', ', array_map('htmlspecialchars', $course['tags']));
                                    } else {
                                        echo 'Aucun tag';
                                    }
                                    ?>
                                </p>
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
                                <p><i class="fas fa-tags"></i> 
                                    <?php 
                                    if (!empty($course['tags'])) {
                                        echo implode(', ', array_map('htmlspecialchars', $course['tags']));
                                    } else {
                                        echo 'Aucun tag';
                                    }
                                    ?>
                                </p>
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
            $('#modalTags').text('Tags: ' + (course.tags && course.tags.length > 0 ? course.tags.join(', ') : 'Aucun tag'));
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
            
            // Ajouter un bouton de fermeture
            var closeButton = $('<button>').text('X').addClass('close-message');
            messageElement.append(closeButton);
            
            // Gérer la fermeture du message
            closeButton.on('click', function() {
                messageElement.hide();
            });
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
            }
        });
    </script>
</body>
</html>

