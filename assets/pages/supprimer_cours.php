<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/document.php';
require_once '../../models/coursvideos.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_post'] !== 'enseignant' || $_SESSION['user_status'] !== 'accepter') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['type'])) {
    $database = new Database();
    $db = $database->getConnection();

    $type = $_POST['type'];
    
    if ($type === 'document') {
        $cours = new CoursDocument($db);
    } else {
        $cours = new CoursVideo($db);
    }

    $cours->id = $_POST['id'];

    if ($cours->supprimerCours()) {
        echo json_encode(['success' => true, 'message' => 'Cours supprimé avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du cours']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID du cours non spécifié, type non spécifié ou méthode non autorisée']);
}

