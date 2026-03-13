<?php
require_once 'config/database.php';
include 'includes/header.php';

// Fetch popular movies for the carousel (e.g., top 5 recently added)
$carousel_sql = "SELECT * FROM movies ORDER BY release_year DESC LIMIT 5";
$carousel_result = $conn->query($carousel_sql);

// Fetch more movies for a "Continue Watching" or "Trending" section below
$trending_sql = "SELECT * FROM movies ORDER BY RAND() LIMIT 8";
$trending_result = $conn->query($trending_sql);
?>

<div class="welcome-section text-center mb-5 py-5">
    <div class="typing-container" id="typing-text"></div><span class="cursor"></span>
    <p class="lead mt-3 text-muted">Manage your movies, track your progress, and discover new favorites.</p>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="mt-4">
            <a href="register.php" class="btn btn-primary btn-lg px-5 me-2">Get Started</a>
            <a href="login.php" class="btn btn-outline-light btn-lg px-5">Login</a>
        </div>
    <?php endif; ?>
</div>

<?php if ($carousel_result && $carousel_result->num_rows > 0): ?>
<div id="popularMoviesCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php $first = true; while($movie = $carousel_result->fetch_assoc()): ?>
            <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                <div class="row align-items-center">
                    <div class="col-md-5 text-center text-md-end">
                        <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" class="carousel-poster" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                    </div>
                    <div class="col-md-7 text-center text-md-start px-md-5 mt-4 mt-md-0">
                        <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($movie['title']); ?></h1>
                        <p class="badge bg-primary fs-6 mb-3"><?php echo htmlspecialchars($movie['genre']); ?> • <?php echo $movie['release_year']; ?></p>
                        <p class="lead"><?php echo htmlspecialchars(substr($movie['description'], 0, 200)); ?>...</p>
                        <a href="movie_details.php?id=<?php echo $movie['movie_id']; ?>" class="btn btn-danger btn-lg mt-3">
                            <i class="fas fa-play me-2"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        <?php $first = false; endwhile; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#popularMoviesCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#popularMoviesCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
<?php endif; ?>

<div class="trending-section mt-5">
    <h3 class="mb-4">Trending Now</h3>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
        <?php while($movie = $trending_result->fetch_assoc()): ?>
            <div class="col">
                <a href="movie_details.php?id=<?php echo $movie['movie_id']; ?>" class="text-decoration-none">
                    <div class="card movie-card">
                        <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title text-light"><?php echo htmlspecialchars($movie['title']); ?></h5>
                            <p class="card-text small text-muted"><?php echo $movie['release_year']; ?> • <?php echo htmlspecialchars($movie['genre']); ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    const text = "Welcome to your personal Movie Watchlist.";
    const typingContainer = document.getElementById('typing-text');
    let index = 0;

    function type() {
        if (index < text.length) {
            typingContainer.innerHTML += text.charAt(index);
            index++;
            setTimeout(type, 50);
        }
    }

    document.addEventListener('DOMContentLoaded', type);
</script>

<?php include 'includes/footer.php'; ?>
