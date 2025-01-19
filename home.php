<?php
session_start();
require_once 'db/Database.php';
require_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$categories = $user->getAllCategories();

$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$coursesPerPage = 3;

$courses = $user->getCoursesWithPagination($category, $search, $page, $coursesPerPage);
$totalCourses = $user->getTotalCourses($category, $search);
$totalPages = ceil($totalCourses / $coursesPerPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Youdemy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style/style_home.css">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
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
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="<?php echo $page == $i ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
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