<?php
/**
 * View Profile Page - Indian Matrimony
 * View and interact with user profiles
 * DBMS Mini-Project
 */
require_once 'config.php';

require_login();

$current_user_id = $_SESSION['user_id'];
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$message_type = '';

// Handle interest sending (from profile page or quick interest from dashboard)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['send_interest']) || isset($_POST['quick_interest']))) {
    $interested_in_id = (int)$_POST['interested_in_id'];
    
    if ($interested_in_id != $current_user_id) {
        // Check if interest already exists
        $check_query = "SELECT * FROM interests WHERE user_id = $current_user_id AND interested_in_id = $interested_in_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) == 0) {
            $insert_query = "INSERT INTO interests (user_id, interested_in_id, status) VALUES ($current_user_id, $interested_in_id, 'pending')";
            if (mysqli_query($conn, $insert_query)) {
                // Log activity
                $log_query = "INSERT INTO activity_logs (user_id, activity_type, activity_details) VALUES ($current_user_id, 'interest_sent', 'Expressed interest in user ID: $interested_in_id')";
                mysqli_query($conn, $log_query);
                
                $message = "Interest sent successfully!";
                $message_type = "success";
                
                // If quick interest from dashboard, redirect to profile
                if (isset($_POST['quick_interest'])) {
                    header("Location: view_profile.php?id=$interested_in_id&interest_sent=1");
                    exit();
                }
            } else {
                $message = "Failed to send interest. Please try again.";
                $message_type = "error";
            }
        } else {
            $message = "You have already expressed interest in this profile.";
            $message_type = "error";
        }
    }
}

// Get profile ID from GET or POST
if (!$profile_id) {
    $profile_id = isset($_POST['interested_in_id']) ? (int)$_POST['interested_in_id'] : 0;
}

// Check if interest was just sent (from redirect)
if (isset($_GET['interest_sent'])) {
    $message = "Interest sent successfully!";
    $message_type = "success";
}

if ($profile_id == 0 || $profile_id == $current_user_id) {
    if ($profile_id == $current_user_id) {
        header("Location: edit_profile.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$query = "SELECT * FROM users WHERE id = $profile_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: dashboard.php");
    exit();
}

$profile = mysqli_fetch_assoc($result);

// Check if current user has already sent interest
$interest_check = "SELECT * FROM interests WHERE user_id = $current_user_id AND interested_in_id = $profile_id";
$interest_result = mysqli_query($conn, $interest_check);
$has_interest = mysqli_num_rows($interest_result) > 0;
$interest_data = $has_interest ? mysqli_fetch_assoc($interest_result) : null;

// Check if profile user has sent interest to current user
$mutual_check = "SELECT * FROM interests WHERE user_id = $profile_id AND interested_in_id = $current_user_id";
$mutual_result = mysqli_query($conn, $mutual_check);
$mutual_interest = mysqli_num_rows($mutual_result) > 0;

// Log profile view
$log_query = "INSERT INTO activity_logs (user_id, activity_type, activity_details) VALUES ($current_user_id, 'profile_view', 'Viewed profile of user ID: $profile_id')";
mysqli_query($conn, $log_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['name']); ?> - Indian Matrimony</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">💑 Indian Matrimony</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="match_preferences.php">Match Preferences</a></li>
                <li><a href="connections.php">Connections</a></li>
                <li><a href="edit_profile.php">My Profile</a></li>
                <li><a href="statistics.php">Statistics</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="max-width: 900px;">
        <a href="dashboard.php" class="btn btn-outline mt-3" style="display: inline-block;">← Back to Dashboard</a>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'error'; ?>" style="margin-top: 1rem;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="glass-card mt-3">
            <div style="display: grid; grid-template-columns: 200px 1fr; gap: 2rem; align-items: start;">
                
                <div>
                    <img src="uploads/<?php echo htmlspecialchars($profile['photo']); ?>" 
                         alt="<?php echo htmlspecialchars($profile['name']); ?>"
                         style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary-color);"
                         onerror="this.src='https://via.placeholder.com/200/a8b5f5/ffffff?text=<?php echo substr($profile['name'], 0, 1); ?>'">
                </div>
                
                
                <div>
                    <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($profile['name']); ?></h1>
                    <p style="color: var(--text-light); font-size: 1.1rem; margin-bottom: 2rem;">
                        <?php echo htmlspecialchars($profile['age']); ?> years old, <?php echo htmlspecialchars($profile['gender']); ?>
                    </p>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                        
                        <div class="neuro-card" style="padding: 1.5rem;">
                            <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Personal Details</h3>
                            <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                                <div>
                                    <strong style="color: var(--text-dark);">Religion:</strong>
                                    <p style="margin: 0.2rem 0 0 0; color: var(--text-light);"><?php echo htmlspecialchars($profile['religion']); ?></p>
                                </div>
                                <div>
                                    <strong style="color: var(--text-dark);">Mother Tongue:</strong>
                                    <p style="margin: 0.2rem 0 0 0; color: var(--text-light);"><?php echo htmlspecialchars($profile['mother_tongue']); ?></p>
                                </div>
                                <div>
                                    <strong style="color: var(--text-dark);">City:</strong>
                                    <p style="margin: 0.2rem 0 0 0; color: var(--text-light);"><?php echo htmlspecialchars($profile['city']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                      
                        <div class="neuro-card" style="padding: 1.5rem;">
                            <h3 style="margin-bottom: 1rem; color: var(--secondary-color);">Professional Details</h3>
                            <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                                <div>
                                    <strong style="color: var(--text-dark);">Education:</strong>
                                    <p style="margin: 0.2rem 0 0 0; color: var(--text-light);"><?php echo htmlspecialchars($profile['education']); ?></p>
                                </div>
                                <div>
                                    <strong style="color: var(--text-dark);">Occupation:</strong>
                                    <p style="margin: 0.2rem 0 0 0; color: var(--text-light);"><?php echo htmlspecialchars($profile['occupation']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    <?php if (!empty($profile['bio'])): ?>
                        <div class="neuro-card mt-3" style="padding: 1.5rem;">
                            <h3 style="margin-bottom: 1rem; color: var(--accent-color);">About</h3>
                            <p style="color: var(--text-dark); line-height: 1.8;"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    
                    <div class="neuro-card mt-3" style="padding: 1.5rem; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
                        <h3 style="margin-bottom: 1rem; color: white;">Contact Information</h3>
                        <p style="color: rgba(255, 255, 255, 0.9);">
                            <strong>Email:</strong> <?php echo htmlspecialchars($profile['email']); ?>
                        </p>
                        <p style="margin-top: 1rem; font-size: 0.9rem; color: rgba(255, 255, 255, 0.8);">
                            Member since: <?php echo date('F Y', strtotime($profile['created_at'])); ?>
                        </p>
                    </div>
                    
                    <!-- Interest and Message Actions -->
                    <div class="mt-3" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <?php if (!$has_interest): ?>
                            <form method="POST" style="flex: 1; min-width: 200px;">
                                <input type="hidden" name="interested_in_id" value="<?php echo $profile_id; ?>">
                                <button type="submit" name="send_interest" class="btn btn-primary" style="width: 100%;">
                                    💝 Express Interest
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-outline" style="flex: 1; min-width: 200px;" disabled>
                                <?php 
                                if ($interest_data['status'] == 'accepted') {
                                    echo '✅ Interest Accepted';
                                } elseif ($interest_data['status'] == 'rejected') {
                                    echo '❌ Interest Rejected';
                                } else {
                                    echo '⏳ Interest Pending';
                                }
                                ?>
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($mutual_interest): ?>
                            <a href="messages.php?user_id=<?php echo $profile_id; ?>" class="btn btn-secondary" style="flex: 1; min-width: 200px; text-align: center;">
                                💬 Send Message
                            </a>
                        <?php endif; ?>
                        
                        <a href="view_profile.php?id=<?php echo $profile_id; ?>" class="btn btn-outline" style="flex: 1; min-width: 200px; text-align: center;">
                            🔄 Refresh
                        </a>
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
