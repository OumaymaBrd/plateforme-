<?php
class Tags_courses {
    private $conn;
    private $table_name = "tags_courses";

    public $id;
    public $id_cours;
    public $id_tags;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addTagToCourse($id_cours, $id_tags) {
        $query = "INSERT INTO " . $this->table_name . " (id_cours, id_tags) VALUES (:id_cours, :id_tags)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_cours", $id_cours);
        $stmt->bindParam(":id_tags", $id_tags);
        return $stmt->execute();
    }

    public function removeTagFromCourse($id_cours, $id_tags) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_cours = :id_cours AND id_tags = :id_tags";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_cours", $id_cours);
        $stmt->bindParam(":id_tags", $id_tags);
        return $stmt->execute();
    }

    public function getTagsForCourse($id_cours) {
        $query = "SELECT t.id, t.nom FROM tags t
                  JOIN " . $this->table_name . " tc ON t.id = tc.id_tags
                  WHERE tc.id_cours = :id_cours";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_cours", $id_cours);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateTagsForCourse($id_cours, $new_tags) {
        // First, remove all existing tags for this course
        $query = "DELETE FROM " . $this->table_name . " WHERE id_cours = :id_cours";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_cours", $id_cours);
        $stmt->execute();

        // Then, add the new tags
        foreach ($new_tags as $tag_id) {
            $this->addTagToCourse($id_cours, $tag_id);
        }

        return true;
    }
}

