<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Get movie ID
$movieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($movieId <= 0) {
    header("Location: index.php");
    exit;
}

// Get movie details with average rating
$stmt = $conn->prepare("SELECT m.*, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count 
                         FROM movies m 
                         LEFT JOIN reviews r ON m.id = r.movie_id 
                         WHERE m.id = ? 
                         GROUP BY m.id");
$stmt->bind_param("i", $movieId);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();

if (!$movie) {
    header("Location: index.php");
    exit;
}

$pageTitle = $movie['title'];

// Handle review submission
$reviewMsg = '';
$reviewError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        $reviewError = "You must be logged in to write a review.";
    } else {
        $rating = (int)$_POST['rating'];
        $text = trim($_POST['review_text']);

        if ($rating < 1 || $rating > 5) {
            $reviewError = "Please select a rating between 1 and 5.";
        } elseif (strlen($text) < 10) {
            $reviewError = "Your review must be at least 10 characters long.";
        } else {
            $stmt = $conn->prepare("INSERT INTO reviews (user_id, movie_id, rating, review_text) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $_SESSION['user_id'], $movieId, $rating, $text);
            if ($stmt->execute()) {
                $reviewMsg = "Your review has been saved!";
                // Refresh page to show new review
                header("Location: movie.php?id=" . $movieId . "&success=1");
                exit;
            } else {
                $reviewError = "Something went wrong. Please try again.";
            }
        }
    }
}

if (isset($_GET['success'])) {
    $reviewMsg = "Your review has been saved!";
}

// Handle add to favorites
$favMsg = '';
if (isset($_POST['add_favorite']) && isLoggedIn()) {
    $stmt = $conn->prepare("INSERT IGNORE INTO favorites (user_id, movie_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $_SESSION['user_id'], $movieId);
    $stmt->execute();
    $favMsg = ($stmt->affected_rows > 0) ? "Added to your favorites!" : "Already in your favorites.";
}

// Check if already in favorites
$isFavorite = false;
if (isLoggedIn()) {
    $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND movie_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $movieId);
    $stmt->execute();
    $isFavorite = $stmt->get_result()->num_rows > 0;
}

// Get all reviews for this movie
$stmt = $conn->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.movie_id = ? ORDER BY r.created_at DESC");
$stmt->bind_param("i", $movieId);
$stmt->execute();
$reviews = $stmt->get_result();

require_once 'includes/header.php';
?>

<div class="container mt-4 mb-5">

    <!-- BREADCRUMB -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="index.php?genre=<?php echo urlencode($movie['genre']); ?>"><?php echo htmlspecialchars($movie['genre']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($movie['title']); ?></li>
        </ol>
    </nav>

    <!-- MOVIE INFO -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster-detail">
        </div>
        <div class="col-md-8">
            <span class="badge mb-2" style="background-color:var(--cr-gold); color:#0D0D0D;"><?php echo htmlspecialchars($movie['genre']); ?></span>
            <h1><?php echo htmlspecialchars($movie['title']); ?></h1>
            <p class="movie-meta">
                <?php echo $movie['year']; ?> &middot; Directed by <a href="#"><?php echo htmlspecialchars($movie['director']); ?></a>
            </p>
            <div class="stars mb-3">
                <?php
                $avg = round($movie['avg_rating'], 1);
                $avgRound = round($avg);
                for ($i = 1; $i <= 5; $i++) {
                    echo $i <= $avgRound ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star empty"></i>';
                }
                ?>
                <strong class="ms-2"><?php echo number_format($avg, 1); ?></strong>
                <small class="text-muted">(<?php echo $movie['review_count']; ?> reviews)</small>
            </div>

            <p style="line-height:1.7;"><?php echo htmlspecialchars($movie['description']); ?></p>

            <h5 style="color:var(--cr-gold); font-family:'Playfair Display',serif;">Cast</h5>
            <div class="mb-3">
                <?php
                $castList = explode(',', $movie['cast_members']);
                foreach ($castList as $actor) {
                    echo '<span class="cast-badge">' . htmlspecialchars(trim($actor)) . '</span>';
                }
                ?>
            </div>

            <!-- FAVORITE BUTTON -->
            <?php if ($favMsg): ?>
                <div class="alert alert-success py-2"><?php echo $favMsg; ?></div>
            <?php endif; ?>

            <div class="d-flex gap-2">
                <?php if (isLoggedIn()): ?>
                    <form method="POST">
                        <button type="submit" name="add_favorite" class="btn btn-outline-gold">
                            <i class="bi bi-heart<?php echo $isFavorite ? '-fill' : ''; ?> me-1"></i>
                            <?php echo $isFavorite ? 'In Favorites' : 'Add to Favorites'; ?>
                        </button>
                    </form>
                <?php endif; ?>
                <a href="index.php" class="btn btn-outline-gold"><i class="bi bi-arrow-left me-1"></i> Back</a>
            </div>
        </div>
    </div>

    <!-- WRITE REVIEW -->
    <div class="mb-5">
        <h3 class="section-title"><i class="bi bi-pencil-square"></i> Write a Review</h3>

        <?php if ($reviewMsg): ?>
            <div class="alert alert-success"><?php echo $reviewMsg; ?></div>
        <?php endif; ?>
        <?php if ($reviewError): ?>
            <div class="alert alert-danger"><?php echo $reviewError; ?></div>
        <?php endif; ?>

        <?php if (isLoggedIn()): ?>
            <div class="review-card">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label" style="color:var(--cr-text-muted);">Your Rating</label>
                        <div class="star-rating-input" id="starRating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star" data-value="<?php echo $i; ?>" onclick="setRating(<?php echo $i; ?>)"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:var(--cr-text-muted);">Your Review</label>
                        <textarea class="form-control" name="review_text" rows="4" placeholder="Share your thoughts about this film..." style="background-color:var(--cr-bg); border-color:var(--cr-border); color:var(--cr-text);"></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="btn btn-gold">Submit Review</button>
                </form>
            </div>
        <?php else: ?>
            <div class="review-card text-center py-4">
                <p class="text-muted mb-2">You need to be logged in to write a review.</p>
                <a href="login.php" class="btn btn-gold">Login</a>
                <a href="register.php" class="btn btn-outline-gold ms-2">Register</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- USER REVIEWS -->
    <div>
        <h3 class="section-title"><i class="bi bi-chat-dots-fill"></i> User Reviews</h3>

        <?php if ($reviews->num_rows === 0): ?>
            <p class="text-muted">No reviews yet. Be the first to share your opinion!</p>
        <?php else: ?>
            <?php while ($review = $reviews->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="review-avatar">
                                <?php echo strtoupper(substr($review['full_name'], 0, 1)); ?>
                            </div>
                            <span class="reviewer-name"><?php echo htmlspecialchars($review['full_name']); ?></span>
                        </div>
                        <span class="review-date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                    </div>
                    <div class="stars mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?> <?php echo $i > $review['rating'] ? 'empty' : ''; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="mb-0"><?php echo htmlspecialchars($review['review_text']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function setRating(value) {
    document.getElementById('ratingInput').value = value;
    const stars = document.querySelectorAll('#starRating .bi');
    stars.forEach((star, index) => {
        if (index < value) {
            star.classList.remove('bi-star');
            star.classList.add('bi-star-fill', 'active');
        } else {
            star.classList.remove('bi-star-fill', 'active');
            star.classList.add('bi-star');
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
