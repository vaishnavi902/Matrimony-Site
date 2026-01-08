<?php
/**
 * Login Page - Indian Matrimony
 * DBMS Mini-Project
 * Developed by: Varad Kalanke and Vaishnavi Ghadge
 */
require_once 'config.php';


if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            
            if (password_verify($password, $user['password'])) {
            
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Log login activity
                $log_query = "INSERT INTO activity_logs (user_id, activity_type, activity_details) VALUES ({$user['id']}, 'login', 'User logged in successfully')";
                mysqli_query($conn, $log_query);
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Indian Matrimony</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">💑 Indian Matrimony</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </div>
    </nav>

    
    <div class="container" style="max-width: 500px;">
        <div class="glass-card mt-4">
            <h2 class="text-center mb-3">Welcome Back!</h2>
            <p class="text-center" style="color: var(--text-light); margin-bottom: 2rem;">Login to find your perfect match</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="Enter your email">
                </div>
                
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required
                           placeholder="Enter your password">
                </div>
                
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Login</button>
            </form>
            
            <p class="text-center mt-3">
                Don't have an account? <a href="register.php" style="color: var(--primary-color); font-weight: 600;">Register here</a>
            </p>
            
            
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Indian Matrimony. All rights reserved.</p>
            <p class="developers">Developed by <strong>Varad Kalanke</strong> and <strong>Vaishnavi Ghadge</strong></p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
