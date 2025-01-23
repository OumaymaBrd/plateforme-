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
    $nom_tags = $_POST['nom_tag'];

    if ($id) {
 
        if ($admin->modifierTag($id, $nom_tags)) {
            echo json_encode(['success' => true, 'message' => 'Tag modifié avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification du tag']);
        }
    } else {
    
        $tags = array_map('trim', explode(',', $nom_tags));
        $success = true;
        $added_count = 0;

        foreach ($tags as $tag) {
            if (!empty($tag)) {
                if ($admin->ajouterTag($tag)) {
                    $added_count++;
                } else {
                    $success = false;
                    break;
                }
            }
        }

        if ($success) {
            echo json_encode(['success' => true, 'message' => $added_count . ' tag(s) ajouté(s) avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout des tags']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

