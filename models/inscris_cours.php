<?php

class InscrisCours {
    private $id;
    private $etudiantId;
    private $coursId;
    private $dateInscription;

    public function __construct($etudiantId, $coursId) {
        $this->etudiantId = $etudiantId;
        $this->coursId = $coursId;
        $this->dateInscription = date('Y-m-d H:i:s');
    }

    // Getters
    public function getId() { return $this->id; }
    public function getEtudiantId() { return $this->etudiantId; }
    public function getCoursId() { return $this->coursId; }
    public function getDateInscription() { return $this->dateInscription; }
}