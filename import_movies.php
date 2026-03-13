<?php
require_once 'config/database.php';

$api_key = "1beb9872f9776c8a6d5562110ad7d92d";

/* TMDB GENRE MAP */
$genres_map = [
28 => "Action",
12 => "Adventure",
16 => "Animation",
35 => "Comedy",
80 => "Crime",
99 => "Documentary",
18 => "Drama",
10751 => "Family",
14 => "Fantasy",
36 => "History",
27 => "Horror",
10402 => "Music",
9648 => "Mystery",
10749 => "Romance",
878 => "Sci-Fi",
10770 => "TV Movie",
53 => "Thriller",
10752 => "War",
37 => "Western"
];

for ($page = 1; $page <= 10; $page++) {

    $url = "https://api.themoviedb.org/3/movie/popular?api_key=$api_key&language=en-US&page=$page";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (!isset($data['results'])) {
        die("Error fetching movies from API.");
    }

    foreach ($data['results'] as $movie) {

        $movie_id = $movie['id'];

        $title = $conn->real_escape_string($movie['title']);

        $description = $conn->real_escape_string($movie['overview']);

        $release_year = !empty($movie['release_date']) ? substr($movie['release_date'],0,4) : NULL;

        $poster = !empty($movie['poster_path'])
            ? "https://image.tmdb.org/t/p/w500".$movie['poster_path']
            : "";

        /* Convert genre_ids to text */
        $movie_genres = [];

        if (!empty($movie['genre_ids'])) {

            foreach ($movie['genre_ids'] as $gid) {

                if (isset($genres_map[$gid])) {
                    $movie_genres[] = $genres_map[$gid];
                }

            }

        }

        $genre_string = implode(", ", $movie_genres);

        $sql = "INSERT IGNORE INTO movies
        (movie_id, title, description, genre, release_year, poster_url)
        VALUES
        ('$movie_id', '$title', '$description', '$genre_string', '$release_year', '$poster')";

        $conn->query($sql);
    }
}

echo "<h2>Movies imported successfully!</h2>";
?>