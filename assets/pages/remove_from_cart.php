<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/Etudiant.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_post'] !== 'etudiant') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $etudiant = new Etudiant($db);

    $titre_cours = $_POST['titre_cours'];
    $matricule_etudiant = $_POST['matricule_etudiant'];

    if ($etudiant->removeFromCart($titre_cours, $matricule_etudiant)) {
        echo json_encode(['success' => true, 'message' => 'Réservation annulée avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'annulation de la réservation']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

