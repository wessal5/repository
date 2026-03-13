<?php
require_once 'config/database.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* FETCH USER WATCHLIST */
$sql = "SELECT w.watchlist_id, w.status, m.movie_id, m.title, m.poster_url, m.genre, m.release_year
        FROM watchlist w
        JOIN movies m ON w.movie_id = m.movie_id
        WHERE w.user_id = ?
        ORDER BY w.added_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold">My Watchlist</h2>
        <p class="text-muted">Keep track of movies you want to watch or have already seen.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="movies.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add More Movies</a>
    </div>
</div>

<div class="card bg-dark border-secondary shadow">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead>
                    <tr class="text-muted border-secondary">
                        <th class="ps-4">Movie</th>
                        <th>Genre</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="border-secondary">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center py-2">
                                        <a href="movie_details.php?id=<?php echo $row['movie_id']; ?>">
                                            <img src="<?php echo htmlspecialchars($row['poster_url']); ?>" class="rounded me-3" style="width: 50px; height: 75px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/60x90?text=No+Poster'">
                                        </a>
                                        <div>
                                            <a href="movie_details.php?id=<?php echo $row['movie_id']; ?>" class="text-white text-decoration-none fw-bold"><?php echo htmlspecialchars($row['title']); ?></a>
                                            <div class="small text-muted"><?php echo $row['release_year']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['genre']); ?></span></td>
                                <td>
                                    <?php if ($row['status'] == 'planned'): ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Planned</span>
                                    <?php elseif ($row['status'] == 'watching'): ?>
                                        <span class="badge bg-primary"><i class="fas fa-play me-1"></i> Watching</span>
                                    <?php elseif ($row['status'] == 'completed'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i> Completed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Update
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-dark">
                                            <li>
                                                <form action="update_status.php" method="POST">
                                                    <input type="hidden" name="watchlist_id" value="<?php echo $row['watchlist_id']; ?>">
                                                    <input type="hidden" name="status" value="planned">
                                                    <button type="submit" class="dropdown-item">Set to Planned</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="update_status.php" method="POST">
                                                    <input type="hidden" name="watchlist_id" value="<?php echo $row['watchlist_id']; ?>">
                                                    <input type="hidden" name="status" value="watching">
                                                    <button type="submit" class="dropdown-item">Set to Watching</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="update_status.php" method="POST">
                                                    <input type="hidden" name="watchlist_id" value="<?php echo $row['watchlist_id']; ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="dropdown-item">Set to Completed</button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="remove_from_watchlist.php" method="POST">
                                                    <input type="hidden" name="watchlist_id" value="<?php echo $row['watchlist_id']; ?>">
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Remove from watchlist?')">Remove</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-film fa-3x mb-3"></i>
                                <p>Your watchlist is empty. <a href="movies.php" class="text-primary">Add some movies!</a></p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$stmt->close();
include 'includes/footer.php';
?>
