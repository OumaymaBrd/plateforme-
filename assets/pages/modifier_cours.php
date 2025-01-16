<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/document.php';
require_once '../../models/coursvideos.php';

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
    } else {
        $cours = new CoursVideo($db);
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

    if ($type === 'document') {
        $cours->nombre_pages = $_POST['nombre_pages'];
    } else {
        $cours->duree_minutes = $_POST['duree_minutes'];
    }

    if ($cours->modifierCours()) {
        echo json_encode(['success' => true, 'message' => 'Cours modifié avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification du cours']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

