<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/document.php';
require_once '../../models/coursvideos.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_post'] !== 'enseignant' || $_SESSION['user_status'] !== 'accepter') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();

        $type = $_POST['type'];
        
        if ($type === 'document') {
            $cours = new CoursDocument($db);
        } else {
            $cours = new CoursVideo($db);
        }

        // Fetch the existing course data
        if (!$cours->getCoursByTitre($_POST['titre'])) {
            throw new Exception('Cours non trouvé');
        }

        // Update only the fields that are provided
        if (isset($_POST['description'])) {
            $cours->setDescription($_POST['description']);
        }
        if (isset($_POST['format'])) {
            $cours->setFormat($_POST['format']);
        }
        if (isset($_POST['categorie'])) {
            $cours->setCategorie($_POST['categorie']);
        }
        
        if (isset($_POST['tags']) && is_array($_POST['tags'])) {
            $cours->setTags(implode(', ', $_POST['tags']));
        }

        if ($type === 'document' && isset($_POST['nombre_pages'])) {
            $cours->setNombrePages($_POST['nombre_pages']);
        } elseif ($type === 'video' && isset($_POST['duree_minutes'])) {
            $cours->setDureeMinutes($_POST['duree_minutes']);
        }

        // Handle file upload if a new file is provided
        if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] == 0) {
            $allowed_extensions = ['pdf', 'mp4', 'avi', 'mov'];
            $file_extension = strtolower(pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION));

            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception("Format de fichier non autorisé.");
            }

            $upload_dir = '../../uploads/';
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    throw new Exception("Impossible de créer le répertoire d'upload.");
                }
            }

            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;

            if (!move_uploaded_file($_FILES['file_upload']['tmp_name'], $file_path)) {
                throw new Exception("Erreur lors de l'upload du fichier.");
            }

            // Delete the old file if it exists
            $old_file_path = $cours->getFilePath();
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
            $cours->setFilePath($file_path);
        }

        if (!$cours->modifierCours()) {
            throw new Exception('Erreur lors de la modification du cours');
        }

        echo json_encode(['success' => true, 'message' => 'Cours modifié avec succès']);
    } catch (Exception $e) {
        error_log("Error in modifier_cours.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Une erreur est survenue: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

