<?php
class Cours {
    protected $conn;
    protected $table_name = "cours";

    public $id;
    public $titre;
    public $description;
    public $type;
    public $format;
    public $tags;
    public $categorie;
    public $matricule_enseignant;
    public $file_path;
    public $supprime;
    public $nombre_pages;
    public $duree_minutes;

    public function __construct($db) {
        $this->conn = $db;
    }
   
    public function getCoursesForEnseignant($matricule) {
        $query = "SELECT * FROM " . $this->table_name . "
              WHERE matricule_enseignant = :matricule AND supprime = 0 
              ORDER BY id DESC";
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
        $query = "SELECT DISTINCT TRIM(tag) as tag FROM (
                    SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(tags, ',', n.n), ',', -1) as tag
                    FROM " . $this->table_name . "
                    CROSS JOIN (
                        SELECT a.N + b.N * 10 + 1 n
                        FROM (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
                        CROSS JOIN (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
                        ORDER BY n
                    ) n
                    WHERE n.n <= 1 + (LENGTH(tags) - LENGTH(REPLACE(tags, ',', '')))
                    AND supprime = 0
                ) as subquery
                WHERE tag != ''
                ORDER BY tag";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
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
            $this->tags = $row['tags'];
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

    // abstract function ajouterCours();
    // abstract function modifierCours();
}

