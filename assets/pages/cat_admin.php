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
    $nom = $_POST['nom_categorie'];
    $description = $_POST['description_categorie'];

    if ($id) {
        if ($admin->modifierCategorie($id, $nom, $description)) {
            echo json_encode(['success' => true, 'message' => 'Catégorie modifiée avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification de la catégorie']);
        }
    } else {
        if ($admin->ajouterCategorie($nom, $description)) {
            echo json_encode(['success' => true, 'message' => 'Catégorie ajoutée avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de la catégorie']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

