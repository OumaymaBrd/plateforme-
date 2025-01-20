<?php
require_once 'Cours.php';

class CoursVideo extends Cours {
    public function __construct($db) {
        parent::__construct($db);
        $this->type = 'video';
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
        return parent::modifierCours();
    }
}

