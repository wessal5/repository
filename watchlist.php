<?php
require_once 'config/database.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* FETCH USER WATCHLIST */

$sql = "SELECT w.watchlist_id, w.status, m.movie_id, m.title, m.poster_url, m.genre
        FROM watchlist w
        JOIN movies m ON w.movie_id = m.movie_id
        WHERE w.user_id = ?
        ORDER BY w.added_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$user_id);
$stmt->execute();
$result = $stmt->get_result();


/* FIND USER FAVORITE GENRE */

$favorite_genre = null;

$genre_sql = "
SELECT m.genre
FROM watchlist w
JOIN movies m ON w.movie_id = m.movie_id
WHERE w.user_id = ?
";

$stmt_genre = $conn->prepare($genre_sql);
$stmt_genre->bind_param("i",$user_id);
$stmt_genre->execute();
$result_genres = $stmt_genre->get_result();

$genre_count = [];

while($row = $result_genres->fetch_assoc()){

    $genres = explode(",", $row['genre']);

    foreach($genres as $g){

        $g = trim($g);

        if(!isset($genre_count[$g])){
            $genre_count[$g] = 0;
        }

        $genre_count[$g]++;
    }
}

if(!empty($genre_count)){
    arsort($genre_count);
    $favorite_genre = array_key_first($genre_count);
}


/* GET RECOMMENDED MOVIES */

$recommended_movies = null;

if($favorite_genre){

$rec_sql = "
SELECT *
FROM movies
WHERE genre LIKE ?
ORDER BY release_year DESC
LIMIT 6
";

$like_genre = "%".$favorite_genre."%";

$stmt_rec = $conn->prepare($rec_sql);
$stmt_rec->bind_param("s",$like_genre);
$stmt_rec->execute();

$recommended_movies = $stmt_rec->get_result();

}

?>

<div class="row mb-4">
    <div class="col">
        <h2>My Watchlist</h2>
    </div>
</div>

<div class="card shadow">
<div class="card-body p-0">

<div class="table-responsive">

<table class="table table-hover mb-0">

<thead class="table-light">
<tr>
<th>Poster</th>
<th>Movie Title</th>
<th>Genre</th>
<th>Status</th>
<th class="text-end">Actions</th>
</tr>
</thead>

<tbody>

<?php if ($result->num_rows > 0): ?>

<?php while($row = $result->fetch_assoc()): ?>

<tr class="align-middle">

<td style="width:80px;">
<img src="<?php echo htmlspecialchars($row['poster_url']); ?>"
class="img-thumbnail"
style="width:60px;height:90px;object-fit:cover;"
onerror="this.src='https://via.placeholder.com/60x90?text=No+Poster'">
</td>

<td>
<strong><?php echo htmlspecialchars($row['title']); ?></strong>
</td>

<td>
<?php echo htmlspecialchars($row['genre']); ?>
</td>

<td>

<?php if ($row['status'] == 'planned'): ?>
<span class="badge bg-warning text-dark">Planned</span>

<?php elseif ($row['status'] == 'watching'): ?>
<span class="badge bg-primary">Watching</span>

<?php elseif ($row['status'] == 'completed'): ?>
<span class="badge bg-success">Completed</span>
<?php endif; ?>

</td>

<td class="text-end">

<div class="btn-group" role="group">

<form action="update_status.php" method="POST" class="me-1">
<input type="hidden" name="watchlist_id" value="<?php echo $row['watchlist_id']; ?>">
<input type="hidden" name="status" value="planned">
<button type="submit" class="btn btn-sm btn-outline-warning">Planned</button>
</form>

<form action="update_status.php" method="POST" class="me-1">
<input type="hidden" name="watchlist_id" value="<?php echo $row['watchlist_id']; ?>">
<input type="hidden" name="status" value="watching">
<button type="submit" class="btn btn-sm btn-outline-primary">Watching</button>
</form>

<form action="update_status.php" method="POST" class="me-1">
<input type="hidden" name="watchlist_id" value="<?php echo $row['watchlist_id']; ?>">
<input type="hidden" name="status" value="completed">
<button type="submit" class="btn btn-sm btn-outline-success">Completed</button>
</form>

<form action="remove_from_watchlist.php" method="POST">
<input type="hidden" name="watchlist_id" value="<?php echo $row['watchlist_id']; ?>">
<button type="submit"
class="btn btn-sm btn-outline-danger"
onclick="return confirm('Remove from watchlist?')">
Remove
</button>
</form>

</div>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="5" class="text-center py-4 text-muted">
Your watchlist is empty. <a href="movies.php">Add some movies!</a>
</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>
</div>


<?php if($recommended_movies && $recommended_movies->num_rows > 0): ?>

<div class="row mt-5">

<div class="col">

<h3>Recommended Movies</h3>
<p class="text-muted">
Based on your favorite genre: <?php echo htmlspecialchars($favorite_genre); ?>
</p>

</div>

</div>

<div class="row row-cols-1 row-cols-md-3 row-cols-lg-6 g-4">

<?php while($movie = $recommended_movies->fetch_assoc()): ?>

<div class="col">

<div class="card h-100 shadow-sm">

<img src="<?php echo htmlspecialchars($movie['poster_url']); ?>"
class="card-img-top"
onerror="this.src='https://via.placeholder.com/400x600?text=No+Poster'">

<div class="card-body">

<h6 class="card-title">
<?php echo htmlspecialchars($movie['title']); ?>
</h6>

<p class="text-muted small">
<?php echo $movie['release_year']; ?>
</p>

</div>

</div>

</div>

<?php endwhile; ?>

</div>

<?php endif; ?>

<?php
$stmt->close();
include 'includes/footer.php';
?>