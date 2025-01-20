<?php
require_once 'db/Database.php';
require_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$message = '';
$messageType = '';
$showModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user->post = $_POST['post'];
    $user->email = $_POST['email'];
    $user->nom = $_POST['nom'];
    $user->prenom = $_POST['prenom'];
    $user->age = $_POST['age'];
    $user->mot_passe = $_POST['mot_passe'];

    if ($user->emailExists($user->email)) {
        $message = "Cet email est déjà utilisé. Veuillez en choisir un autre.";
        $messageType = 'error';
    } else {
        if ($user->register()) {
            $showModal = true;
            if ($user->post === 'etudiant') {
                $message = "Votre matricule est : " . $user->matricule . ". Compte créé avec succès.";
            } elseif ($user->post === 'enseignant') {
                $message = "Votre matricule est : " . $user->matricule . ". Votre compte est en cours de traitement.";
            }
            $messageType = 'success';
        } else {
            $message = "Une erreur est survenue lors de la création du compte : " . $user->error_message;
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Plateforme Youdemy</title>
    <link href="assets/style/style.css" rel="stylesheet">
</head>
<body>
    

    <section class="container">
        <div class="login-container">
            <div class="circle circle-one"></div>
            <div class="form-container">
            <a href="home.php" class="opacity">Accueil</a>
                <h1 class="opacity">Inscription</h1>
                <?php if ($message && !$showModal): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form action="" method="POST">
                    <select name="post" id="post" required>
                        <option value="" style="color:black;">Sélectionnez votre poste</option>
                        <option value="etudiant" style="color:black;">Étudiant</option>
                        <option value="enseignant" style="color:black;">Enseignant</option>
                    </select>
                    <input type="text" name="nom" id="nom" placeholder="Nom" required>
                    <input type="text" name="prenom" id="prenom" placeholder="Prénom" required>
                    <input type="number" name="age" id="age" placeholder="Âge" required>
                    <input type="email" name="email" id="email" placeholder="Email" required>
                    <input type="password" name="mot_passe" id="mot_passe" placeholder="Mot de passe" required>
                    <button class="opacity" type="submit">S'inscrire</button>
                  
                </form>
            </div>
            <div class="circle circle-two"></div>
        </div>
    </section>

    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p id="modalMessage"></p>
        </div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("myModal");
        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Show modal if there's a success message
        <?php if ($showModal): ?>
        window.onload = function() {
            modal.style.display = "block";
            document.getElementById("modalMessage").innerHTML = "<?php echo addslashes($message); ?>";
        }
        <?php endif; ?>
    </script>
</body>
</html>

