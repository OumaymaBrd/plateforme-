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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300&family=Poppins:wght@300&display=swap');

        :root {
            --background: #1a1a2e;
            --color: #ffffff;
            --primary-color: #0f3460;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            background: var(--background);
            color: var(--color);
            letter-spacing: 1px;
            transition: background 0.2s ease;
        }

        a {
            text-decoration: none;
            color: var(--color);
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .login-container {
            position: relative;
            width: 100%;
            max-width: 400px;
        }

        .form-container {
            border: 1px solid hsla(0, 0%, 65%, 0.158);
            box-shadow: 0 0 36px 1px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            backdrop-filter: blur(20px);
            z-index: 99;
            padding: 2rem;
        }

        .login-container form input,
        .login-container form select {
            display: block;
            padding: 14.5px;
            width: 100%;
            margin: 1rem 0;
            color: var(--color);
            outline: none;
            background-color: #9191911f;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            letter-spacing: 0.8px;
            font-size: 15px;
            backdrop-filter: blur(15px);
        }

        .login-container form input:focus,
        .login-container form select:focus {
            box-shadow: 0 0 16px 1px rgba(0, 0, 0, 0.2);
            animation: wobble 0.3s ease-in;
        }

        .login-container form button {
            background-color: var(--primary-color);
            color: var(--color);
            display: block;
            padding: 13px;
            border-radius: 5px;
            outline: none;
            font-size: 18px;
            letter-spacing: 1.5px;
            font-weight: bold;
            width: 100%;
            cursor: pointer;
            margin: 2rem 0 1rem;
            transition: all 0.1s ease-in-out;
            border: none;
        }

        .login-container form button:hover {
            box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.15);
            transform: scale(1.02);
        }

        .circle {
            width: 8rem;
            height: 8rem;
            background: var(--primary-color);
            border-radius: 50%;
            position: absolute;
            z-index: -1;
        }

        .circle-one {
            top: 0;
            left: 0;
            transform: translate(-45%, -45%);
        }

        .circle-two {
            bottom: 0;
            right: 0;
            transform: translate(45%, 45%);
        }

        .opacity {
            opacity: 0.6;
        }

        .message {
            margin: 1rem 0;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background-color: #4CAF50;
            color: white;
        }

        .error {
            background-color: #f44336;
            color: white;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: var(--background);
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            text-align: center;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }

        @keyframes wobble {
            0% { transform: scale(1.025); }
            25% { transform: scale(1); }
            75% { transform: scale(1.025); }
            100% { transform: scale(1); }
        }

        @media (max-width: 768px) {
            .login-container {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <a href="accueil.html" class="home-link">Accueil</a>

    <section class="container">
        <div class="login-container">
            <div class="circle circle-one"></div>
            <div class="form-container">
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
        //test hjj
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

