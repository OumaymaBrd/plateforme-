<?php
require_once 'Cours.php';

class CoursDocument extends Cours {
    public $nombre_pages;

    public function ajouterCours() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET titre=:titre, description=:description, type='document', format=:format, 
                      nombre_pages=:nombre_pages, tags=:tags, 
                      categorie=:categorie, matricule_enseignant=:matricule_enseignant, file_path=:file_path";

        $stmt = $this->conn->prepare($query);

        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->format = htmlspecialchars(strip_tags($this->format));
        $this->tags = htmlspecialchars(strip_tags($this->tags));
        $this->categorie = htmlspecialchars(strip_tags($this->categorie));
        $this->matricule_enseignant = htmlspecialchars(strip_tags($this->matricule_enseignant));
        $this->file_path = htmlspecialchars(strip_tags($this->file_path));

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":format", $this->format);
        $stmt->bindParam(":nombre_pages", $this->nombre_pages);
        $stmt->bindParam(":tags", $this->tags);
        $stmt->bindParam(":categorie", $this->categorie);
        $stmt->bindParam(":matricule_enseignant", $this->matricule_enseignant);
        $stmt->bindParam(":file_path", $this->file_path);

        return $stmt->execute();
    }

    public function modifierCours() {
        $query = "UPDATE " . $this->table_name . "
                  SET titre=:titre, description=:description, format=:format,
                      nombre_pages=:nombre_pages, tags=:tags, categorie=:categorie";
        
        if (isset($this->file_path)) {
            $query .= ", file_path=:file_path";
        }
        
        $query .= " WHERE id=:id AND type='document'";

        $stmt = $this->conn->prepare($query);

        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->format = htmlspecialchars(strip_tags($this->format));
        $this->tags = htmlspecialchars(strip_tags($this->tags));
        $this->categorie = htmlspecialchars(strip_tags($this->categorie));

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":format", $this->format);
        $stmt->bindParam(":nombre_pages", $this->nombre_pages);
        $stmt->bindParam(":tags", $this->tags);
        $stmt->bindParam(":categorie", $this->categorie);
        $stmt->bindParam(":id", $this->id);

        if (isset($this->file_path)) {
            $stmt->bindParam(":file_path", $this->file_path);
        }

        return $stmt->execute();
    }

    public function supprimerCours() {
        $query = "UPDATE " . $this->table_name . " SET supprime = 1 WHERE id = :id AND type = 'document'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }
}

