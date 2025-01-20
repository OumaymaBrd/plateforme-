<?php
require_once 'cours.php';

class CoursDocument extends Cours {
    protected $nombre_pages;

    public function __construct($db) {
        parent::__construct($db);
        $this->type = 'document';
    }

    public function getNombrePages() {
        return $this->nombre_pages;
    }

    public function setNombrePages($nombre_pages) {
        $this->nombre_pages = $nombre_pages;
    }

    public function ajouterCours() {
        $query = "INSERT INTO " . $this->table_name . "
                  (titre, description, type, format, categorie, matricule_enseignant, file_path, nombre_pages)
                  VALUES
                  (:titre, :description, :type, :format, :categorie, :matricule_enseignant, :file_path, :nombre_pages)";

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
        $stmt->bindParam(":nombre_pages", $this->getNombrePages());

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function modifierCours() {
        $query = "UPDATE " . $this->table_name . "
                  SET description = :description, format = :format, 
                      categorie = :categorie, file_path = :file_path, nombre_pages = :nombre_pages
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
        $stmt->bindParam(":nombre_pages", $this->getNombrePages());

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

