<?php
abstract class Cours {
    protected $conn;
    protected $table_name = "cours";

    public $id;
    public $titre;
    public $description;
    public $type;
    public $format;
    public $categorie;
    public $matricule_enseignant;
    public $file_path;
    public $supprime;
    public $nombre_pages;
    public $duree_minutes;

    public function __construct($db) {
        $this->conn = $db;
    }

    abstract public function ajouterCours();
    abstract public function modifierCours();

    public function getCoursesForEnseignant($matricule) {
        $query = "SELECT c.*, GROUP_CONCAT(t.nom_tag) as tags FROM " . $this->table_name . " c
              LEFT JOIN tags_courses tc ON c.id = tc.id_cours
              LEFT JOIN tags t ON tc.id_tags = t.id
              WHERE c.matricule_enseignant = :matricule AND c.supprime = 0 
              GROUP BY c.id
              ORDER BY c.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule', $matricule);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategories() {
        $query = "SELECT DISTINCT categorie FROM " . $this->table_name . " WHERE supprime = 0 ORDER BY categorie";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getTags() {
        $query = "SELECT id, nom_tag FROM tags ORDER BY nom_tag";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEnrolledCourses($matricule_enseignant) {
        $query = "SELECT u.nom, u.prenom, ic.titre_cours, u.matricule as matricule_etudiant
                  FROM inscris_cours ic
                  JOIN cours c ON ic.titre_cours = c.titre
                  JOIN user_ u ON ic.matricule_etudiant = u.matricule
                  WHERE c.matricule_enseignant = :matricule_enseignant AND c.supprime = 0
                  ORDER BY c.titre, u.nom, u.prenom";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEnrolledStudentsCount($matricule_enseignant) {
        $query = "SELECT COUNT(DISTINCT ic.matricule_etudiant) as count
                  FROM inscris_cours ic
                  JOIN cours c ON ic.titre_cours = c.titre
                  WHERE c.matricule_enseignant = :matricule_enseignant AND c.supprime = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function getCoursesCount($matricule_enseignant) {
        $query = "SELECT COUNT(*) as count
                  FROM " . $this->table_name . "
                  WHERE matricule_enseignant = :matricule_enseignant AND supprime = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function supprimerCours() {
        $query = "UPDATE " . $this->table_name . " SET supprime = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function getCoursById() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->titre = $row['titre'];
            $this->description = $row['description'];
            $this->type = $row['type'];
            $this->format = $row['format'];
            $this->categorie = $row['categorie'];
            $this->matricule_enseignant = $row['matricule_enseignant'];
            $this->file_path = $row['file_path'];
            $this->supprime = $row['supprime'];
            $this->nombre_pages = $row['nombre_pages'];
            $this->duree_minutes = $row['duree_minutes'];
            return true;
        }
        
        return false;
    }

    protected function sanitizeInput($input) {
        return htmlspecialchars(strip_tags($input));
    }

    public function supprimerInscription($matricule_etudiant, $titre_cours) {
        $query = "DELETE FROM inscris_cours 
                  WHERE matricule_etudiant = :matricule_etudiant 
                  AND titre_cours = :titre_cours";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule_etudiant', $matricule_etudiant);
        $stmt->bindParam(':titre_cours', $titre_cours);
        
        return $stmt->execute();
    }
}

