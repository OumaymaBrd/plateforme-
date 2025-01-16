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
    $matricule_enseignant = $_POST['matricule_enseignant'];
    $matricule_etudiant = $_POST['matricule_etudiant'];

    if ($etudiant->isCoursReserved($titre_cours, $matricule_etudiant)) {
        echo json_encode(['success' => false, 'message' => 'Vous avez déjà réservé ce cours']);
    } else {
        if ($etudiant->reserveCourse($titre_cours, $matricule_enseignant, $matricule_etudiant)) {
            echo json_encode(['success' => true, 'message' => 'Cours réservé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la réservation du cours']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

