<?php
require_once 'User.php';

class Etudiant extends User {
    protected $conn;

    public function __construct($db) {
        parent::__construct($db);
        $this->conn = $db;
    }

    public function getCourses($category = null, $currentPage = 1, $itemsPerPage = 10, $searchTerm = '') {
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        $query = "SELECT c.id, c.titre, c.description, c.date_creation, c.type, c.format, 
                  c.file_path, c.nombre_pages, c.tags, c.categorie, 
                  u.nom, u.prenom, c.matricule_enseignant
                  FROM cours c
                  JOIN user_ u ON u.matricule = c.matricule_enseignant
                  WHERE c.supprime = 0";
        
        $params = array();
        
        if ($category) {
            $query .= " AND c.categorie = :category";
            $params[':category'] = $category;
        }
        
        if ($searchTerm) {
            $query .= " AND (c.titre LIKE :searchTerm OR c.description LIKE :searchTerm)";
            $params[':searchTerm'] = "%{$searchTerm}%";
        }
        
        $query .= " ORDER BY c.date_creation DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $itemsPerPage;
        $params[':offset'] = $offset;
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => &$val) {
            if ($key == ':limit' || $key == ':offset') {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val);
            }
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCourses($category = null, $searchTerm = '') {
        $query = "SELECT COUNT(*) FROM cours c WHERE c.supprime = 0";
        
        $params = array();
        
        if ($category) {
            $query .= " AND c.categorie = :category";
            $params[':category'] = $category;
        }
        
        if ($searchTerm) {
            $query .= " AND (c.titre LIKE :searchTerm OR c.description LIKE :searchTerm)";
            $params[':searchTerm'] = "%{$searchTerm}%";
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => &$val) {
            $stmt->bindValue($key, $val);
        }
        
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    public function getCategories() {
        $query = "SELECT DISTINCT categorie FROM cours WHERE supprime = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function isCoursReserved($titre_cours, $matricule_etudiant) {
        $query = "SELECT COUNT(*) FROM inscris_cours 
                  WHERE titre_cours = :titre_cours AND matricule_etudiant = :matricule_etudiant";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titre_cours', $titre_cours);
        $stmt->bindParam(':matricule_etudiant', $matricule_etudiant);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }

    public function reserveCourse($titre_cours, $matricule_enseignant, $matricule_etudiant) {
        if ($this->isCoursReserved($titre_cours, $matricule_etudiant)) {
            return false; // Le cours est déjà réservé
        }

        $query = "INSERT INTO inscris_cours (titre_cours, matricule_enseignant, matricule_etudiant) 
                  VALUES (:titre_cours, :matricule_enseignant, :matricule_etudiant)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titre_cours', $titre_cours);
        $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
        $stmt->bindParam(':matricule_etudiant', $matricule_etudiant);
        
        return $stmt->execute();
    }

    public function getCart($matricule_etudiant) {
        $query = "SELECT ic.*, c.description, c.type, c.format, c.file_path, c.categorie, u.nom, u.prenom
                  FROM inscris_cours ic
                  JOIN cours c ON ic.titre_cours = c.titre AND ic.matricule_enseignant = c.matricule_enseignant
                  JOIN user_ u ON ic.matricule_enseignant = u.matricule
                  WHERE ic.matricule_etudiant = :matricule_etudiant";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule_etudiant', $matricule_etudiant);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeFromCart($titre_cours, $matricule_etudiant) {
        $query = "DELETE FROM inscris_cours 
                  WHERE titre_cours = :titre_cours AND matricule_etudiant = :matricule_etudiant";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titre_cours', $titre_cours);
        $stmt->bindParam(':matricule_etudiant', $matricule_etudiant);
        
        return $stmt->execute();
    }
}

