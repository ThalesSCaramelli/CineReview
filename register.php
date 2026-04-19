<?php
$pageTitle = 'Register';
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: account.php");
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $result = registerUser($name, $email, $password);
        if ($result === true) {
            $success = "Account created! You can now login.";
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
            <i class="bi bi-person-plus-fill" style="font-size:2.5rem; color:var(--cr-gold);"></i>
        </div>
        <h2>Create Account</h2>
        <p class="text-center text-muted mb-4">Join CineReview and start reviewing</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?> <a href="login.php">Click here to login</a>.</div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text" style="background-color:var(--cr-bg-light); border-color:var(--cr-border); color:var(--cr-gold);">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Your full name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                </div>
            </div>
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
                    <input type="password" class="form-control" id="password" name="password" placeholder="At least 6 characters" required>
                </div>
            </div>
            <button type="submit" class="btn btn-gold w-100 mb-3">Create Account</button>
        </form>

        <p class="text-center text-muted mb-0">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
