<?php
require_once 'cours.php';

class CoursVideo extends Cours {
    protected $duree_minutes;

    public function __construct($db) {
        parent::__construct($db);
        $this->type = 'video';
    }

    public function getDureeMinutes() {
        return $this->duree_minutes;
    }

    public function setDureeMinutes($duree_minutes) {
        $this->duree_minutes = $duree_minutes;
    }

    public function ajouterCours() {
        $query = "INSERT INTO " . $this->table_name . "
                  (titre, description, type, format, categorie, matricule_enseignant, file_path, duree_minutes)
                  VALUES
                  (:titre, :description, :type, :format, :categorie, :matricule_enseignant, :file_path, :duree_minutes)";

        $stmt = $this->conn->prepare($query);

        $this->titre = $this->sanitizeInput($this->titre);
        $this->description = $this->sanitizeInput($this->description);
        $this->format = $this->sanitizeInput($this->format);
        $this->categorie = $this->sanitizeInput($this->categorie);
        $this->file_path = $this->sanitizeInput($this->file_path);

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":format", $this->format);
        $stmt->bindParam(":categorie", $this->categorie);
        $stmt->bindParam(":matricule_enseignant", $this->matricule_enseignant);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":duree_minutes", $this->duree_minutes);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function modifierCours() {
        $query = "UPDATE " . $this->table_name . "
                  SET titre = :titre, description = :description, format = :format, 
                      categorie = :categorie, file_path = :file_path, duree_minutes = :duree_minutes
                  WHERE titre = :titre_original";

        $stmt = $this->conn->prepare($query);

        $this->titre = $this->sanitizeInput($this->titre);
        $this->description = $this->sanitizeInput($this->description);
        $this->format = $this->sanitizeInput($this->format);
        $this->categorie = $this->sanitizeInput($this->categorie);
        $this->file_path = $this->sanitizeInput($this->file_path);

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":format", $this->format);
        $stmt->bindParam(":categorie", $this->categorie);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":duree_minutes", $this->duree_minutes);
        $stmt->bindParam(":titre_original", $this->titre);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

