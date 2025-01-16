<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/Admin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_post'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $admin = new Admin($db);

    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $nom = $_POST['nom_tag'];

    if ($id) {
        if ($admin->modifierTag($id, $nom)) {
            echo json_encode(['success' => true, 'message' => 'Tag modifié avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification du tag']);
        }
    } else {
        if ($admin->ajouterTag($nom)) {
            echo json_encode(['success' => true, 'message' => 'Tag ajouté avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout du tag']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

