<?php
class User {
    private $conn;
    private $table_name = "user_";

    public $id;
    public $matricule;
    public $nom;
    public $prenom;
    public $age;
    public $mot_passe;
    public $post;
    public $email;
    public $status;
    public $error_message;

    public function __construct($db) {
        $this->conn = $db;
        $this->error_message = '';
    }

    public function register() {
        // Générer le matricule avant l'insertion
        $this->matricule = $this->generateMatricule();

        $query = "INSERT INTO " . $this->table_name . " 
                  (matricule, nom, prenom, age, mot_passe, post, email, status) 
                  VALUES (:matricule, :nom, :prenom, :age, :mot_passe, :post, :email, :status)";

        $stmt = $this->conn->prepare($query);

        $this->matricule = $this->sanitizeInput($this->matricule);
        $this->nom = $this->sanitizeInput($this->nom);
        $this->prenom = $this->sanitizeInput($this->prenom);
        $this->age = $this->sanitizeInput($this->age);
        $this->mot_passe = password_hash($this->mot_passe, PASSWORD_DEFAULT);
        $this->post = $this->sanitizeInput($this->post);
        $this->email = $this->sanitizeInput($this->email);
        $this->status = ($this->post === 'etudiant') ? 'accepter' : 'en Cours';

        $stmt->bindParam(":matricule", $this->matricule);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":age", $this->age);
        $stmt->bindParam(":mot_passe", $this->mot_passe);
        $stmt->bindParam(":post", $this->post);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":status", $this->status);

        try {
            if($stmt->execute()) {
                return true;
            }
            $this->error_message = "Erreur lors de l'exécution de la requête.";
            error_log("Erreur d'inscription : " . $this->error_message);
            return false;
        } catch (PDOException $e) {
            $this->error_message = "Erreur PDO : " . $e->getMessage();
            error_log("Erreur d'inscription : " . $this->error_message);
            return false;
        }
    }

    private function sanitizeInput($input) {
        return htmlspecialchars(strip_tags($input ?? ''));
    }

    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function generateMatricule() {
        $prefix = 'YD';
        $randomNumber = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $matricule = $prefix . $randomNumber;

        // Vérifier si le matricule existe déjà
        while ($this->matriculeExists($matricule)) {
            $randomNumber = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $matricule = $prefix . $randomNumber;
        }

        return $matricule;
    }

    private function matriculeExists($matricule) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE matricule = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $matricule);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function connect($matricule, $mot_passe) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE matricule = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $matricule);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $this->error_message = "Matricule non trouvé.";
            return false;
        }

        if (password_verify($mot_passe, $row['mot_passe'])) {
            $this->id = $row['id'];
            $this->matricule = $row['matricule'];
            $this->nom = $row['nom'];
            $this->prenom = $row['prenom'];
            $this->age = $row['age'];
            $this->post = $row['post'];
            $this->email = $row['email'];
            $this->status = $row['status'];
            return true;
        } else {
            $this->error_message = "Mot de passe incorrect.";
            return false;
        }
    }

    public function isAdmin() {
        return $this->post === 'admin';
    }

    public function getAllCourses() {
        $query = "SELECT c.*, u.nom as nom_enseignant, u.prenom as prenom_enseignant 
                  FROM cours c 
                  JOIN user_ u ON c.matricule_enseignant = u.matricule 
                  WHERE c.supprime = 0 
                  ORDER BY c.date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filterCourses($category = null, $search = null) {
        $query = "SELECT c.*, u.nom as nom_enseignant, u.prenom as prenom_enseignant 
                  FROM cours c 
                  JOIN user_ u ON c.matricule_enseignant = u.matricule 
                  WHERE c.supprime = 0";
        
        if ($category) {
            $query .= " AND c.categorie = :category";
        }
        
        if ($search) {
            $query .= " AND (c.titre LIKE :search OR c.description LIKE :search)";
        }
        
        $query .= " ORDER BY c.date_creation DESC";
        
        $stmt = $this->conn->prepare($query);
        // 


        // test
        // 
        if ($category) {
            $stmt->bindParam(':category', $category);
        }
        
        if ($search) {
            $search = "%{$search}%";
            $stmt->bindParam(':search', $search);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCategories() {
        $query = "SELECT DISTINCT categorie FROM cours WHERE supprime = 0 ORDER BY categorie";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getCourseDetails($courseId) {
        $query = "SELECT c.*, u.nom as nom_enseignant, u.prenom as prenom_enseignant 
                  FROM cours c 
                  JOIN user_ u ON c.matricule_enseignant = u.matricule 
                  WHERE c.id = :id AND c.supprime = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

