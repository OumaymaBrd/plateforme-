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

        $titre = $this->sanitizeInput($this->getTitre());
        $description = $this->sanitizeInput($this->getDescription());
        $format = $this->sanitizeInput($this->getFormat());
        $categorie = $this->sanitizeInput($this->getCategorie());
        $file_path = $this->sanitizeInput($this->getFilePath());

        $stmt->bindParam(":titre", $titre);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":type", $this->getType());
        $stmt->bindParam(":format", $format);
        $stmt->bindParam(":categorie", $categorie);
        $stmt->bindParam(":matricule_enseignant", $this->getMatriculeEnseignant());
        $stmt->bindParam(":file_path", $file_path);
        $stmt->bindParam(":duree_minutes", $this->getDureeMinutes());

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function modifierCours() {
        $query = "UPDATE " . $this->table_name . "
                  SET description = :description, format = :format, 
                      categorie = :categorie, file_path = :file_path, duree_minutes = :duree_minutes
                  WHERE titre = :titre";

        $stmt = $this->conn->prepare($query);

        $titre = $this->sanitizeInput($this->getTitre());
        $description = $this->sanitizeInput($this->getDescription());
        $format = $this->sanitizeInput($this->getFormat());
        $categorie = $this->sanitizeInput($this->getCategorie());
        $file_path = $this->sanitizeInput($this->getFilePath());

        $stmt->bindParam(":titre", $titre);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":format", $format);
        $stmt->bindParam(":categorie", $categorie);
        $stmt->bindParam(":file_path", $file_path);
        $stmt->bindParam(":duree_minutes", $this->getDureeMinutes());

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

