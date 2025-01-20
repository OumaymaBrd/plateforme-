--create database
create database Plateforme_Youdemy;

--creation tableau categorie 
create table categorie(
    id int PRIMARY key AUTO_INCREMENT ,
    nom_categorie varchar(20),
    description varchar(20),
    supprime int DEFAULT 0
    );

--creation d une tableau user_
create table user_(
    id int PRIMARY KEY AUTO_INCREMENT,
    matricule varchar(500), 
    nom varchar(50),
    prenom varchar(50),
    age int,
    mot_passe varchar(500),
    role ENUM('enseignant','etudiant', 'admin') ,
    email varchar(100),
    status ENUM('en Cours', 'accepter', 'refuser') DEFAULT 'en cours'
    );

    --ajout la table tags 
    CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_tag VARCHAR(100) NOT NULL UNIQUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE user_
CHANGE COLUMN role post VARCHAR(255);

--
CREATE TABLE cours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    type ENUM('document', 'video') NOT NULL,
    format VARCHAR(50),
    nombre_pages INT,
    duree_minutes INT,
    tags TEXT,
    categorie VARCHAR(100),
    matricule_enseignant VARCHAR(50),
    supprime TINYINT(1) DEFAULT 0,
    FOREIGN KEY (matricule_enseignant) REFERENCES user_(matricule)
) ;

--
ALTER TABLE cours ADD COLUMN file_path VARCHAR(255) AFTER matricule_enseignant;
--
CREATE TABLE inscris_cours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre_cours VARCHAR(50),
    matricule_enseignant VARCHAR(100),
    matricule_etudiant VARCHAR(100),
    FOREIGN KEY (matricule_enseignant) REFERENCES cours(matricule_enseignant),
    FOREIGN KEY (matricule_etudiant) REFERENCES user_(matricule),
    FOREIGN KEY (titre_cours) REFERENCES cours(titre)
);

--create table association 
CREATE TABLE tags_courses(
    id int PRIMARY KEY AUTO_INCREMENT,
    id_cours int , 
    id_tags int ,
    
     FOREIGN KEY (id_cours) REFERENCES cours(id) ,
    FOREIGN KEY (id_tags) REFERENCES tags(id)
    
    );

