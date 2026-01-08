<?php
/**
 * Admin Panel - Indian Matrimony
 * Admin dashboard for managing users and platform data
 * DBMS Mini-Project
 */
require_once 'config.php';

// Check if user is admin (simple check - in production use proper admin session)
$admin_logged_in = isset($_SESSION['admin_id']);

// Handle admin login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    $admin_query = "SELECT * FROM admin_users WHERE username = '$username'";
    $admin_result = mysqli_query($conn, $admin_query);
    
    if ($admin_result && mysqli_num_rows($admin_result) > 0) {
        $admin = mysqli_fetch_assoc($admin_result);
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $admin_logged_in = true;
        }
    }
    
    if (!$admin_logged_in) {
        $error = "Invalid admin credentials.";
    }
}

// Handle admin logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    $admin_logged_in = false;
}

// If not logged in, show login form
if (!$admin_logged_in) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - Indian Matrimony</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="container" style="max-width: 500px; margin-top: 10rem;">
            <div class="glass-card">
                <h2 class="text-center mb-3">Admin Login</h2>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="admin_login" class="btn btn-primary" style="width: 100%;">Login</button>
                </form>
                <p class="text-center mt-3">
                    <a href="index.php" style="color: var(--primary-color);">← Back to Home</a>
                </p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Admin Dashboard Data
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$total_interests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM interests"))['count'];
$total_messages = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM messages"))['count'];
$accepted_interests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM interests WHERE status = 'accepted'"))['count'];
$total_activities = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM activity_logs"))['count'];

// Get recent users
$recent_users_query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 10";
$recent_users_result = mysqli_query($conn, $recent_users_query);

// Get users with most activities
$active_users_query = "SELECT u.id, u.name, u.email, COUNT(al.id) as activity_count 
                       FROM users u 
                       LEFT JOIN activity_logs al ON u.id = al.user_id 
                       GROUP BY u.id, u.name, u.email 
                       ORDER BY activity_count DESC 
                       LIMIT 10";
$active_users_result = mysqli_query($conn, $active_users_query);

// Get platform statistics
$gender_stats_query = "SELECT gender, COUNT(*) as count FROM users GROUP BY gender";
$gender_stats_result = mysqli_query($conn, $gender_stats_query);

// Handle user deletion
if (isset($_GET['delete_user']) && isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    $delete_query = "DELETE FROM users WHERE id = $user_id";
    mysqli_query($conn, $delete_query);
    header("Location: admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Indian Matrimony</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">💑 Indian Matrimony - Admin</a>
            <ul class="nav-links">
                <li><a href="admin.php">Dashboard</a></li>
                <li><a href="index.php">Home</a></li>
                <li><a href="admin.php?logout=1">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="max-width: 1400px;">
        <h2 class="text-center mt-3 mb-3">Admin Dashboard</h2>
        <p class="text-center" style="color: var(--text-light); margin-bottom: 2rem;">
            Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
        </p>

        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="neuro-card" style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">👥</div>
                <h3 style="color: var(--primary-color); font-size: 2rem; margin: 0.5rem 0;"><?php echo $total_users; ?></h3>
                <p style="color: var(--text-light); margin: 0;">Total Users</p>
            </div>
            
            <div class="neuro-card" style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">💝</div>
                <h3 style="color: var(--secondary-color); font-size: 2rem; margin: 0.5rem 0;"><?php echo $total_interests; ?></h3>
                <p style="color: var(--text-light); margin: 0;">Total Interests</p>
            </div>
            
            <div class="neuro-card" style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">✅</div>
                <h3 style="color: var(--accent-color); font-size: 2rem; margin: 0.5rem 0;"><?php echo $accepted_interests; ?></h3>
                <p style="color: var(--text-light); margin: 0;">Accepted Interests</p>
            </div>
            
            <div class="neuro-card" style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">💬</div>
                <h3 style="color: var(--primary-color); font-size: 2rem; margin: 0.5rem 0;"><?php echo $total_messages; ?></h3>
                <p style="color: var(--text-light); margin: 0;">Total Messages</p>
            </div>
            
            <div class="neuro-card" style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📊</div>
                <h3 style="color: var(--secondary-color); font-size: 2rem; margin: 0.5rem 0;"><?php echo $total_activities; ?></h3>
                <p style="color: var(--text-light); margin: 0;">Total Activities</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(600px, 1fr)); gap: 2rem;">
            <!-- Recent Users -->
            <div class="glass-card">
                <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Recent Users</h3>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--bg-color); border-bottom: 2px solid var(--glass-bg);">
                                <th style="padding: 1rem; text-align: left;">Name</th>
                                <th style="padding: 1rem; text-align: left;">Email</th>
                                <th style="padding: 1rem; text-align: left;">City</th>
                                <th style="padding: 1rem; text-align: left;">Joined</th>
                                <th style="padding: 1rem; text-align: left;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($recent_users_result)): ?>
                                <tr style="border-bottom: 1px solid var(--glass-bg);">
                                    <td style="padding: 0.8rem;"><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td style="padding: 0.8rem;"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td style="padding: 0.8rem;"><?php echo htmlspecialchars($user['city']); ?></td>
                                    <td style="padding: 0.8rem;"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td style="padding: 0.8rem;">
                                        <a href="view_profile.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.9rem;">View</a>
                                        <a href="admin.php?delete_user=1&user_id=<?php echo $user['id']; ?>" 
                                           class="btn btn-outline" 
                                           style="padding: 0.3rem 0.8rem; font-size: 0.9rem;"
                                           onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Most Active Users -->
            <div class="glass-card">
                <h3 style="margin-bottom: 1rem; color: var(--secondary-color);">Most Active Users</h3>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php while ($active = mysqli_fetch_assoc($active_users_result)): ?>
                        <div class="neuro-card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><?php echo htmlspecialchars($active['name']); ?></strong>
                                    <p style="color: var(--text-light); margin: 0.3rem 0 0 0; font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($active['email']); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-size: 1.5rem; color: var(--primary-color); font-weight: bold;">
                                        <?php echo $active['activity_count']; ?>
                                    </span>
                                    <p style="color: var(--text-light); margin: 0; font-size: 0.8rem;">activities</p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Gender Statistics -->
        <div class="glass-card mt-3">
            <h3 style="margin-bottom: 1rem; color: var(--accent-color);">User Distribution by Gender</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <?php while ($gender = mysqli_fetch_assoc($gender_stats_result)): ?>
                    <div class="neuro-card" style="padding: 1.5rem; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">
                            <?php echo $gender['gender'] == 'Male' ? '👨' : ($gender['gender'] == 'Female' ? '👩' : '⚧️'); ?>
                        </div>
                        <h3 style="margin: 0.5rem 0; color: var(--text-dark);"><?php echo htmlspecialchars($gender['gender']); ?></h3>
                        <p style="color: var(--primary-color); font-size: 1.5rem; font-weight: bold; margin: 0;">
                            <?php echo $gender['count']; ?> users
                        </p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Database Views Usage -->
        <div class="glass-card mt-3">
            <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Database Views & Complex Queries</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <!-- User Statistics View -->
                <div class="neuro-card" style="padding: 1.5rem;">
                    <h4 style="margin-bottom: 1rem; color: var(--primary-color);">User Statistics View</h4>
                    <?php
                    $view_query = "SELECT * FROM user_statistics ORDER BY interests_sent DESC LIMIT 5";
                    $view_result = mysqli_query($conn, $view_query);
                    ?>
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        <?php while ($stat = mysqli_fetch_assoc($view_result)): ?>
                            <div style="padding: 0.8rem; background: var(--bg-color); border-radius: 8px;">
                                <strong><?php echo htmlspecialchars($stat['name']); ?></strong>
                                <p style="margin: 0.3rem 0 0 0; font-size: 0.9rem; color: var(--text-light);">
                                    Sent: <?php echo $stat['interests_sent']; ?> | 
                                    Received: <?php echo $stat['interests_received']; ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Mutual Interests View -->
                <div class="neuro-card" style="padding: 1.5rem;">
                    <h4 style="margin-bottom: 1rem; color: var(--secondary-color);">Mutual Interests View</h4>
                    <?php
                    $mutual_query = "SELECT * FROM mutual_interests ORDER BY interest_date DESC LIMIT 5";
                    $mutual_result = mysqli_query($conn, $mutual_query);
                    ?>
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        <?php while ($mutual = mysqli_fetch_assoc($mutual_result)): ?>
                            <div style="padding: 0.8rem; background: var(--bg-color); border-radius: 8px;">
                                <strong>User <?php echo $mutual['user1_id']; ?> ↔ User <?php echo $mutual['user2_id']; ?></strong>
                                <p style="margin: 0.3rem 0 0 0; font-size: 0.9rem; color: var(--text-light);">
                                    <?php echo date('M d, Y', strtotime($mutual['interest_date'])); ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Unread Messages View -->
                <div class="neuro-card" style="padding: 1.5rem;">
                    <h4 style="margin-bottom: 1rem; color: var(--accent-color);">Unread Messages View</h4>
                    <?php
                    $unread_query = "SELECT * FROM unread_messages_count ORDER BY unread_count DESC LIMIT 5";
                    $unread_result = mysqli_query($conn, $unread_query);
                    ?>
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        <?php while ($unread = mysqli_fetch_assoc($unread_result)): ?>
                            <div style="padding: 0.8rem; background: var(--bg-color); border-radius: 8px;">
                                <strong>User ID: <?php echo $unread['user_id']; ?></strong>
                                <p style="margin: 0.3rem 0 0 0; font-size: 0.9rem; color: var(--text-light);">
                                    <?php echo $unread['unread_count']; ?> unread messages
                                </p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Indian Matrimony. All rights reserved.</p>
            <p class="developers">Developed by <strong>Varad Kalanke</strong> and <strong>Vaishnavi Ghadge</strong></p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>


