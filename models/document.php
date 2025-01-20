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
        $stmt->bindParam(":nombre_pages", $this->nombre_pages);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function modifierCours() {
        $query = "UPDATE " . $this->table_name . "
                  SET titre = :titre, description = :description, format = :format, 
                      categorie = :categorie, file_path = :file_path, nombre_pages = :nombre_pages
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
        $stmt->bindParam(":nombre_pages", $this->nombre_pages);
        $stmt->bindParam(":titre_original", $this->titre);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

