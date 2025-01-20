<?php
abstract class Cours {
    protected $conn;
    protected $table_name = "cours";

    protected $id;
    protected $titre;
    protected $description;
    protected $type;
    protected $format;
    protected $categorie;
    protected $matricule_enseignant;
    protected $file_path;
    protected $supprime;
    protected $nombre_pages;
    protected $duree_minutes;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Getters and Setters
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getTitre() {
        return $this->titre;
    }

    public function setTitre($titre) {
        $this->titre = $titre;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getFormat() {
        return $this->format;
    }

    public function setFormat($format) {
        $this->format = $format;
    }

    public function getCategorie() {
        return $this->categorie;
    }

    public function setCategorie($categorie) {
        $this->categorie = $categorie;
    }

    public function getMatriculeEnseignant() {
        return $this->matricule_enseignant;
    }

    public function setMatriculeEnseignant($matricule_enseignant) {
        $this->matricule_enseignant = $matricule_enseignant;
    }

    public function getFilePath() {
        return $this->file_path;
    }

    public function setFilePath($file_path) {
        $this->file_path = $file_path;
    }

    public function getSupprime() {
        return $this->supprime;
    }

    public function setSupprime($supprime) {
        $this->supprime = $supprime;
    }

    public function getNombrePages() {
        return $this->nombre_pages;
    }

    public function setNombrePages($nombre_pages) {
        $this->nombre_pages = $nombre_pages;
    }

    public function getDureeMinutes() {
        return $this->duree_minutes;
    }

    public function setDureeMinutes($duree_minutes) {
        $this->duree_minutes = $duree_minutes;
    }

    abstract public function ajouterCours();

    public function modifierCours() {
        $query = "UPDATE " . $this->table_name . "
              SET description = :description, format = :format, 
                  categorie = :categorie, file_path = :file_path";
    
        if (isset($this->nombre_pages)) {
            $query .= ", nombre_pages = :nombre_pages";
        } elseif (isset($this->duree_minutes)) {
            $query .= ", duree_minutes = :duree_minutes";
        }
    
        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":format", $this->format);
        $stmt->bindParam(":categorie", $this->categorie);
        $stmt->bindParam(":file_path", $this->file_path);

        if (isset($this->nombre_pages)) {
            $stmt->bindParam(":nombre_pages", $this->nombre_pages);
        } elseif (isset($this->duree_minutes)) {
            $stmt->bindParam(":duree_minutes", $this->duree_minutes);
        }

        try {
            if($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la modification du cours : " . $e->getMessage());
            throw $e;
        }
        return false;
    }

    public static function getCoursesForEnseignant($db, $matricule) {
        $query = "SELECT c.*, GROUP_CONCAT(t.nom_tag) as tags 
                  FROM " . (new static($db))->table_name . " c
                  LEFT JOIN tags_courses tc ON c.id = tc.id_cours
                  LEFT JOIN tags t ON tc.id_tags = t.id
                  WHERE c.matricule_enseignant = :matricule AND c.supprime = 0 
                  GROUP BY c.id
                  ORDER BY c.titre DESC";
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':matricule', $matricule);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des cours : " . $e->getMessage());
            throw $e;
        }
    }

    public static function getCategories($db) {
        $query = "SELECT DISTINCT categorie FROM " . (new static($db))->table_name . " WHERE supprime = 0 ORDER BY categorie";
        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des catégories : " . $e->getMessage());
            throw $e;
        }
    }

    public static function getTags($db) {
        $query = "SELECT id, nom_tag FROM tags ORDER BY nom_tag";
        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des tags : " . $e->getMessage());
            throw $e;
        }
    }

    public static function getEnrolledCourses($db, $matricule_enseignant) {
        $query = "SELECT u.nom, u.prenom, ic.titre_cours, u.matricule as matricule_etudiant
                  FROM inscris_cours ic
                  JOIN " . (new static($db))->table_name . " c ON ic.titre_cours = c.titre
                  JOIN user_ u ON ic.matricule_etudiant = u.matricule
                  WHERE c.matricule_enseignant = :matricule_enseignant AND c.supprime = 0
                  ORDER BY c.titre, u.nom, u.prenom";
        
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des inscriptions : " . $e->getMessage());
            throw $e;
        }
    }

    public static function getEnrolledStudentsCount($db, $matricule_enseignant) {
        $query = "SELECT COUNT(DISTINCT ic.matricule_etudiant) as count
                  FROM inscris_cours ic
                  JOIN " . (new static($db))->table_name . " c ON ic.titre_cours = c.titre
                  WHERE c.matricule_enseignant = :matricule_enseignant AND c.supprime = 0";
        
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Erreur lors du comptage des étudiants inscrits : " . $e->getMessage());
            throw $e;
        }
    }

    public static function getCoursesCount($db, $matricule_enseignant) {
        $query = "SELECT COUNT(*) as count
                  FROM " . (new static($db))->table_name . "
                  WHERE matricule_enseignant = :matricule_enseignant AND supprime = 0";
        
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Erreur lors du comptage des cours : " . $e->getMessage());
            throw $e;
        }
    }

    public function supprimerCours() {
        $query = "UPDATE " . $this->table_name . " SET supprime = 1 WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression du cours : " . $e->getMessage());
            throw $e;
        }
    }

    public function getCoursByTitre($titre) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE titre = :titre";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':titre', $titre);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $this->setId($row['id']);
                $this->setTitre($row['titre']);
                $this->setDescription($row['description']);
                $this->setType($row['type']);
                $this->setFormat($row['format']);
                $this->setCategorie($row['categorie']);
                $this->setMatriculeEnseignant($row['matricule_enseignant']);
                $this->setFilePath($row['file_path']);
                $this->setSupprime($row['supprime']);
                $this->setNombrePages($row['nombre_pages'] ?? null);
                $this->setDureeMinutes($row['duree_minutes'] ?? null);
                return true;
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du cours par titre : " . $e->getMessage());
            throw $e;
        }
        
        return false;
    }

    protected function sanitizeInput($input) {
        return htmlspecialchars(strip_tags($input));
    }

    public static function supprimerInscription($db, $matricule_etudiant, $titre_cours) {
        $query = "DELETE FROM inscris_cours 
                  WHERE matricule_etudiant = :matricule_etudiant 
                  AND titre_cours = :titre_cours";
        
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':matricule_etudiant', $matricule_etudiant);
            $stmt->bindParam(':titre_cours', $titre_cours);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'inscription : " . $e->getMessage());
            throw $e;
        }
    }

    public function getDebugInfo() {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'description' => $this->description,
            'type' => $this->type,
            'format' => $this->format,
            'categorie' => $this->categorie,
            'file_path' => $this->file_path,
            'nombre_pages' => $this->nombre_pages ?? null,
            'duree_minutes' => $this->duree_minutes ?? null,
        ];
    }
}

