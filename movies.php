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
$genre_query = $conn->query("SELECT DISTINCT genre FROM movies WHERE genre IS NOT NULL");
while ($row = $genre_query->fetch_assoc()) {
    $split_genres = explode(',', $row['genre']);
    foreach ($split_genres as $g) {
        $g = trim($g);
        if (!empty($g) && !in_array($g, $genres)) {
            $genres[] = $g;
        }
    }
}
sort($genres);
?>

<div class="row mb-5 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold">Explore Movies</h2>
        <p class="text-muted">Find your next favorite movie from our collection.</p>
    </div>
    <div class="col-md-6">
        <form method="GET" class="row g-2">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control bg-dark text-white border-secondary" placeholder="Search movie..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="genre" class="form-select bg-dark text-white border-secondary">
                    <option value="">All Genres</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?php echo $g; ?>" <?php if ($genre_filter == $g) echo 'selected'; ?>><?php echo $g; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php if ($result->num_rows > 0): ?>
        <?php while($movie = $result->fetch_assoc()): ?>
            <div class="col">
                <a href="movie_details.php?id=<?php echo $movie['movie_id']; ?>" class="text-decoration-none">
                    <div class="card movie-card h-100">
                        <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($movie['title']); ?>" onerror="this.src='https://via.placeholder.com/400x600?text=No+Poster'">
                        <div class="card-body">
                            <h5 class="card-title text-light"><?php echo htmlspecialchars($movie['title']); ?></h5>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="text-muted small"><?php echo $movie['release_year']; ?></span>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($movie['genre']); ?></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-search fa-4x text-muted mb-3"></i>
            <p class="lead text-muted">No movies found matching your criteria.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
