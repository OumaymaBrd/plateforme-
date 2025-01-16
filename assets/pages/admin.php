<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/User.php';
require_once '../../models/Admin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_post'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$admin = new Admin($db);

$enseignants = $admin->getEnseignants();
$cours = $admin->getCours();
$categories = $admin->getCategories();
$tags = $admin->getTags();

// Fetch statistics
$totalCourses = $admin->getTotalCourses();
$coursesByCategory = $admin->getCoursesByCategory();
$topCourses = $admin->getTopCourses();
$topTeachers = $admin->getTopTeachers();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Youdemy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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
        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
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
        }
        #message.success {
            background-color: #4CAF50;
        }
        #message.error {
            background-color: #f44336;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .chart-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }
        .chart {
            width: 45%;
            margin-bottom: 20px;
        }

        
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Dashboard Admin</h2>
            <div class="menu-item" onclick="openTab('enseignants')">Gestion des enseignants</div>
            <div class="menu-item" onclick="toggleSubMenu('content-management')">Gestion du contenu</div>
            <div class="sub-menu" id="content-management-sub">
                <div class="menu-item" onclick="openTab('cours')">Visualiser les cours</div>
                <div class="menu-item" onclick="openTab('categories')">Gérer les catégories</div>
                <div class="menu-item" onclick="openTab('tags')">Gérer les tags</div>
            </div>
            <div class="menu-item" onclick="openTab('statistics')">Statistiques</div>
            <div class="menu-item" onclick="location.href='../../models/logout.php'">Se déconnecter</div>
        </div>
        <div class="main-content">
            <div id="message"></div>

            <div id="enseignants" class="tab-content active">
                <div class="card">
                    <h2>Gestion des enseignants</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Matricule</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enseignants as $enseignant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($enseignant['matricule']); ?></td>
                                    <td><?php echo htmlspecialchars($enseignant['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($enseignant['prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($enseignant['status']); ?></td>
                                    <td>
                                        <select onchange="updateEnseignantStatus('<?php echo $enseignant['matricule']; ?>', this.value)">
                                            <option value="en Cours" <?php echo $enseignant['status'] == 'en Cours' ? 'selected' : ''; ?>>En cours</option>
                                            <option value="accepter" <?php echo $enseignant['status'] == 'accepter' ? 'selected' : ''; ?>>Accepter</option>
                                            <option value="refuser" <?php echo $enseignant['status'] == 'refuser' ? 'selected' : ''; ?>>Refuser</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="cours" class="tab-content">
                <div class="card">
                    <h2>Visualisation des cours</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Format</th>
                                <th>Catégorie</th>
                                <th>Fichier</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cours as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['titre']); ?></td>
                                    <td><?php echo htmlspecialchars($course['description']); ?></td>
                                    <td><?php echo htmlspecialchars($course['type']); ?></td>
                                    <td><?php echo htmlspecialchars($course['format']); ?></td>
                                    <td><?php echo htmlspecialchars($course['categorie']); ?></td>
                                    <td><a href="<?php echo htmlspecialchars($course['file_path']); ?>" target="_blank">Voir le fichier</a></td>
                                    <td><button class="btn" onclick="supprimerCours(<?php echo $course['id']; ?>)">Supprimer</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="categories" class="tab-content">
                <div class="card">
                    <h2>Gestion des catégories</h2>
                    <form id="categorieForm">
                        <input type="hidden" id="categorie_id" name="id">
                        <div class="form-group">
                            <label for="nom_categorie">Nom de la catégorie:</label>
                            <input type="text" id="nom_categorie" name="nom_categorie" required>
                        </div>
                        <div class="form-group">
                            <label for="description_categorie">Description:</label>
                            <textarea id="description_categorie" name="description_categorie" required></textarea>
                        </div>
                        <button type="submit" class="btn">Ajouter/Modifier la catégorie</button>
                    </form>
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $categorie): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($categorie['nom_categorie']); ?></td>
                                    <td><?php echo htmlspecialchars($categorie['description']); ?></td>
                                    <td>
                                        <button class="btn" onclick="editCategorie(<?php echo htmlspecialchars(json_encode($categorie)); ?>)">Modifier</button>
                                        <button class="btn" onclick="supprimerCategorie(<?php echo $categorie['id']; ?>)">Supprimer</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="tags" class="tab-content">
                <div class="card">
                    <h2>Gestion des tags</h2>
                    <form id="tagForm">
                        <input type="hidden" id="tag_id" name="id">
                        <div class="form-group">
                            <label for="nom_tag">Nom du tag:</label>
                            <input type="text" id="nom_tag" name="nom_tag" required>
                        </div>
                        <button type="submit" class="btn">Ajouter/Modifier le tag</button>
                    </form>
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tags as $tag): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($tag['nom_tag']); ?></td>
                                    <td><?php echo htmlspecialchars($tag['date_creation']); ?></td>
                                    <td>
                                        <button class="btn" onclick="editTag(<?php echo htmlspecialchars(json_encode($tag)); ?>)">Modifier</button>
                                        <button class="btn" onclick="supprimerTag(<?php echo $tag['id']; ?>)">Supprimer</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="statistics" class="tab-content">
                <div class="card">
                    <h2>Statistiques</h2>
                    <div class="chart-container">
                        <div class="chart" id="coursesByCategoryChart"></div>
                        <div class="chart" id="topCoursesChart"></div>
                        <div class="chart" id="topTeachersChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function openTab(tabName) {
            $('.tab-content').removeClass('active');
            $('#' + tabName).addClass('active');
        }

        function toggleSubMenu(menuId) {
            $('#' + menuId + '-sub').toggleClass('active');
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

        function updateEnseignantStatus(matricule, status) {
            $.ajax({
                url: 'update_ens_admin.php',
                type: 'POST',
                data: { matricule: matricule, status: status },
                success: function(response) {
                    var result = JSON.parse(response);
                    showMessage(result.message, result.success);
                    if (result.success) {
                        location.reload();
                    }
                }
            });
        }

        function supprimerCours(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce cours ?')) {
                $.ajax({
                    url: 'del_cours_admin.php',
                    type: 'POST',
                    data: { id: id },
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

        $('#categorieForm').submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: 'cat_admin.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    var result = JSON.parse(response);
                    showMessage(result.message, result.success);
                    if (result.success) {
                        $('#categorieForm')[0].reset();
                        $('#categorie_id').val('');
                        location.reload();
                    }
                }
            });
        });

        function editCategorie(categorie) {
            $('#categorie_id').val(categorie.id);
            $('#nom_categorie').val(categorie.nom_categorie);
            $('#description_categorie').val(categorie.description);
        }

        function supprimerCategorie(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
                $.ajax({
                    url: 'del_cat_admin.php',
                    type: 'POST',
                    data: { id: id },
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

        $('#tagForm').submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: 'tag_admin.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    var result = JSON.parse(response);
                    showMessage(result.message, result.success);
                    if (result.success) {
                        $('#tagForm')[0].reset();
                        $('#tag_id').val('');
                        location.reload();
                    }
                }
            });
        });

        function editTag(tag) {
            $('#tag_id').val(tag.id);
            $('#nom_tag').val(tag.nom_tag);
        }

        function supprimerTag(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce tag ?')) {
                $.ajax({
                    url: 'del_tag_admin.php',
                    type: 'POST',
                    data: { id: id },
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

        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            drawCoursesByCategoryChart();
            drawTopCoursesChart();
            drawTopTeachersChart();
        }

       
        google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawCharts);

function drawCharts() {
    drawCoursesByCategoryChart();
    drawTopCoursesChart();
    drawTopTeachersChart();
}

function drawCoursesByCategoryChart() {
    var data = google.visualization.arrayToDataTable([
        ['Catégorie', 'Nombre de cours'],
        <?php
        foreach ($coursesByCategory as $category) {
            echo "['" . addslashes($category['categorie']) . "', " . $category['count'] . "],";
        }
        ?>
    ]);

    var options = {
        title: 'Répartition des cours par catégorie',
        is3D: true,
        width: '100%',
        height: 500,
        backgroundColor: 'transparent',
        colors: ['#4285F4', '#DB4437', '#F4B400', '#0F9D58', '#AB47BC', '#00ACC1', '#FF7043'],
        legend: { position: 'right', textStyle: { fontSize: 14 } },
        titleTextStyle: { fontSize: 18, bold: true },
        pieSliceTextStyle: { fontSize: 14 },
        sliceVisibilityThreshold: 0.05
    };

    var chart = new google.visualization.PieChart(document.getElementById('coursesByCategoryChart'));
    chart.draw(data, options);
}

function drawTopCoursesChart() {
    var data = google.visualization.arrayToDataTable([
        ['Cours', 'Nombre d\'étudiants'],
        <?php
        foreach ($topCourses as $course) {
            echo "['" . addslashes($course['titre_cours']) . "', " . $course['count'] . "],";
        }
        ?>
    ]);

    var options = {
        title: 'Top 3 des cours les plus populaires',
        is3D: true,
        width: '100%',
        height: 400,
        backgroundColor: 'transparent',
        colors: ['#4285F4', '#DB4437', '#F4B400'],
        legend: { position: 'right', textStyle: { fontSize: 14 } },
        titleTextStyle: { fontSize: 18, bold: true },
        pieSliceTextStyle: { fontSize: 14 }
    };

    var chart = new google.visualization.PieChart(document.getElementById('topCoursesChart'));
    chart.draw(data, options);
}

function drawTopTeachersChart() {
    var data = google.visualization.arrayToDataTable([
        ['Enseignant', 'Nombre d\'étudiants'],
        <?php
        foreach ($topTeachers as $teacher) {
            echo "['" . addslashes($teacher['nom'] . ' ' . $teacher['prenom']) . "', " . $teacher['count'] . "],";
        }
        ?>
    ]);

    var options = {
        title: 'Top 3 des enseignants',
        is3D: true,
        width: '100%',
        height: 400,
        backgroundColor: 'transparent',
        colors: ['#0F9D58', '#AB47BC', '#00ACC1'],
        legend: { position: 'right', textStyle: { fontSize: 14 } },
        titleTextStyle: { fontSize: 18, bold: true },
        pieSliceTextStyle: { fontSize: 14 }
    };

    var chart = new google.visualization.PieChart(document.getElementById('topTeachersChart'));
    chart.draw(data, options);
}
        
        function drawTopCoursesChart() {
            var data = google.visualization.arrayToDataTable([
                ['Cours', 'Nombre d\'étudiants'],
                <?php
                foreach ($topCourses as $course) {
                    echo "['" . $course['titre_cours'] . "', " . $course['count'] . "],";
                }
                ?>
            ]);

            var options = {
                title: 'Top 3 des cours les plus populaires',
                legend: { position: 'none' },
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('topCoursesChart'));
            chart.draw(data, options);
        }

        function drawTopTeachersChart() {
            var data = google.visualization.arrayToDataTable([
                ['Enseignant', 'Nombre d\'étudiants'],
                <?php
                foreach ($topTeachers as $teacher) {
                    echo "['" . $teacher['nom'] . ' ' . $teacher['prenom'] . "', " . $teacher['count'] . "],";
                }
                ?>
            ]);

            var options = {
                title: 'Top 3 des enseignants',
                legend: { position: 'none' },
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('topTeachersChart'));
            chart.draw(data, options);
        }

        
    </script>
</body>
</html>

