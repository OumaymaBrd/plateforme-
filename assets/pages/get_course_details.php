<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/User.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();

    $user = new User($db);

    if (isset($_GET['id'])) {
        $courseId = intval($_GET['id']);
        $courseDetails = $user->getCourseDetails($courseId);
        
        if ($courseDetails) {
            echo json_encode($courseDetails);
        } else {
            echo json_encode(['error' => 'Cours non trouvé']);
        }
    } else {
        echo json_encode(['error' => 'ID du cours non spécifié']);
    }
} catch (Exception $e) {
    error_log("Erreur dans get_course_details.php: " . $e->getMessage());
    echo json_encode(['error' => 'Une erreur interne est survenue']);
}

