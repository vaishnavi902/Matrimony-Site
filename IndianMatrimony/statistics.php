<?php
/**
 * Statistics Page - Indian Matrimony
 * Display user statistics and analytics using database views and complex queries
 * DBMS Mini-Project
 */
require_once 'config.php';

require_login();

$current_user_id = $_SESSION['user_id'];

// Get user statistics from view
$stats_query = "SELECT * FROM user_statistics WHERE id = $current_user_id";
$stats_result = mysqli_query($conn, $stats_query);
$user_stats = mysqli_fetch_assoc($stats_result);

// Get activity summary using stored procedure
$activity_days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
$activity_proc = "CALL GetUserActivity($current_user_id, $activity_days)";
$activity_result = mysqli_query($conn, $activity_proc);

// Clear results to free up connection for next query
if ($activity_result) {
    mysqli_free_result($activity_result);
    mysqli_next_result($conn);
}

// Get recent activities
$activities_query = "SELECT * FROM activity_logs WHERE user_id = $current_user_id ORDER BY created_at DESC LIMIT 10";
$activities_result = mysqli_query($conn, $activities_query);

// Get mutual interests count
$mutual_query = "SELECT COUNT(*) as count FROM mutual_interests WHERE user1_id = $current_user_id OR user2_id = $current_user_id";
$mutual_result = mysqli_query($conn, $mutual_query);
$mutual_count = mysqli_fetch_assoc($mutual_result)['count'];

// Get unread messages count from view
$unread_query = "SELECT unread_count FROM unread_messages_count WHERE user_id = $current_user_id";
$unread_result = mysqli_query($conn, $unread_query);
$unread_data = mysqli_fetch_assoc($unread_result);
$unread_count = $unread_data ? $unread_data['unread_count'] : 0;

// Get profile views count
$views_query = "SELECT COUNT(*) as count FROM activity_logs WHERE activity_type = 'profile_view' AND activity_details LIKE '%user ID: $current_user_id%'";
$views_result = mysqli_query($conn, $views_query);
$views_count = mysqli_fetch_assoc($views_result)['count'];

// Get top cities with most users
$top_cities_query = "SELECT city, COUNT(*) as user_count FROM users GROUP BY city ORDER BY user_count DESC LIMIT 5";
$top_cities_result = mysqli_query($conn, $top_cities_query);

// Get religion distribution
$religion_query = "SELECT religion, COUNT(*) as count FROM users GROUP BY religion ORDER BY count DESC";
$religion_result = mysqli_query($conn, $religion_query);

// Get age distribution
$age_query = "SELECT 
    CASE 
        WHEN age BETWEEN 18 AND 25 THEN '18-25'
        WHEN age BETWEEN 26 AND 30 THEN '26-30'
        WHEN age BETWEEN 31 AND 35 THEN '31-35'
        WHEN age BETWEEN 36 AND 40 THEN '36-40'
        ELSE '40+'
    END as age_group,
    COUNT(*) as count
    FROM users
    GROUP BY age_group
    ORDER BY MIN(age)";
$age_result = mysqli_query($conn, $age_query);

// Get interest acceptance rate
$interest_rate_query = "SELECT 
    COUNT(CASE WHEN status = 'accepted' THEN 1 END) as accepted,
    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
    COUNT(*) as total
    FROM interests
    WHERE user_id = $current_user_id";
$interest_rate_result = mysqli_query($conn, $interest_rate_query);
$interest_rate = mysqli_fetch_assoc($interest_rate_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Indian Matrimony</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">💑 Indian Matrimony</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="connections.php">Connections</a></li>
                <li><a href="edit_profile.php">My Profile</a></li>
                <li><a href="statistics.php">Statistics</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="max-width: 1200px;">
        <h2 class="text-center mt-3 mb-3">My Statistics & Analytics</h2>

        <!-- Personal Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="neuro-card" style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">💝</div>
                <h3 style="color: var(--primary-color); font-size: 2rem; margin: 0.5rem 0;"><?php echo $user_stats['interests_sent'] ?? 0; ?></h3>
                <p style="color: var(--text-light); margin: 0;">Interests Sent</p>
            </div>
            
            <div class="neuro-card" style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📥</div>
                <h3 style="color: var(--secondary-color); font-size: 2rem; margin: 0.5rem 0;"><?php echo $user_stats['interests_received'] ?? 0; ?></h3>
                <p style="color: var(--text-light); margin: 0;">Interests Received</p>
            </div>
            
            <div class="neuro-card" style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">✅</div>
                <h3 style="color: var(--accent-color); font-size: 2rem; margin: 0.5rem 0;"><?php echo $user_stats['accepted_interests'] ?? 0; ?></h3>
                <p style="color: var(--text-light); margin: 0;">Accepted Interests</p>
            </div>
            
            <div class="neuro-card" style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🤝</div>
                <h3 style="color: var(--primary-color); font-size: 2rem; margin: 0.5rem 0;"><?php echo $mutual_count; ?></h3>
                <p style="color: var(--text-light); margin: 0;">Mutual Connections</p>
            </div>
            
            <div class="neuro-card" style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">💬</div>
                <h3 style="color: var(--secondary-color); font-size: 2rem; margin: 0.5rem 0;"><?php echo ($user_stats['messages_sent'] ?? 0) + ($user_stats['messages_received'] ?? 0); ?></h3>
                <p style="color: var(--text-light); margin: 0;">Total Messages</p>
            </div>
            
            <div class="neuro-card" style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">👁️</div>
                <h3 style="color: var(--accent-color); font-size: 2rem; margin: 0.5rem 0;"><?php echo $views_count; ?></h3>
                <p style="color: var(--text-light); margin: 0;">Profile Views</p>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <!-- Interest Statistics -->
            <div class="glass-card">
                <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Interest Statistics</h3>
                <?php if ($interest_rate['total'] > 0): ?>
                    <div style="margin-bottom: 1rem;">
                        <p style="color: var(--text-light); margin-bottom: 0.5rem;">
                            <strong>Total Interests Sent:</strong> <?php echo $interest_rate['total']; ?>
                        </p>
                        <div style="background: var(--bg-color); border-radius: 10px; padding: 1rem; margin-bottom: 0.5rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>✅ Accepted</span>
                                <span><strong><?php echo $interest_rate['accepted']; ?></strong> (<?php echo round(($interest_rate['accepted'] / $interest_rate['total']) * 100, 1); ?>%)</span>
                            </div>
                            <div style="height: 10px; background: var(--glass-bg); border-radius: 5px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo ($interest_rate['accepted'] / $interest_rate['total']) * 100; ?>%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));"></div>
                            </div>
                        </div>
                        <div style="background: var(--bg-color); border-radius: 10px; padding: 1rem; margin-bottom: 0.5rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>❌ Rejected</span>
                                <span><strong><?php echo $interest_rate['rejected']; ?></strong> (<?php echo round(($interest_rate['rejected'] / $interest_rate['total']) * 100, 1); ?>%)</span>
                            </div>
                            <div style="height: 10px; background: var(--glass-bg); border-radius: 5px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo ($interest_rate['rejected'] / $interest_rate['total']) * 100; ?>%; background: #e74c3c;"></div>
                            </div>
                        </div>
                        <div style="background: var(--bg-color); border-radius: 10px; padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>⏳ Pending</span>
                                <span><strong><?php echo $interest_rate['pending']; ?></strong> (<?php echo round(($interest_rate['pending'] / $interest_rate['total']) * 100, 1); ?>%)</span>
                            </div>
                            <div style="height: 10px; background: var(--glass-bg); border-radius: 5px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo ($interest_rate['pending'] / $interest_rate['total']) * 100; ?>%; background: #f39c12;"></div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-light);">No interests sent yet. Start browsing profiles!</p>
                <?php endif; ?>
            </div>

            <!-- Activity Summary -->
            <div class="glass-card">
                <h3 style="margin-bottom: 1rem; color: var(--secondary-color);">Activity Summary</h3>
                <form method="GET" style="margin-bottom: 1rem;">
                    <label for="days" style="display: block; margin-bottom: 0.5rem; color: var(--text-light);">Activity Period:</label>
                    <select name="days" id="days" class="form-control" onchange="this.form.submit()">
                        <option value="7" <?php echo $activity_days == 7 ? 'selected' : ''; ?>>Last 7 days</option>
                        <option value="30" <?php echo $activity_days == 30 ? 'selected' : ''; ?>>Last 30 days</option>
                        <option value="90" <?php echo $activity_days == 90 ? 'selected' : ''; ?>>Last 90 days</option>
                    </select>
                </form>
                <?php
                // Get activity summary again after form submit
                $activity_proc2 = "CALL GetUserActivity($current_user_id, $activity_days)";
                $activity_result2 = mysqli_query($conn, $activity_proc2);
                if ($activity_result2 && mysqli_num_rows($activity_result2) > 0): 
                ?>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php while ($activity = mysqli_fetch_assoc($activity_result2)): ?>
                            <div class="neuro-card" style="padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong><?php echo ucfirst(str_replace('_', ' ', $activity['activity_type'])); ?></strong>
                                        <p style="color: var(--text-light); margin: 0.3rem 0 0 0; font-size: 0.9rem;">
                                            <?php echo date('M d, Y', strtotime($activity['activity_date'])); ?>
                                        </p>
                                    </div>
                                    <div style="font-size: 1.5rem; color: var(--primary-color);">
                                        <?php echo $activity['activity_count']; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; 
                        mysqli_free_result($activity_result2);
                        mysqli_next_result($conn);
                        ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-light);">No activity in selected period.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Platform Analytics -->
        <div class="glass-card mt-3">
            <h3 style="margin-bottom: 1.5rem; color: var(--accent-color);">Platform Analytics</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <!-- Top Cities -->
                <div>
                    <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Top Cities by Users</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        <?php while ($city = mysqli_fetch_assoc($top_cities_result)): ?>
                            <div class="neuro-card" style="padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 600;"><?php echo htmlspecialchars($city['city']); ?></span>
                                    <span style="color: var(--primary-color); font-weight: bold;"><?php echo $city['user_count']; ?> users</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Religion Distribution -->
                <div>
                    <h4 style="margin-bottom: 1rem; color: var(--secondary-color);">Religion Distribution</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        <?php while ($religion = mysqli_fetch_assoc($religion_result)): ?>
                            <div class="neuro-card" style="padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 600;"><?php echo htmlspecialchars($religion['religion']); ?></span>
                                    <span style="color: var(--secondary-color); font-weight: bold;"><?php echo $religion['count']; ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Age Distribution -->
                <div>
                    <h4 style="margin-bottom: 1rem; color: var(--accent-color);">Age Distribution</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        <?php while ($age = mysqli_fetch_assoc($age_result)): ?>
                            <div class="neuro-card" style="padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 600;"><?php echo $age['age_group']; ?> years</span>
                                    <span style="color: var(--accent-color); font-weight: bold;"><?php echo $age['count']; ?> users</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="glass-card mt-3">
            <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Recent Activities</h3>
            <?php if ($activities_result && mysqli_num_rows($activities_result) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php while ($activity = mysqli_fetch_assoc($activities_result)): ?>
                        <div class="neuro-card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div>
                                    <strong style="color: var(--text-dark);">
                                        <?php echo ucfirst(str_replace('_', ' ', $activity['activity_type'])); ?>
                                    </strong>
                                    <?php if (!empty($activity['activity_details'])): ?>
                                        <p style="color: var(--text-light); margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($activity['activity_details']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <p style="color: var(--text-light); margin: 0.5rem 0 0 0; font-size: 0.8rem;">
                                        <?php echo date('M d, Y h:i A', strtotime($activity['created_at'])); ?>
                                    </p>
                                </div>
                                <span style="font-size: 1.5rem;">
                                    <?php
                                    $icons = [
                                        'login' => '🔐',
                                        'profile_view' => '👁️',
                                        'search' => '🔍',
                                        'interest_sent' => '💝',
                                        'message_sent' => '💬'
                                    ];
                                    echo $icons[$activity['activity_type']] ?? '📝';
                                    ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="color: var(--text-light);">No recent activities.</p>
            <?php endif; ?>
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
