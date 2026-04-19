<?php
$pageTitle = 'Edit Review';
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$reviewId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get the review (only if it belongs to the current user)
$stmt = $conn->prepare("SELECT r.*, m.title as movie_title FROM reviews r JOIN movies m ON r.movie_id = m.id WHERE r.id = ? AND r.user_id = ?");
$stmt->bind_param("ii", $reviewId, $_SESSION['user_id']);
$stmt->execute();
$review = $stmt->get_result()->fetch_assoc();

if (!$review) {
    header("Location: account.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $text = trim($_POST['review_text']);

    if ($rating < 1 || $rating > 5) {
        $error = "Please select a rating between 1 and 5.";
    } elseif (strlen($text) < 10) {
        $error = "Your review must be at least 10 characters long.";
    } else {
        $stmt = $conn->prepare("UPDATE reviews SET rating = ?, review_text = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("isii", $rating, $text, $reviewId, $_SESSION['user_id']);
        if ($stmt->execute()) {
            header("Location: account.php?tab=reviews");
            exit;
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="account.php">My Account</a></li>
            <li class="breadcrumb-item active">Edit Review</li>
        </ol>
    </nav>

    <div class="review-card" style="max-width:700px;">
        <h3 class="mb-1">Edit Review</h3>
        <p class="text-muted mb-3">For: <strong style="color:var(--cr-gold);"><?php echo htmlspecialchars($review['movie_title']); ?></strong></p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label" style="color:var(--cr-text-muted);">Your Rating</label>
                <div class="star-rating-input" id="starRating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill active' : ''; ?>" data-value="<?php echo $i; ?>" onclick="setRating(<?php echo $i; ?>)"></i>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="<?php echo $review['rating']; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" style="color:var(--cr-text-muted);">Your Review</label>
                <textarea class="form-control" name="review_text" rows="5" style="background-color:var(--cr-bg); border-color:var(--cr-border); color:var(--cr-text);"><?php echo htmlspecialchars($review['review_text']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-gold">Save Changes</button>
            <a href="account.php?tab=reviews" class="btn btn-outline-gold ms-2">Cancel</a>
        </form>
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
