<?php
$pageTitle = 'My Account';
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user = getCurrentUser();
$msg = '';
$msgType = '';

// Handle profile update
if (isset($_POST['update_profile'])) {
    $newName = trim($_POST['full_name'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $newPass = $_POST['new_password'] ?? '';

    if (empty($newName) || empty($newEmail)) {
        $msg = "Name and email cannot be empty.";
        $msgType = 'danger';
    } else {
        $sql = "UPDATE users SET full_name = ?, email = ?";
        $params = [$newName, $newEmail];
        $types = "ss";

        if (!empty($newPass)) {
            if (strlen($newPass) < 6) {
                $msg = "Password must be at least 6 characters.";
                $msgType = 'danger';
            } else {
                $sql .= ", password = ?";
                $params[] = password_hash($newPass, PASSWORD_DEFAULT);
                $types .= "s";
            }
        }

        if ($msgType !== 'danger') {
            $sql .= " WHERE id = ?";
            $params[] = $_SESSION['user_id'];
            $types .= "i";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $newName;
                $msg = "Profile updated!";
                $msgType = 'success';
                $user = getCurrentUser(); // refresh
            } else {
                $msg = "Something went wrong.";
                $msgType = 'danger';
            }
        }
    }
}

// Handle delete review
if (isset($_POST['delete_review'])) {
    $reviewId = (int)$_POST['review_id'];
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $reviewId, $_SESSION['user_id']);
    $stmt->execute();
    $msg = "Review deleted.";
    $msgType = 'success';
}

// Handle remove favorite
if (isset($_POST['remove_favorite'])) {
    $movieId = (int)$_POST['movie_id'];
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $movieId);
    $stmt->execute();
    $msg = "Removed from favorites.";
    $msgType = 'success';
}

// Get user reviews
$stmt = $conn->prepare("SELECT r.*, m.title as movie_title, m.poster as movie_poster 
                         FROM reviews r 
                         JOIN movies m ON r.movie_id = m.id 
                         WHERE r.user_id = ? 
                         ORDER BY r.created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$userReviews = $stmt->get_result();

// Get user favorites
$stmt = $conn->prepare("SELECT f.*, m.title, m.poster, m.year, m.genre 
                         FROM favorites f 
                         JOIN movies m ON f.movie_id = m.id 
                         WHERE f.user_id = ? 
                         ORDER BY f.created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$userFavorites = $stmt->get_result();

// Active tab
$activeTab = $_GET['tab'] ?? 'reviews';

require_once 'includes/header.php';
?>

<div class="container mt-4 mb-5">

    <!-- ACCOUNT HEADER -->
    <div class="account-header">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="review-avatar" style="width:60px; height:60px; font-size:1.5rem;">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
            </div>
            <div class="col">
                <h3 class="mb-0"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                <small class="text-muted">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></small>
            </div>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?php echo $msgType; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>

    <!-- TABS -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'reviews' ? 'active' : ''; ?>" href="account.php?tab=reviews">
                <i class="bi bi-pencil-square me-1"></i> My Reviews
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'favorites' ? 'active' : ''; ?>" href="account.php?tab=favorites">
                <i class="bi bi-heart me-1"></i> My Favorites
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'settings' ? 'active' : ''; ?>" href="account.php?tab=settings">
                <i class="bi bi-gear me-1"></i> Settings
            </a>
        </li>
    </ul>

    <!-- TAB: MY REVIEWS -->
    <?php if ($activeTab === 'reviews'): ?>
        <?php if ($userReviews->num_rows === 0): ?>
            <p class="text-muted">You have not written any reviews yet. <a href="index.php">Browse movies</a> to get started.</p>
        <?php else: ?>
            <?php while ($rev = $userReviews->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1">
                                <a href="movie.php?id=<?php echo $rev['movie_id']; ?>"><?php echo htmlspecialchars($rev['movie_title']); ?></a>
                            </h5>
                            <div class="stars mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $rev['rating'] ? '-fill' : ''; ?> <?php echo $i > $rev['rating'] ? 'empty' : ''; ?>"></i>
                                <?php endfor; ?>
                                <small class="text-muted ms-2"><?php echo date('d M Y', strtotime($rev['created_at'])); ?></small>
                            </div>
                            <p class="mb-0"><?php echo htmlspecialchars($rev['review_text']); ?></p>
                        </div>
                        <div class="d-flex gap-2 ms-3">
                            <a href="edit_review.php?id=<?php echo $rev['id']; ?>" class="btn btn-sm btn-outline-gold">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" onsubmit="return confirm('Delete this review?');">
                                <input type="hidden" name="review_id" value="<?php echo $rev['id']; ?>">
                                <button type="submit" name="delete_review" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    <?php endif; ?>

    <!-- TAB: MY FAVORITES -->
    <?php if ($activeTab === 'favorites'): ?>
        <?php if ($userFavorites->num_rows === 0): ?>
            <p class="text-muted">You have no favorites yet. <a href="index.php">Browse movies</a> and add some!</p>
        <?php else: ?>
            <div class="row g-4">
                <?php while ($fav = $userFavorites->fetch_assoc()): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="movie-card card">
                            <a href="movie.php?id=<?php echo $fav['movie_id']; ?>">
                                <img src="<?php echo htmlspecialchars($fav['poster']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($fav['title']); ?>">
                            </a>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($fav['title']); ?></h5>
                                <p class="card-text"><?php echo $fav['year']; ?> &middot; <?php echo htmlspecialchars($fav['genre']); ?></p>
                                <form method="POST">
                                    <input type="hidden" name="movie_id" value="<?php echo $fav['movie_id']; ?>">
                                    <button type="submit" name="remove_favorite" class="btn btn-sm btn-outline-danger w-100">
                                        <i class="bi bi-heart-break me-1"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- TAB: SETTINGS -->
    <?php if ($activeTab === 'settings'): ?>
        <div class="review-card" style="max-width:600px;">
            <h4 class="mb-3">Update Profile</h4>
            <form method="POST">
                <div class="mb-3">
                    <label for="full_name" class="form-label" style="color:var(--cr-text-muted);">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($user['full_name']); ?>"
                           style="background-color:var(--cr-bg); border-color:var(--cr-border); color:var(--cr-text);" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label" style="color:var(--cr-text-muted);">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>"
                           style="background-color:var(--cr-bg); border-color:var(--cr-border); color:var(--cr-text);" required>
                </div>
                <div class="mb-4">
                    <label for="new_password" class="form-label" style="color:var(--cr-text-muted);">New Password <small>(leave blank to keep current)</small></label>
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="At least 6 characters"
                           style="background-color:var(--cr-bg); border-color:var(--cr-border); color:var(--cr-text);">
                </div>
                <button type="submit" name="update_profile" class="btn btn-gold">Save Changes</button>
            </form>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
