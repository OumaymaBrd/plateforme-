<?php
class Admin {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getEnseignants() {
        $query = "SELECT * FROM user_ WHERE post='enseignant' ORDER BY 
                  CASE 
                      WHEN status = 'en Cours' THEN 1
                      WHEN status = 'accepter' THEN 2
                      WHEN status = 'refuser' THEN 3
                  END";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateEnseignantStatus($matricule, $status) {
        $query = "UPDATE user_ SET status = :status WHERE matricule = :matricule AND post = 'enseignant'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':matricule', $matricule);
        return $stmt->execute();
    }

    public function getCours() {
        $query = "SELECT c.*, u.nom as nom_enseignant, u.prenom as prenom_enseignant 
                  FROM cours c 
                  JOIN user_ u ON c.matricule_enseignant = u.matricule 
                  WHERE c.supprime = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function supprimerCours($id) {
        $query = "UPDATE cours SET supprime = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getCategories() {
        $query = "SELECT * FROM categorie WHERE supprime = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ajouterCategorie($nom, $description) {
        $query = "INSERT INTO categorie (nom_categorie, description) VALUES (:nom, :description)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        return $stmt->execute();
    }

    public function modifierCategorie($id, $nom, $description) {
        $query = "UPDATE categorie SET nom_categorie = :nom, description = :description WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        return $stmt->execute();
    }

    public function supprimerCategorie($id) {
        $query = "UPDATE categorie SET supprime = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getTags() {
        $query = "SELECT * FROM tags";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ajouterTag($nom) {
        $query = "INSERT INTO tags (nom_tag) VALUES (:nom)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $nom);
        return $stmt->execute();
    }

    public function modifierTag($id, $nom) {
        $query = "UPDATE tags SET nom_tag = :nom WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nom', $nom);
        return $stmt->execute();
    }

    public function supprimerTag($id) {
        $query = "DELETE FROM tags WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getTotalCourses() {
        $query = "SELECT COUNT(*) as total FROM cours WHERE supprime = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    

    public function getCoursesByCategory() {
        $query = "SELECT categorie, COUNT(*) as count FROM cours WHERE supprime = 0 GROUP BY categorie";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopCourses() {
        $query = "SELECT c.titre as titre_cours, COUNT(ic.titre_cours) as count 
                  FROM cours c
                  LEFT JOIN inscris_cours ic ON c.titre = ic.titre_cours
                  WHERE c.supprime = 0
                  GROUP BY c.titre 
                  ORDER BY count DESC 
                  LIMIT 3";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopTeachers() {
        $query = "SELECT u.nom, u.prenom, COUNT(*) as count
                  FROM user_ u
                  JOIN inscris_cours ic ON u.matricule = ic.matricule_enseignant
                  GROUP BY u.matricule
                  ORDER BY count DESC
                  LIMIT 3";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // New method to get course details
    public function getCourseDetails($courseId) {
        $query = "SELECT c.*, u.nom as nom_enseignant, u.prenom as prenom_enseignant 
                  FROM cours c 
                  JOIN user_ u ON c.matricule_enseignant = u.matricule 
                  WHERE c.id = :id AND c.supprime = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $courseId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // New method to get enrolled students for a course
    public function getEnrolledStudents($courseId) {
        $query = "SELECT u.* 
                  FROM user_ u
                  JOIN inscris_cours ic ON u.matricule = ic.matricule_etudiant
                  JOIN cours c ON ic.titre_cours = c.titre
                  WHERE c.id = :id AND u.post = 'etudiant'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $courseId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // New method to get total number of students
    public function getTotalStudents() {
        $query = "SELECT COUNT(*) as total FROM user_ WHERE post = 'etudiant'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // New method to get total number of teachers
    public function getTotalTeachers() {
        $query = "SELECT COUNT(*) as total FROM user_ WHERE post = 'enseignant'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // New method to get recent activities (e.g., new enrollments, new courses)
    public function getRecentActivities($limit = 10) {
        $query = "SELECT 'enrollment' as type, ic.date_inscription as date, u.nom, u.prenom, c.titre
                  FROM inscris_cours ic
                  JOIN user_ u ON ic.matricule_etudiant = u.matricule
                  JOIN cours c ON ic.titre_cours = c.titre
                  UNION ALL
                  SELECT 'new_course' as type, c.date_creation as date, u.nom, u.prenom, c.titre
                  FROM cours c
                  JOIN user_ u ON c.matricule_enseignant = u.matricule
                  ORDER BY date DESC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Error handling method
    private function handleError($e) {
        error_log("Error in Admin class: " . $e->getMessage());
        // You can add more sophisticated error handling here, such as sending notifications
    }

    

    public function getEtudiants() {
        $query = "SELECT * FROM user_ WHERE post='etudiant' ORDER BY 
                  CASE 
                      WHEN status = 'accepter' THEN 1
                      WHEN status = 'refuser' THEN 2
                      WHEN status = 'en Cours' THEN 3
                      
                  END";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUserStatus($matricule, $status, $userType) {
        $query = "UPDATE user_ SET status = :status WHERE matricule = :matricule AND post = :userType";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':matricule', $matricule);
        $stmt->bindParam(':userType', $userType);
        return $stmt->execute();
    }

    
}

