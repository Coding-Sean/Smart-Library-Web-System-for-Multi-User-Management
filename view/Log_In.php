<html>
    <head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/login.css">
    </head>
<body>
    <div class="login-container">
        <div class="login-header">SIGN IN</div>
        <h1>MyLibrary</h1>
        <form action="../controller/LoginController.php" method="POST">
            <div class="input-group">
                <input type="email" name="email" placeholder="Email" required></div>
            
            <div class="input-group"><input type="password" name="password" placeholder="Password" required></div>

            <button type="submit" class="btn btn-login">SIGN IN</button>
            <a href="Sign_Up.php" class="btn btn-signup">SIGN UP</a>
        </form>

    </div>
</body>
</html>