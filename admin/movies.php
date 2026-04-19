<?php
// ============================================
// CineReview Admin Panel - Manage Movies
// Simple password-protected admin area
// ============================================
session_start();

define('ADMIN_PASSWORD', 'cinereview2024'); // Change this before going live

// Admin login check
if (!isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['admin_password']) && $_POST['admin_password'] === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        // Show login form if not authenticated
        if (isset($_POST['admin_password'])) {
            $loginError = "Incorrect password.";
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login - CineReview</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
            <style>
                :root { --cr-gold: #D4A843; }
                body { background: #0D0D0D; color: #F5F0E8; font-family: 'Source Sans 3', sans-serif; }
                .login-box { max-width: 380px; margin: 8rem auto; background: #141414; border: 1px solid #2A2A2A; border-radius: 10px; padding: 2.5rem; }
                h2 { font-family: 'Playfair Display', serif; color: var(--cr-gold); }
                .form-control { background: #1A1A1A; border-color: #2A2A2A; color: #F5F0E8; }
                .form-control:focus { border-color: var(--cr-gold); box-shadow: 0 0 0 0.2rem rgba(212,168,67,0.15); }
                .btn-gold { background: var(--cr-gold); color: #0D0D0D; font-weight: 600; border: none; }
                .btn-gold:hover { background: #E8C36A; color: #0D0D0D; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h2 class="text-center mb-1"><i class="bi bi-shield-lock"></i> Admin</h2>
                <p class="text-center text-muted mb-4">CineReview Admin Panel</p>
                <?php if (isset($loginError)): ?>
                    <div class="alert alert-danger py-2"><?php echo $loginError; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted">Admin Password</label>
                        <input type="password" class="form-control" name="admin_password" placeholder="Enter password" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-gold w-100">Enter</button>
                </form>
                <p class="text-center mt-3"><a href="../index.php" style="color:#8A8578;">← Back to CineReview</a></p>
            </div>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        </body>
        </html>
        <?php
        exit;
    }
}

// --- Database connection ---
require_once '../includes/config.php';

$msg = '';
$msgType = '';

// --- LOGOUT ---
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header("Location: movies.php");
    exit;
}

// --- ADD MOVIE ---
if (isset($_POST['add_movie'])) {
    $title      = trim($_POST['title']);
    $year       = (int)$_POST['year'];
    $genre      = trim($_POST['genre']);
    $director   = trim($_POST['director']);
    $cast       = trim($_POST['cast_members']);
    $desc       = trim($_POST['description']);
    $poster     = trim($_POST['poster']);

    if (empty($title) || empty($genre) || empty($director) || empty($cast) || empty($desc) || empty($poster)) {
        $msg = "All fields are required.";
        $msgType = 'danger';
    } else {
        $stmt = $conn->prepare("INSERT INTO movies (title, year, genre, director, cast_members, description, poster) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissss s", $title, $year, $genre, $director, $cast, $desc, $poster);
        // fix bind: year is int
        $stmt = $conn->prepare("INSERT INTO movies (title, year, genre, director, cast_members, description, poster) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siissss", $title, $year, $genre, $director, $cast, $desc, $poster);
        if ($stmt->execute()) {
            $msg = "Movie \"$title\" added successfully!";
            $msgType = 'success';
        } else {
            $msg = "Database error: " . $conn->error;
            $msgType = 'danger';
        }
    }
}

// --- EDIT MOVIE (load) ---
$editMovie = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM movies WHERE id = $editId");
    $editMovie = $res ? $res->fetch_assoc() : null;
}

// --- UPDATE MOVIE ---
if (isset($_POST['update_movie'])) {
    $id       = (int)$_POST['movie_id'];
    $title    = trim($_POST['title']);
    $year     = (int)$_POST['year'];
    $genre    = trim($_POST['genre']);
    $director = trim($_POST['director']);
    $cast     = trim($_POST['cast_members']);
    $desc     = trim($_POST['description']);
    $poster   = trim($_POST['poster']);

    $stmt = $conn->prepare("UPDATE movies SET title=?, year=?, genre=?, director=?, cast_members=?, description=?, poster=? WHERE id=?");
    $stmt->bind_param("siissssi", $title, $year, $genre, $director, $cast, $desc, $poster, $id);
    if ($stmt->execute()) {
        $msg = "Movie updated successfully!";
        $msgType = 'success';
        $editMovie = null;
    } else {
        $msg = "Update error: " . $conn->error;
        $msgType = 'danger';
    }
}

// --- DELETE MOVIE ---
if (isset($_POST['delete_movie'])) {
    $id = (int)$_POST['movie_id'];
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = "Movie deleted.";
        $msgType = 'success';
    }
}

// --- GET ALL MOVIES ---
$movies = $conn->query("SELECT * FROM movies ORDER BY id DESC");

// --- ALL GENRES for datalist ---
$genreOptions = ['Action', 'Comedy', 'Crime', 'Drama', 'Fantasy', 'Horror', 'Romance', 'Sci-Fi', 'Thriller', 'Animation', 'Documentary', 'Adventure'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Movies | CineReview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --cr-bg:       #0D0D0D;
            --cr-bg-light: #1A1A1A;
            --cr-bg-card:  #141414;
            --cr-gold:     #D4A843;
            --cr-gold-h:   #E8C36A;
            --cr-text:     #F5F0E8;
            --cr-muted:    #8A8578;
            --cr-border:   #2A2A2A;
        }
        * { box-sizing: border-box; }
        body { background: var(--cr-bg); color: var(--cr-text); font-family: 'Source Sans 3', sans-serif; min-height: 100vh; }

        /* --- Sidebar Layout --- */
        .admin-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 220px; min-width: 220px; background: var(--cr-bg-light);
            border-right: 1px solid var(--cr-border); padding: 1.5rem 0;
            position: sticky; top: 0; height: 100vh; overflow-y: auto;
        }
        .sidebar-brand {
            font-family: 'Playfair Display', serif; color: var(--cr-gold);
            font-size: 1.2rem; padding: 0 1.5rem 1.5rem; border-bottom: 1px solid var(--cr-border);
            margin-bottom: 1rem; display: block;
        }
        .sidebar-brand i { margin-right: 0.5rem; }
        .sidebar a {
            display: flex; align-items: center; gap: 0.6rem;
            padding: 0.7rem 1.5rem; color: var(--cr-muted);
            text-decoration: none; font-weight: 500; transition: all 0.2s;
        }
        .sidebar a:hover, .sidebar a.active {
            color: var(--cr-gold); background: rgba(212,168,67,0.07);
            border-left: 3px solid var(--cr-gold); padding-left: calc(1.5rem - 3px);
        }
        .sidebar .sidebar-divider { border-color: var(--cr-border); margin: 0.8rem 1rem; }

        /* --- Main Content --- */
        .admin-main { flex: 1; padding: 2rem; overflow-y: auto; }
        .admin-title { font-family: 'Playfair Display', serif; color: var(--cr-text); font-size: 1.8rem; margin-bottom: 0.3rem; }
        .admin-subtitle { color: var(--cr-muted); margin-bottom: 2rem; font-size: 0.95rem; }

        /* --- Cards --- */
        .admin-card {
            background: var(--cr-bg-card); border: 1px solid var(--cr-border);
            border-radius: 10px; padding: 1.5rem; margin-bottom: 2rem;
        }
        .admin-card h5 { font-family: 'Playfair Display', serif; color: var(--cr-gold); margin-bottom: 1.2rem; }

        /* --- Form Controls --- */
        .form-control, .form-select {
            background: var(--cr-bg-light); border: 1px solid var(--cr-border);
            color: var(--cr-text); border-radius: 6px;
        }
        .form-control:focus, .form-select:focus {
            background: var(--cr-bg-light); color: var(--cr-text);
            border-color: var(--cr-gold); box-shadow: 0 0 0 0.2rem rgba(212,168,67,0.15);
        }
        .form-label { color: var(--cr-muted); font-weight: 500; font-size: 0.9rem; margin-bottom: 0.3rem; }
        .form-select option { background: var(--cr-bg-light); }
        textarea.form-control { resize: vertical; min-height: 90px; }

        /* --- Buttons --- */
        .btn-gold { background: var(--cr-gold); color: #0D0D0D; font-weight: 600; border: none; transition: all 0.2s; }
        .btn-gold:hover { background: var(--cr-gold-h); color: #0D0D0D; transform: translateY(-1px); }
        .btn-outline-gold { border: 1px solid var(--cr-gold); color: var(--cr-gold); background: transparent; font-weight: 600; transition: all 0.2s; }
        .btn-outline-gold:hover { background: var(--cr-gold); color: #0D0D0D; }
        .btn-outline-danger { border-color: rgba(220,53,69,0.5); color: #f1948a; }
        .btn-outline-danger:hover { background: rgba(220,53,69,0.15); color: #f1948a; border-color: rgba(220,53,69,0.5); }

        /* --- Movie Table --- */
        .movie-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .movie-table thead th {
            background: var(--cr-bg-light); color: var(--cr-muted);
            font-weight: 600; font-size: 0.82rem; text-transform: uppercase;
            letter-spacing: 0.05em; padding: 0.8rem 1rem;
            border-bottom: 1px solid var(--cr-border);
        }
        .movie-table tbody tr { transition: background 0.15s; }
        .movie-table tbody tr:hover { background: rgba(255,255,255,0.02); }
        .movie-table tbody td {
            padding: 0.85rem 1rem; border-bottom: 1px solid var(--cr-border);
            vertical-align: middle; font-size: 0.92rem;
        }
        .movie-table tbody tr:last-child td { border-bottom: none; }
        .poster-thumb { width: 42px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid var(--cr-border); }
        .genre-badge {
            background: rgba(212,168,67,0.12); color: var(--cr-gold);
            border: 1px solid rgba(212,168,67,0.3); padding: 0.2rem 0.6rem;
            border-radius: 20px; font-size: 0.78rem; font-weight: 600; white-space: nowrap;
        }
        .action-btns { display: flex; gap: 0.4rem; }

        /* --- Alerts --- */
        .alert-success { background: rgba(40,167,69,0.12); border-color: rgba(40,167,69,0.25); color: #7dcea0; }
        .alert-danger  { background: rgba(220,53,69,0.12); border-color: rgba(220,53,69,0.25); color: #f1948a; }

        /* --- Poster Preview --- */
        #posterPreview {
            width: 80px; height: 110px; object-fit: cover;
            border-radius: 6px; border: 1px solid var(--cr-border);
            display: none; margin-top: 0.5rem;
        }
        #posterPreview.visible { display: block; }

        /* --- Stats bar --- */
        .stat-box {
            background: var(--cr-bg-light); border: 1px solid var(--cr-border);
            border-radius: 8px; padding: 1rem 1.4rem; text-align: center;
        }
        .stat-box .stat-number { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--cr-gold); }
        .stat-box .stat-label { color: var(--cr-muted); font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em; }

        /* --- Responsive --- */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .admin-main { padding: 1rem; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <a href="movies.php" class="sidebar-brand"><i class="bi bi-film"></i> CineReview</a>
        <a href="movies.php" class="active"><i class="bi bi-camera-reels"></i> Movies</a>
        <hr class="sidebar-divider">
        <a href="../index.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> View Site</a>
        <a href="movies.php?logout=1"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </aside>

    <!-- MAIN -->
    <main class="admin-main">

        <h1 class="admin-title">Manage Movies</h1>
        <p class="admin-subtitle">Add, edit, or remove movies from the CineReview catalogue.</p>

        <?php if ($msg): ?>
            <div class="alert alert-<?php echo $msgType; ?> d-flex align-items-center gap-2">
                <i class="bi bi-<?php echo $msgType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <!-- STATS ROW -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $movies->num_rows; ?></div>
                    <div class="stat-label">Total Movies</div>
                </div>
            </div>
            <?php
            $movies->data_seek(0);
            $genreCount = [];
            while ($m = $movies->fetch_assoc()) {
                $genreCount[$m['genre']] = ($genreCount[$m['genre']] ?? 0) + 1;
            }
            $movies->data_seek(0);
            ?>
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <div class="stat-number"><?php echo count($genreCount); ?></div>
                    <div class="stat-label">Genres</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <?php $totalReviews = $conn->query("SELECT COUNT(*) as c FROM reviews")->fetch_assoc()['c']; ?>
                    <div class="stat-number"><?php echo $totalReviews; ?></div>
                    <div class="stat-label">Reviews</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <?php $totalUsers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c']; ?>
                    <div class="stat-number"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Users</div>
                </div>
            </div>
        </div>

        <!-- ADD / EDIT FORM -->
        <div class="admin-card">
            <h5>
                <i class="bi bi-<?php echo $editMovie ? 'pencil-square' : 'plus-circle'; ?> me-2"></i>
                <?php echo $editMovie ? 'Edit Movie: ' . htmlspecialchars($editMovie['title']) : 'Add New Movie'; ?>
            </h5>

            <form method="POST" id="movieForm">
                <?php if ($editMovie): ?>
                    <input type="hidden" name="movie_id" value="<?php echo $editMovie['id']; ?>">
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" placeholder="e.g. Inception"
                               value="<?php echo htmlspecialchars($editMovie['title'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Year *</label>
                        <input type="number" class="form-control" name="year" min="1888" max="2030"
                               placeholder="e.g. 2024" value="<?php echo htmlspecialchars($editMovie['year'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Genre *</label>
                        <input type="text" class="form-control" name="genre" list="genreList"
                               placeholder="e.g. Sci-Fi" value="<?php echo htmlspecialchars($editMovie['genre'] ?? ''); ?>" required>
                        <datalist id="genreList">
                            <?php foreach ($genreOptions as $g): ?>
                                <option value="<?php echo $g; ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Director *</label>
                        <input type="text" class="form-control" name="director" placeholder="e.g. Christopher Nolan"
                               value="<?php echo htmlspecialchars($editMovie['director'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cast Members * <small style="color:var(--cr-muted)">(comma-separated)</small></label>
                        <input type="text" class="form-control" name="cast_members"
                               placeholder="e.g. Leonardo DiCaprio, Cillian Murphy"
                               value="<?php echo htmlspecialchars($editMovie['cast_members'] ?? ''); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3"
                                  placeholder="Write a short synopsis of the movie..."><?php echo htmlspecialchars($editMovie['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Poster URL * <small style="color:var(--cr-muted)">(direct image link)</small></label>
                        <input type="url" class="form-control" name="poster" id="posterUrl"
                               placeholder="https://example.com/poster.jpg"
                               value="<?php echo htmlspecialchars($editMovie['poster'] ?? ''); ?>"
                               oninput="previewPoster(this.value)" required>
                        <img id="posterPreview" src="" alt="Poster preview" <?php echo !empty($editMovie['poster']) ? 'class="visible" src="' . htmlspecialchars($editMovie['poster']) . '"' : ''; ?>>
                        <small style="color:var(--cr-muted);">Tip: Use a direct image URL. Aspect ratio 2:3 recommended.</small>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <?php if ($editMovie): ?>
                        <button type="submit" name="update_movie" class="btn btn-gold">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                        <a href="movies.php" class="btn btn-outline-gold">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_movie" class="btn btn-gold">
                            <i class="bi bi-plus-lg me-1"></i> Add Movie
                        </button>
                        <button type="reset" class="btn btn-outline-gold" onclick="document.getElementById('posterPreview').className=''">
                            Clear
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- MOVIE LIST TABLE -->
        <div class="admin-card">
            <h5><i class="bi bi-table me-2"></i> All Movies (<?php echo $movies->num_rows; ?>)</h5>

            <?php if ($movies->num_rows === 0): ?>
                <p class="text-muted">No movies yet. Add one above!</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="movie-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Poster</th>
                                <th>Title</th>
                                <th>Year</th>
                                <th>Genre</th>
                                <th>Director</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($m = $movies->fetch_assoc()): ?>
                                <tr>
                                    <td style="color:var(--cr-muted)"><?php echo $m['id']; ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($m['poster']); ?>"
                                             alt="<?php echo htmlspecialchars($m['title']); ?>"
                                             class="poster-thumb"
                                             onerror="this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'42\' height=\'60\'><rect fill=\'%231A1A1A\' width=\'42\' height=\'60\'/><text x=\'50%25\' y=\'50%25\' fill=\'%238A8578\' font-size=\'8\' text-anchor=\'middle\' dy=\'.3em\'>No img</text></svg>'">
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($m['title']); ?></strong></td>
                                    <td><?php echo $m['year']; ?></td>
                                    <td><span class="genre-badge"><?php echo htmlspecialchars($m['genre']); ?></span></td>
                                    <td style="color:var(--cr-muted)"><?php echo htmlspecialchars($m['director']); ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="movies.php?edit=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-gold">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" onsubmit="return confirm('Delete \'<?php echo addslashes($m['title']); ?>\'? This will also delete all its reviews and favorites.');">
                                                <input type="hidden" name="movie_id" value="<?php echo $m['id']; ?>">
                                                <button type="submit" name="delete_movie" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <a href="../movie.php?id=<?php echo $m['id']; ?>" target="_blank" class="btn btn-sm btn-outline-gold" title="View on site">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewPoster(url) {
    const img = document.getElementById('posterPreview');
    if (url && url.startsWith('http')) {
        img.src = url;
        img.classList.add('visible');
        img.onerror = () => img.classList.remove('visible');
    } else {
        img.classList.remove('visible');
    }
}
</script>
</body>
</html>
