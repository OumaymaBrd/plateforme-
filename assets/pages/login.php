<?php
session_start();
require_once '../../db/Database.php';
require_once '../../models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $matricule = $_POST['matricule'];
    $mot_passe = $_POST['mot_passe'];

    if ($user->connect($matricule, $mot_passe)) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_matricule'] = $user->matricule;
        $_SESSION['user_post'] = $user->post;
        $_SESSION['user_nom'] = $user->nom;
        $_SESSION['user_prenom'] = $user->prenom;
        $_SESSION['user_status'] = $user->status;

        $matricule_param = urlencode($_SESSION['user_matricule']);

        if ($user->isAdmin()) {
            header("Location: admin.php?matricule=$matricule_param");
            exit();
        } elseif ($user->status === 'accepter') {
            switch ($user->post) {
                case 'enseignant':
                    header("Location: enseignat.php?matricule=$matricule_param");
                    exit();
                case 'etudiant':
                    header("Location: etudiant.php?matricule=$matricule_param");
                    exit();
                default:
                    header("Location: ../../home.php?matricule=$matricule_param");
                    exit();
            }
        } elseif ($user->status === 'en Cours') {
            header("Location: EnCoursTraitement.php?matricule=$matricule_param");
            exit();
        } elseif ($user->status === 'refuser') {
            header("Location: 401.php?matricule=$matricule_param");
            exit();
        } else {
            $error = "Statut de compte non reconnu.";
        }
    } else {
        $error = $user->error_message;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Andev Web - Login</title>
    <link rel="stylesheet" href="../style/style_index.css">
    <style>
        .error-message {
            background-color: rgba(255, 0, 0, 0.1);
            color: #ff3333;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        .home-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <section class="container">
        <div class="login-container">
            <div class="circle circle-one"></div>
            <a href="../../home.php" class="home-link">Accueil</a>
            <div class="form-container">
                <img src="https://raw.githubusercontent.com/hicodersofficial/glassmorphism-login-form/master/assets/illustration.png" alt="illustration" class="illustration" />
                <h1 class="opacity">Login</h1>
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="text" name="matricule" placeholder="Matricule" required />
                    <input type="password" name="mot_passe" placeholder="Mot de Passe" required />
                    <button type="submit" name="login" class="opacity">Se connecter</button>
                </form>
                <div class="register-forget opacity">
                    <a href="../../index.php">S'inscrire</a>
                    <a href="#">Mot de passe oublié?</a>
                </div>
            </div>
            <div class="circle circle-two"></div>
        </div>
    </section>
    <script src="../js/script.js"></script>
</body>
</html>

