<?php
require_once 'config/database.php';
include 'includes/header.php';

$movie_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($movie_id <= 0) {
    header("Location: movies.php");
    exit();
}

// Handle Rating Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rating']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);

    // Check if rating already exists
    $check_stmt = $conn->prepare("SELECT rating_id FROM ratings WHERE user_id = ? AND movie_id = ?");
    $check_stmt->bind_param("ii", $user_id, $movie_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $update_stmt = $conn->prepare("UPDATE ratings SET rating = ? WHERE user_id = ? AND movie_id = ?");
        $update_stmt->bind_param("iii", $rating, $user_id, $movie_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO ratings (user_id, movie_id, rating) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iii", $user_id, $movie_id, $rating);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $check_stmt->close();
    echo "<script>alert('Rating submitted!');</script>";
}

// Fetch Movie Details
$stmt = $conn->prepare("SELECT * FROM movies WHERE movie_id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();

if (!$movie) {
    header("Location: movies.php");
    exit();
}

// Fetch Average Rating
$avg_stmt = $conn->prepare("SELECT AVG(rating) as average FROM ratings WHERE movie_id = ?");
$avg_stmt->bind_param("i", $movie_id);
$avg_stmt->execute();
$avg_result = $avg_stmt->get_result()->fetch_assoc();
$average_rating = $avg_result['average'] ? round($avg_result['average'], 1) : "No ratings yet";

// Fetch User's Rating
$user_rating = 0;
if (isset($_SESSION['user_id'])) {
    $user_stmt = $conn->prepare("SELECT rating FROM ratings WHERE user_id = ? AND movie_id = ?");
    $user_stmt->bind_param("ii", $_SESSION['user_id'], $movie_id);
    $user_stmt->execute();
    $user_res = $user_stmt->get_result()->fetch_assoc();
    if ($user_res) {
        $user_rating = $user_res['rating'];
    }
}

// Check if in watchlist
$in_watchlist = false;
if (isset($_SESSION['user_id'])) {
    $wl_stmt = $conn->prepare("SELECT watchlist_id FROM watchlist WHERE user_id = ? AND movie_id = ?");
    $wl_stmt->bind_param("ii", $_SESSION['user_id'], $movie_id);
    $wl_stmt->execute();
    if ($wl_stmt->get_result()->num_rows > 0) {
        $in_watchlist = true;
    }
}
?>

<div class="row">
    <div class="col-md-4">
        <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" class="img-fluid rounded shadow-lg mb-4" alt="<?php echo htmlspecialchars($movie['title']); ?>">
    </div>
    <div class="col-md-8">
        <h1 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($movie['title']); ?></h1>
        <div class="mb-4">
            <span class="badge bg-primary fs-5"><?php echo htmlspecialchars($movie['genre']); ?></span>
            <span class="badge bg-secondary fs-5 ms-2"><?php echo $movie['release_year']; ?></span>
            <span class="ms-3 fs-5"><i class="fas fa-star text-warning"></i> <?php echo $average_rating; ?></span>
        </div>

        <h4 class="fw-bold">Overview</h4>
        <p class="lead text-muted mb-5"><?php echo htmlspecialchars($movie['description']); ?></p>

        <div class="card bg-dark text-white p-4 border-secondary mb-4">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h5 class="mb-3">Rate this movie:</h5>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST" id="ratingForm">
                            <div class="star-rating">
                                <?php for($i=5; $i>=1; $i--): ?>
                                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo ($user_rating == $i) ? 'checked' : ''; ?> onclick="document.getElementById('ratingForm').submit();" />
                                    <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars"><i class="fas fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </form>
                    <?php else: ?>
                        <p class="text-warning small"><a href="login.php" class="text-warning">Login</a> to rate this movie.</p>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($in_watchlist): ?>
                            <button class="btn btn-secondary btn-lg disabled w-100"><i class="fas fa-check me-2"></i> In Watchlist</button>
                        <?php else: ?>
                            <form action="add_to_watchlist.php" method="POST">
                                <input type="hidden" name="movie_id" value="<?php echo $movie['movie_id']; ?>">
                                <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-plus me-2"></i> Add to Watchlist</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary btn-lg w-100">Login to Add</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="similar-movies mt-5 pt-5 border-top border-secondary">
    <h3 class="mb-4">Similar Movies (<?php echo htmlspecialchars($movie['genre']); ?>)</h3>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 g-4">
        <?php
        $genre = $movie['genre'];
        $sim_stmt = $conn->prepare("SELECT * FROM movies WHERE genre = ? AND movie_id != ? LIMIT 6");
        $sim_stmt->bind_param("si", $genre, $movie_id);
        $sim_stmt->execute();
        $sim_result = $sim_stmt->get_result();

        if ($sim_result->num_rows > 0) {
            while ($sim_movie = $sim_result->fetch_assoc()): ?>
                <div class="col">
                    <a href="movie_details.php?id=<?php echo $sim_movie['movie_id']; ?>" class="text-decoration-none text-white">
                        <div class="card movie-card h-100">
                            <img src="<?php echo htmlspecialchars($sim_movie['poster_url']); ?>" class="card-img-top" style="height: 250px;" alt="<?php echo htmlspecialchars($sim_movie['title']); ?>">
                            <div class="card-body p-2">
                                <h6 class="card-title mb-0"><?php echo htmlspecialchars($sim_movie['title']); ?></h6>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile;
        } else {
            echo "<p class='text-muted'>No similar movies found.</p>";
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
