<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/Etudiant.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_post'] !== 'etudiant') {
    echo "Non autorisé";
    exit();
}

$matricule = isset($_SESSION['user_matricule']) ? $_SESSION['user_matricule'] : 'N/A';

$database = new Database();
$db = $database->getConnection();

$etudiant = new Etudiant($db);

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : null;
$courses = $etudiant->searchCourses($searchTerm, $category);

if (empty($courses)) {
    echo "<p>Aucun cours trouvé pour votre recherche.</p>";
} else {
    foreach ($courses as $course) {
        echo "<div class='course-card'>";
        echo "<h3>" . htmlspecialchars($course['titre']) . "</h3>";
        echo "<p><i class='fas fa-calendar-alt'></i> " . htmlspecialchars($course['date_creation']) . "</p>";
        echo "<p><i class='fas fa-user'></i> " . htmlspecialchars($course['prenom'] . ' ' . $course['nom']) . "</p>";
        echo "<button class='btn' onclick='showDetails(" . htmlspecialchars(json_encode($course)) . ")'>Détails</button>";
        if ($etudiant->isCoursReserved($course['titre'], $matricule)) {
            echo "<button class='btn btn-disabled' disabled>Déjà réservé</button>";
        } else {
            echo "<button class='btn' onclick='reserveCourse(\"" . $course['titre'] . "\", \"" . $course['matricule_enseignant'] . "\", \"" . $matricule . "\")'>Réserver</button>";
        }
        echo "</div>";
    }
}

