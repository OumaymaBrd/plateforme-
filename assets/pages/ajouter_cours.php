<?php
session_start();
require_once '../../models/Cours.php';
require_once '../../models/coursvideos.php';
require_once '../../models/document.php';
require_once '../../db/Database.php';

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

    $cours->titre = $_POST['titre'];
    $cours->description = $_POST['description'];
    $cours->format = $_POST['format'];
    $cours->categorie = $_POST['categorie'];
    $cours->matricule_enseignant = $_POST['matricule_enseignant'];

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

    // Gestion de l'upload de fichier
    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] == 0) {
        $allowed_extensions = ['pdf', 'mp4', 'avi', 'mov'];
        $file_extension = strtolower(pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_extensions)) {
            $upload_dir = '../../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $file_path)) {
                $cours->file_path = $file_path;
            } else {
                echo json_encode(['success' => false, 'message' => "Erreur lors de l'upload du fichier."]);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => "Format de fichier non autorisé."]);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Aucun fichier n'a été uploadé."]);
        exit();
    }

    if ($cours->ajouterCours()) {
        echo json_encode(['success' => true, 'message' => "Le cours a été ajouté avec succès."]);
    } else {
        echo json_encode(['success' => false, 'message' => "Une erreur est survenue lors de l'ajout du cours."]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => "Méthode non autorisée"]);

