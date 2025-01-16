<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/CoursDocument.php';
require_once '../../models/CoursVideo.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_post'] !== 'enseignant' || $_SESSION['user_status'] !== 'accepter') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $type = $_POST['type'];
    
    if ($type === 'document') {
        $cours = new CoursDocument($db);
        $cours->nombre_pages = $_POST['nombre_pages'];
    } else {
        $cours = new CoursVideo($db);
        $cours->duree_minutes = $_POST['duree_minutes'];
    }

    $cours->id = $_POST['id'];
    $cours->titre = $_POST['titre'];
    $cours->description = $_POST['description'];
    $cours->format = $_POST['format'];
    $cours->categorie = $_POST['categorie'];
    
    if (isset($_POST['tags']) && is_array($_POST['tags'])) {
        $cours->tags = implode(', ', $_POST['tags']);
    } else {
        $cours->tags = '';
    }

    if ($cours->modifierCours()) {
        echo json_encode(['success' => true, 'message' => 'Cours mis à jour avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du cours']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

