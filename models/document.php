<?php
require_once 'Cours.php';

class CoursDocument extends Cours {
    public function ajouterCours() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET titre=:titre, description=:description, type='document', format=:format, 
                      nombre_pages=:nombre_pages, categorie=:categorie, 
                      matricule_enseignant=:matricule_enseignant, file_path=:file_path";

        $stmt = $this->conn->prepare($query);

        $this->titre = $this->sanitizeInput($this->titre);
        $this->description = $this->sanitizeInput($this->description);
        $this->format = $this->sanitizeInput($this->format);
        $this->categorie = $this->sanitizeInput($this->categorie);
        $this->matricule_enseignant = $this->sanitizeInput($this->matricule_enseignant);
        $this->file_path = $this->sanitizeInput($this->file_path);

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":format", $this->format);
        $stmt->bindParam(":nombre_pages", $this->nombre_pages);
        $stmt->bindParam(":categorie", $this->categorie);
        $stmt->bindParam(":matricule_enseignant", $this->matricule_enseignant);
        $stmt->bindParam(":file_path", $this->file_path);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function modifierCours() {
        $query = "UPDATE " . $this->table_name . "
                  SET titre=:titre, description=:description, format=:format,
                      nombre_pages=:nombre_pages, categorie=:categorie";
        
        if (isset($this->file_path)) {
            $query .= ", file_path=:file_path";
        }
        
        $query .= " WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->titre = $this->sanitizeInput($this->titre);
        $this->description = $this->sanitizeInput($this->description);
        $this->format = $this->sanitizeInput($this->format);
        $this->categorie = $this->sanitizeInput($this->categorie);

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":format", $this->format);
        $stmt->bindParam(":nombre_pages", $this->nombre_pages);
        $stmt->bindParam(":categorie", $this->categorie);
        $stmt->bindParam(":id", $this->id);

        if (isset($this->file_path)) {
            $stmt->bindParam(":file_path", $this->file_path);
        }

        return $stmt->execute();
    }
}

