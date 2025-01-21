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
    protected $tags;

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

    public function getTags() {
        return $this->tags;
    }

    public function setTags($tags) {
        $this->tags = $tags;
    }

    abstract public function ajouterCours();

    public function modifierCours() {
        $query = "UPDATE " . $this->table_name . " SET ";
        $params = array();

        if (!empty($this->titre)) {
            $query .= "titre = :titre, ";
            $params[':titre'] = $this->titre;
        }
        if (!empty($this->description)) {
            $query .= "description = :description, ";
            $params[':description'] = $this->description;
        }
        if (!empty($this->format)) {
            $query .= "format = :format, ";
            $params[':format'] = $this->format;
        }
        if (!empty($this->categorie)) {
            $query .= "categorie = :categorie, ";
            $params[':categorie'] = $this->categorie;
        }
        if (!empty($this->tags)) {
            $query .= "tags = :tags, ";
            $params[':tags'] = $this->tags;
        }
        if (!empty($this->file_path)) {
            $query .= "file_path = :file_path, ";
            $params[':file_path'] = $this->file_path;
        }

        // Remove the trailing comma and space
        $query = rtrim($query, ", ");

        $query .= " WHERE id = :id";
        $params[':id'] = $this->id;

        try {
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            if($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la modification du cours : " . $e->getMessage());
            throw $e;
        }
        return false;
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
                $this->setTags($row['tags']);
                return true;
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du cours par titre : " . $e->getMessage());
            throw $e;
        }
        
        return false;
    }

    public static function getCoursesForEnseignant($db, $matricule) {
        $query = "SELECT c.*, GROUP_CONCAT(t.nom_tag) as tags 
                  FROM cours c
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
        $query = "SELECT DISTINCT categorie FROM cours WHERE supprime = 0 ORDER BY categorie";
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

    protected function sanitizeInput($input) {
        return htmlspecialchars(strip_tags($input));
    }
}

