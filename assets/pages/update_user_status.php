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

    $matricule = $_POST['matricule'];
    $status = $_POST['status'];
    $userType = $_POST['userType'];

    if ($admin->updateUserStatus($matricule, $status, $userType)) {
        echo json_encode(['success' => true, 'message' => 'Statut de l\'utilisateur mis à jour avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du statut de l\'utilisateur']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

