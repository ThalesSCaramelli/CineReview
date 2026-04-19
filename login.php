<?php
$pageTitle = 'Login';
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: account.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $result = loginUser($email, $password);
        if ($result === true) {
            header("Location: index.php");
            exit;
        } else {
            $error = $result;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="auth-card">
        <div class="text-center mb-3">
            <i class="bi bi-film" style="font-size:2.5rem; color:var(--cr-gold);"></i>
        </div>
        <h2>Welcome Back</h2>
        <p class="text-center text-muted mb-4">Login to your CineReview account</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text" style="background-color:var(--cr-bg-light); border-color:var(--cr-border); color:var(--cr-gold);">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="your@email.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="background-color:var(--cr-bg-light); border-color:var(--cr-border); color:var(--cr-gold);">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-gold w-100 mb-3">Login</button>
        </form>

        <p class="text-center text-muted mb-0">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
