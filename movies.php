<?php
require_once 'config/database.php';
include 'includes/header.php';

// Search and genre filter
$search = $_GET['search'] ?? '';
$genre_filter = $_GET['genre'] ?? '';

// Base query
$sql = "SELECT * FROM movies WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND title LIKE ?";
}

if (!empty($genre_filter)) {
    $sql .= " AND genre LIKE ?";
}

$sql .= " ORDER BY release_year DESC";

$stmt = $conn->prepare($sql);

if (!empty($search) && !empty($genre_filter)) {
    $search_param = "%$search%";
    $genre_param = "%$genre_filter%";
    $stmt->bind_param("ss", $search_param, $genre_param);
} elseif (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("s", $search_param);
} elseif (!empty($genre_filter)) {
    $genre_param = "%$genre_filter%";
    $stmt->bind_param("s", $genre_param);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch genres for filter
$genres = [];

$genre_query = $conn->query("SELECT genre FROM movies WHERE genre IS NOT NULL");

while ($row = $genre_query->fetch_assoc()) {

    $split_genres = explode(',', $row['genre']);

    foreach ($split_genres as $g) {

        $g = trim($g);

        if (!in_array($g, $genres)) {
            $genres[] = $g;
        }
    }
}

sort($genres);

// Watchlist
$user_watchlist = [];

if (isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

    $wl_query = "SELECT movie_id FROM watchlist WHERE user_id = ?";
    $stmt_wl = $conn->prepare($wl_query);
    $stmt_wl->bind_param("i", $user_id);
    $stmt_wl->execute();

    $wl_result = $stmt_wl->get_result();

    while ($row = $wl_result->fetch_assoc()) {
        $user_watchlist[] = $row['movie_id'];
    }

    $stmt_wl->close();
}
?>

<div class="row mb-4">

<div class="col-md-6">
<h2>Movies</h2>
<p class="text-muted">Explore movies and add them to your personal watchlist.</p>
</div>

<div class="col-md-6">

<form method="GET" class="row g-2">

<div class="col-md-6">
<input type="text"
name="search"
class="form-control"
placeholder="Search movie..."
value="<?php echo htmlspecialchars($search); ?>">
</div>

<div class="col-md-4">
<select name="genre" class="form-select">

<option value="">All Genres</option>

<?php foreach ($genres as $g): ?>

<option value="<?php echo $g; ?>"
<?php if ($genre_filter == $g) echo 'selected'; ?>>
<?php echo $g; ?>
</option>

<?php endforeach; ?>

</select>
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Search</button>
</div>

</form>

</div>

</div>

<div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">

<?php if ($result->num_rows > 0): ?>
<?php while($movie = $result->fetch_assoc()): ?>

<div class="col">

<div class="card h-100 shadow movie-card">

<img src="<?php echo htmlspecialchars($movie['poster_url']); ?>"
class="card-img-top"
alt="<?php echo htmlspecialchars($movie['title']); ?>"
onerror="this.src='https://via.placeholder.com/400x600?text=No+Poster'">

<div class="card-body d-flex flex-column">

<h5 class="card-title">
<?php echo htmlspecialchars($movie['title']); ?>
</h5>

<h6 class="card-subtitle mb-2 text-muted">
<?php echo $movie['release_year']; ?>
<?php if ($movie['genre']): ?>
 • <?php echo htmlspecialchars($movie['genre']); ?>
<?php endif; ?>
</h6>

<?php if (!empty($movie['rating'])): ?>

<div class="mb-2">
⭐ <?php echo $movie['rating']; ?>/10
</div>

<?php endif; ?>

<p class="card-text">

<span class="short-text">
<?php echo htmlspecialchars(substr($movie['description'],0,120)); ?>...
</span>

<span class="full-text d-none">
<?php echo htmlspecialchars($movie['description']); ?>
</span>

<a href="#" class="read-more text-primary">Read more</a>

</p>

</div>

<div class="card-footer bg-white border-top-0 d-grid">

<?php if (isset($_SESSION['user_id'])): ?>

<?php if (in_array($movie['movie_id'], $user_watchlist)): ?>

<button class="btn btn-secondary disabled">
In Watchlist
</button>

<?php else: ?>

<form action="add_to_watchlist.php" method="POST">

<input type="hidden"
name="movie_id"
value="<?php echo $movie['movie_id']; ?>">

<button type="submit"
class="btn btn-primary w-100">
Add to Watchlist
</button>

</form>

<?php endif; ?>

<?php else: ?>

<a href="login.php"
class="btn btn-outline-primary">
Login to Add
</a>

<?php endif; ?>

</div>

</div>

</div>

<?php endwhile; ?>
<?php else: ?>

<div class="col-12">
<p class="alert alert-info">
No movies found.
</p>
</div>

<?php endif; ?>

</div>

<script>

document.querySelectorAll(".read-more").forEach(btn => {

btn.addEventListener("click", function(e){

e.preventDefault();

const parent = this.parentElement;

parent.querySelector(".short-text").classList.toggle("d-none");
parent.querySelector(".full-text").classList.toggle("d-none");

this.textContent =
this.textContent === "Read more" ? "Show less" : "Read more";

});

});

</script>

<?php include 'includes/footer.php'; ?>