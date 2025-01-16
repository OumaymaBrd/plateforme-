<?php
session_start();
require_once 'db/Database.php';
require_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$courses = $user->getAllCourses();
$categories = $user->getAllCategories();

$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

if ($category || $search) {
    $courses = $user->filterCourses($category, $search);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Youdemy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <style>
        :root {
            --primary-color: #1a1a2e;
            --secondary-color: #e94560;
            --accent-color: #0f3460;
            --background-color: #f8f9fa;
            --text-color: #333;
            --light-color: #ffffff;
            --dark-color: #16213e;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        header {
            background-color: var(--primary-color);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--light-color);
            text-decoration: none;
        }
        nav ul {
            display: flex;
            list-style: none;
        }
        nav ul li {
            margin-left: 30px;
        }
        nav ul li a {
            color: var(--light-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        nav ul li a:hover {
            color: var(--secondary-color);
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--secondary-color);
            color: var(--light-color);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
        }
        .btn:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
        }
        .hero {
            background-image: linear-gradient(rgba(26, 26, 46, 0.8), rgba(26, 26, 46, 0.8)), url('https://source.unsplash.com/random/1600x900/?education');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--light-color);
            padding-top: 80px;
        }
        .hero-content {
            max-width: 800px;
        }
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease;
        }
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            animation: fadeInUp 1s ease 0.2s;
            animation-fill-mode: both;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .search-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            animation: fadeInUp 1s ease 0.4s;
            animation-fill-mode: both;
        }
        .search-container input[type="text"],
        .search-container select {
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
        }
        .search-container .btn {
            padding: 12px 24px;
        }
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 50px;
            color: var(--primary-color);
        }
        .course-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        .course-card {
            background-color: var(--light-color);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .course-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        .course-card h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        .course-card p {
            margin-bottom: 15px;
            flex-grow: 1;
        }
        .course-card .btn {
            align-self: flex-start;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: var(--light-color);
            margin: 10% auto;
            padding: 30px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            animation: modalFadeIn 0.3s;
        }
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .close:hover,
        .close:focus {
            color: var(--primary-color);
        }
        .features {
            padding: 80px 0;
            background-color: var(--light-color);
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        .feature-item {
            text-align: center;
            padding: 30px;
            background-color: var(--background-color);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .feature-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .feature-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }
        .testimonials {
            padding: 80px 0;
            background-color: var(--background-color);
        }
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .testimonial-item {
            background-color: var(--light-color);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .testimonial-content {
            font-style: italic;
            margin-bottom: 20px;
        }
        .testimonial-author {
            font-weight: 600;
            color: var(--primary-color);
        }
        footer {
            background-color: var(--primary-color);
            color: var(--light-color);
            padding: 60px 0;
        }
        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .footer-section {
            flex: 1;
            margin-right: 20px;
            min-width: 200px;
        }
        .footer-section h3 {
            margin-bottom: 20px;
            color: var(--secondary-color);
        }
        .footer-section ul {
            list-style: none;
        }
        .footer-section ul li {
            margin-bottom: 10px;
        }
        .footer-section ul li a {
            color: var(--light-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .footer-section ul li a:hover {
            color: var(--secondary-color);
        }
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .social-icons a {
            color: var(--light-color);
            font-size: 24px;
            transition: color 0.3s ease;
        }
        .social-icons a:hover {
            color: var(--secondary-color);
        }
        #contact-form input,
        #contact-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: var(--dark-color);
            color: var(--light-color);
        }
        #contact-form textarea {
            height: 100px;
        }
        .copyright {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="#" class="logo">Youdemy</a>
                <ul>
                    <li><a href="#home">Accueil</a></li>
                    <li><a href="#courses">Cours</a></li>
                    <li><a href="#features">Fonctionnalités</a></li>
                    <li><a href="#testimonials">Témoignages</a></li>
                    <li><a href="assets/pages/login.php">Connexion</a></li>
                    <li><a href="index.php" class="btn">Inscription</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Bienvenue sur Youdemy</h1>
            <p>Découvrez une nouvelle façon d'apprendre en ligne</p>
            <div class="search-container">
                <form action="" method="GET">
                    <input type="text" name="search" placeholder="Rechercher un cours..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    <select name="category">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn">Rechercher</button>
                </form>
            </div>
        </div>
    </section>

    <main class="container">
        <section id="courses">
            <h2 class="section-title">Cours disponibles</h2>
            <div class="course-list">
                <?php foreach ($courses as $index => $course): ?>
                    <div class="course-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <h3><?php echo htmlspecialchars($course['titre']); ?></h3>
                        <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['prenom_enseignant'] . ' ' . $course['nom_enseignant']); ?></p>
                        <p><i class="fas fa-folder"></i> <?php echo htmlspecialchars($course['categorie']); ?></p>
                        <p><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($course['date_creation']); ?></p>
                        <button class="btn view-details" data-id="<?php echo $course['id']; ?>">Voir les détails</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <section id="features" class="features">
        <div class="container">
            <h2 class="section-title">Nos fonctionnalités</h2>
            <div class="features-grid">
                <div class="feature-item" data-aos="fade-up">
                    <i class="fas fa-laptop feature-icon"></i>
                    <h3>Apprentissage en ligne</h3>
                    <p>Accédez à vos cours n'importe où, n'importe quand.</p>
                </div>
                <div class="feature-item" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-users feature-icon"></i>
                    <h3>Communauté active</h3>
                    <p>Échangez avec d'autres apprenants et des experts.</p>
                </div>
                <div class="feature-item" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-certificate feature-icon"></i>
                    <h3>Certificats reconnus</h3>
                    <p>Obtenez des certificats pour valoriser vos compétences.</p>
                </div>
                <div class="feature-item" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-mobile-alt feature-icon"></i>
                    <h3>Application mobile</h3>
                    <p>Apprenez en déplacement avec notre app mobile.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="testimonials">
        <div class="container">
            <h2 class="section-title">Ce que disent nos étudiants</h2>
            <div class="testimonial-grid">
                <div class="testimonial-item" data-aos="fade-up">
                    <p class="testimonial-content">"Youdemy a transformé ma façon d'apprendre. Les cours sont de haute qualité et les instructeurs sont excellents."</p>
                    <p class="testimonial-author">- Marie D.</p>
                </div>
                <div class="testimonial-item" data-aos="fade-up" data-aos-delay="100">
                    <p class="testimonial-content">"J'ai pu acquérir de nouvelles compétences rapidement grâce à Youdemy. C'est une plateforme incroyable !"</p>
                    <p class="testimonial-author">- Thomas L.</p>
                </div>
                <div class="testimonial-item" data-aos="fade-up" data-aos-delay="200">
                    <p class="testimonial-content">"La flexibilité de l'apprentissage en ligne combinée à des cours de qualité fait de Youdemy mon choix numéro un."</p>
                    <p class="testimonial-author">- Sophie M.</p>
                </div>
            </div>
        </div>
    </section>

    <div id="courseModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle"></h2>
            <p id="modalDescription"></p>
            <p id="modalTeacher"></p>
            <p id="modalCategory"></p>
            <p id="modalType"></p>
            <p id="modalFormat"></p>
            <p id="modalDate"></p>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>À propos de Youdemy</h3>
                    <p>Youdemy est une plateforme d'apprentissage en ligne qui offre une large gamme de cours dans divers domaines.</p>
                </div>
                <div class="footer-section">
                    <h3>Liens rapides</h3>
                    <ul>
                        <li><a href="#courses">Tous les cours</a></li>
                        <li><a href="#">Devenir instructeur</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Politique de confidentialité</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contactez-nous</h3>
                    <form id="contact-form">
                        <input type="email" placeholder="Votre email" required>
                        <textarea placeholder="Votre message" required></textarea>
                        <button type="submit" class="btn">Envoyer</button>
                    </form>
                </div>
                <div class="footer-section">
                    <h3>Suivez-nous</h3>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 Youdemy. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        $(document).ready(function() {
            AOS.init({
                duration: 1000,
                once: true
            });

            $('.search-container form').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                window.location.href = 'home.php?' + formData;
            });

            $('.view-details').on('click', function() {
                var courseId = $(this).data('id');
                $.ajax({
                    url: 'assets/pages/get_course_details.php',
                    method: 'GET',
                    data: { id: courseId },
                    dataType: 'json',
                    success: function(response) {
                        $('#modalTitle').text(response.titre);
                        $('#modalDescription').text(response.description);
                        $('#modalTeacher').html('<i class="fas fa-user"></i> ' + response.prenom_enseignant + ' ' + response.nom_enseignant);
                        $('#modalCategory').html('<i class="fas fa-folder"></i> ' + response.categorie);
                        $('#modalType').html('<i class="fas fa-file"></i> ' + response.type);
                        $('#modalFormat').html('<i class="fas fa-file-alt"></i> ' + response.format);
                        $('#modalDate').html('<i class="fas fa-calendar-alt"></i> ' + response.date_creation);
                        $('#courseModal').fadeIn(300);
                    },
                    error: function(xhr, status, error) {
                        console.error("Erreur AJAX:", status, error);
                        alert('Une erreur est survenue lors du chargement des détails du cours. Veuillez réessayer.');
                    }
                });
            });

            $('.close').on('click', function() {
                $('#courseModal').fadeOut(300);
            });

            $(window).on('click', function(event) {
                if (event.target == document.getElementById('courseModal')) {
                    $('#courseModal').fadeOut(300);
                }
            });

            $(window).scroll(function() {
                if ($(this).scrollTop() > 50) {
                    $('header').addClass('scrolled');
                } else {
                    $('header').removeClass('scrolled');
                }
            });

            $('#contact-form').on('submit', function(e) {
                e.preventDefault();
                alert('Merci pour votre message. Nous vous contacterons bientôt!');
                this.reset();
            });
        });
    </script>
</body>
</html>

