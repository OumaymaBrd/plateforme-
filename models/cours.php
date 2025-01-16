<?php
abstract class Cours {
    protected $conn;
    protected $table_name = "cours";

    public $id;
    public $titre;
    public $description;
    public $date_creation;
    public $type;
    public $format;
    public $tags;
    public $categorie;
    public $matricule_enseignant;
    public $file_path;
    public $supprime;

    public function __construct($db) {
        $this->conn = $db;
    }

    abstract public function ajouterCours();
    abstract public function modifierCours();
    abstract public function supprimerCours();

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCoursesForEnseignant($matricule_enseignant) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE matricule_enseignant = ? AND supprime = 0 
                  ORDER BY date_creation DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $matricule_enseignant);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategories() {
        $query = "SELECT nom_categorie FROM categorie WHERE supprime = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getTags() {
        $query = "SELECT nom_tag FROM tags";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getEnrolledCourses($matricule_enseignant) {
        $query = "SELECT u.nom, u.prenom, ic.titre_cours, ic.matricule_enseignant, ic.matricule_etudiant 
                  FROM inscris_cours ic
                  JOIN user_ u ON ic.matricule_etudiant = u.matricule
                  WHERE ic.matricule_enseignant = :matricule_enseignant
                  ORDER BY ic.titre_cours, u.nom, u.prenom";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nouvelle méthode pour obtenir le nombre d'étudiants inscrits
    public function getEnrolledStudentsCount($matricule_enseignant) {
        $query = "SELECT COUNT(DISTINCT matricule_etudiant) as count
                  FROM inscris_cours
                  WHERE matricule_enseignant = :matricule_enseignant";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    // Nouvelle méthode pour obtenir le nombre de cours
    public function getCoursesCount($matricule_enseignant) {
        $query = "SELECT COUNT(id) as count
                  FROM " . $this->table_name . "
                  WHERE matricule_enseignant = :matricule_enseignant AND supprime = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
}

