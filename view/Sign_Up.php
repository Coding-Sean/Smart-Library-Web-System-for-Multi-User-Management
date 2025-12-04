<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - MyLibrary</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/signup.css">
</head>
<body>

    <div class="split left"></div>
    <div class="split right"></div>

    <div class="signup-container">
        <form method="POST" action="../controller/SignupController.php" class="signup-form">
            <h2 class="text-center">SIGN UP</h2>
            <p class="text-center fw-bold">Join MyLibrary</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <div class="input-group">
                <input type="text" name="name" placeholder="Full Name" required>
            </div>

            <div class="input-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <label class="role-label">Select role :</label>
            <div class="role-buttons">
                <input type="radio" name="role" value="Student" id="student" required>
                <label for="student">student</label>

                <input type="radio" name="role" value="Librarian" id="librarian">
                <label for="librarian">librarian</label>

                <input type="radio" name="role" value="Teacher" id="teacher">
                <label for="teacher">teacher</label>

                <input type="radio" name="role" value="Staff" id="staff">
                <label for="staff">staff</label>
            </div>

            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="input-group">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>

            <button type="submit" class="btn-create">CREATE</button>
        </form>
    </div>

</body>
</html>
