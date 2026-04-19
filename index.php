<?php
$pageTitle = 'Home';
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Handle search and genre filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre = isset($_GET['genre']) ? trim($_GET['genre']) : '';

$sql = "SELECT m.*, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count 
        FROM movies m 
        LEFT JOIN reviews r ON m.id = r.movie_id";

$conditions = [];
$params = [];
$types = '';

if ($search !== '') {
    $conditions[] = "m.title LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= 's';
}
if ($genre !== '') {
    $conditions[] = "m.genre = ?";
    $params[] = $genre;
    $types .= 's';
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " GROUP BY m.id ORDER BY m.year DESC, m.title ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$movies = $stmt->get_result();

// Get all genres for the filter
$genres = $conn->query("SELECT DISTINCT genre FROM movies ORDER BY genre ASC");

require_once 'includes/header.php';
?>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="container">
        <h1>Discover. Review. <span>Remember.</span></h1>
        <p>Your personal cinema journal. Browse our collection, share your thoughts, and find your next favourite film.</p>

        <!-- SEARCH BAR -->
        <form method="GET" action="index.php" class="search-bar">
            <div class="row g-2">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text" style="background-color:#1A1A1A; border-color:#2A2A2A; color:#D4A843;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" placeholder="Search movies..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="genre">
                        <option value="">All Genres</option>
                        <?php while ($g = $genres->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($g['genre']); ?>" <?php echo ($genre === $g['genre']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($g['genre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-gold w-100">Search</button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- MOVIE GRID -->
<section class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-title"><i class="bi bi-play-circle-fill"></i> Featured Movies</h2>
        <span class="text-muted"><?php echo $movies->num_rows; ?> films found</span>
    </div>

    <?php if ($movies->num_rows === 0): ?>
        <div class="alert alert-danger text-center">
            No movies found. Try a different search or genre.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php while ($movie = $movies->fetch_assoc()): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="movie.php?id=<?php echo $movie['id']; ?>" class="text-decoration-none">
                        <div class="movie-card card">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($movie['poster']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                                <span class="badge position-absolute top-0 end-0 m-2"><?php echo htmlspecialchars($movie['genre']); ?></span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                <p class="card-text"><?php echo $movie['year']; ?> &middot; <?php echo htmlspecialchars($movie['director']); ?></p>
                                <div class="stars">
                                    <?php
                                    $avg = round($movie['avg_rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $avg ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star empty"></i>';
                                    }
                                    ?>
                                    <small class="text-muted ms-1">(<?php echo $movie['review_count']; ?>)</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
