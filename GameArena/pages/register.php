<?php
/**
 * GameArena - Register Page
 */
$pageTitle = 'Register';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100 py-5">
        <div class="col-md-7">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h2 class="fw-bold">Join GameArena</h2>
                        <p class="text-muted">Create your account at KUET</p>
                    </div>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= sanitize($_GET['error']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/GameArena/controllers/auth_controller.php">
                        <input type="hidden" name="action" value="register">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" required
                                       placeholder="Enter your full name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" class="form-control" required
                                       placeholder="Choose a username">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required
                                   minlength="6" placeholder="Minimum 6 characters">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control"
                                       placeholder="01XXXXXXXXX">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Student ID</label>
                                <input type="text" name="student_id" class="form-control"
                                       placeholder="KUETXXXXXXXXX">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select name="department" class="form-select">
                                <option value="">Select Department</option>
                                <option value="CSE">Computer Science & Engineering</option>
                                <option value="EEE">Electrical & Electronic Engineering</option>
                                <option value="ECE">Electronics & Communication Engineering</option>
                                <option value="ME">Mechanical Engineering</option>
                                <option value="CE">Civil Engineering</option>
                                <option value="URP">Urban & Regional Planning</option>
                                <option value="BME">Biomedical Engineering</option>
                                <option value="IEM">Industrial & Engineering Management</option>
                                <option value="ARCH">Architecture</option>
                                <option value="其他">Other</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">Already have an account?
                            <a href="/GameArena/pages/login.php" class="text-primary">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
