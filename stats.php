<?php
require_once 'config/database.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Total movies in watchlist
$total_stmt = $conn->prepare("SELECT COUNT(*) as total FROM watchlist WHERE user_id = ?");
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_movies = $total_stmt->get_result()->fetch_assoc()['total'];

// Number of watched movies
$watched_stmt = $conn->prepare("SELECT COUNT(*) as watched FROM watchlist WHERE user_id = ? AND status = 'completed'");
$watched_stmt->bind_param("i", $user_id);
$watched_stmt->execute();
$watched_movies = $watched_stmt->get_result()->fetch_assoc()['watched'];

// Number of planned movies
$planned_stmt = $conn->prepare("SELECT COUNT(*) as planned FROM watchlist WHERE user_id = ? AND status = 'planned'");
$planned_stmt->bind_param("i", $user_id);
$planned_stmt->execute();
$planned_movies = $planned_stmt->get_result()->fetch_assoc()['planned'];

// Favorite Genre
$genre_sql = "
    SELECT m.genre, COUNT(*) as count
    FROM watchlist w
    JOIN movies m ON w.movie_id = m.movie_id
    WHERE w.user_id = ?
    GROUP BY m.genre
    ORDER BY count DESC
    LIMIT 1
";
$genre_stmt = $conn->prepare($genre_sql);
$genre_stmt->bind_param("i", $user_id);
$genre_stmt->execute();
$genre_res = $genre_stmt->get_result()->fetch_assoc();
$favorite_genre = $genre_res ? $genre_res['genre'] : "N/A";

// Recent Activity
$activity_sql = "
    SELECT m.title, w.status, w.added_at
    FROM watchlist w
    JOIN movies m ON w.movie_id = m.movie_id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
    LIMIT 5
";
$activity_stmt = $conn->prepare($activity_sql);
$activity_stmt->bind_param("i", $user_id);
$activity_stmt->execute();
$recent_activity = $activity_stmt->get_result();

?>

<h2 class="fw-bold mb-4">Personal Statistics</h2>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="text-muted small text-uppercase fw-bold mb-2">Total Movies</div>
            <div class="stats-number"><?php echo $total_movies; ?></div>
            <i class="fas fa-film text-secondary mt-3"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="text-muted small text-uppercase fw-bold mb-2">Watched</div>
            <div class="stats-number text-success"><?php echo $watched_movies; ?></div>
            <i class="fas fa-check-circle text-success mt-3"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="text-muted small text-uppercase fw-bold mb-2">Planned</div>
            <div class="stats-number text-warning"><?php echo $planned_movies; ?></div>
            <i class="fas fa-clock text-warning mt-3"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="text-muted small text-uppercase fw-bold mb-2">Favorite Genre</div>
            <div class="stats-number fs-3 py-3"><?php echo htmlspecialchars($favorite_genre); ?></div>
            <i class="fas fa-tag text-primary"></i>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card bg-dark border-secondary shadow">
            <div class="card-header border-secondary bg-dark py-3">
                <h5 class="mb-0 fw-bold">Recent Activity</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush bg-dark">
                    <?php if ($recent_activity->num_rows > 0): ?>
                        <?php while($activity = $recent_activity->fetch_assoc()): ?>
                            <li class="list-group-item bg-dark text-white border-secondary py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($activity['title']); ?></strong>
                                        <div class="small text-muted">Added on <?php echo date("M d, Y", strtotime($activity['added_at'])); ?></div>
                                    </div>
                                    <span class="badge <?php echo $activity['status'] == 'completed' ? 'bg-success' : ($activity['status'] == 'watching' ? 'bg-primary' : 'bg-warning text-dark'); ?>">
                                        <?php echo ucfirst($activity['status']); ?>
                                    </span>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="list-group-item bg-dark text-white border-secondary py-4 text-center">
                            No recent activity found.
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-dark border-secondary shadow p-4 text-center">
            <h5 class="fw-bold mb-3">Watch Progress</h5>
            <?php
                $percentage = $total_movies > 0 ? round(($watched_movies / $total_movies) * 100) : 0;
            ?>
            <div class="progress mb-3" style="height: 25px;">
                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $percentage; ?>%</div>
            </div>
            <p class="text-muted small">You have completed <?php echo $watched_movies; ?> out of <?php echo $total_movies; ?> movies in your list.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
