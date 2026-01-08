<?php
/**
 * Dashboard Page - Indian Matrimony
 * Browse and search member profiles
 * DBMS Mini-Project
 * Developed by: Varad Kalanke and Vaishnavi Ghadge
 */
require_once 'config.php';


require_login();


$where_conditions = ["1=1"]; // Always true condition to start


$search_gender = isset($_GET['gender']) && !empty($_GET['gender']) ? sanitize_input($_GET['gender']) : '';
$search_religion = isset($_GET['religion']) && !empty($_GET['religion']) ? sanitize_input($_GET['religion']) : '';
$search_city = isset($_GET['city']) && !empty($_GET['city']) ? sanitize_input($_GET['city']) : '';
$search_education = isset($_GET['education']) && !empty($_GET['education']) ? sanitize_input($_GET['education']) : '';


$current_user_id = $_SESSION['user_id'];
$where_conditions[] = "id != '$current_user_id'";

// Get current user data for match calculation
$current_user_query = "SELECT * FROM users WHERE id = $current_user_id";
$current_user_result = mysqli_query($conn, $current_user_query);
$current_user_data = mysqli_fetch_assoc($current_user_result);

// Get user's match preferences
$pref_query = "SELECT * FROM match_preferences WHERE user_id = $current_user_id";
$pref_result = mysqli_query($conn, $pref_query);
$user_preferences = mysqli_fetch_assoc($pref_result);

if ($search_gender) {
    $where_conditions[] = "gender = '$search_gender'";
}
if ($search_religion) {
    $where_conditions[] = "religion LIKE '%$search_religion%'";
}
if ($search_city) {
    $where_conditions[] = "city LIKE '%$search_city%'";
}
if ($search_education) {
    $where_conditions[] = "education LIKE '%$search_education%'";
}


$where_clause = implode(' AND ', $where_conditions);


$query = "SELECT * FROM users WHERE $where_clause ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Function to calculate match percentage
function calculateMatchPercentage($user, $preferences, $current_user) {
    if (!$preferences) return 0;
    
    $score = 0;
    $max_score = 0;
    
    // Age match (30 points)
    $max_score += 30;
    if ($preferences['min_age'] && $preferences['max_age']) {
        if ($user['age'] >= $preferences['min_age'] && $user['age'] <= $preferences['max_age']) {
            $score += 30;
        } else {
            $age_diff = min(abs($user['age'] - $preferences['min_age']), abs($user['age'] - $preferences['max_age']));
            $score += max(0, 30 - ($age_diff * 5));
        }
    }
    
    // Gender match (20 points)
    $max_score += 20;
    if ($preferences['preferred_gender'] == 'Any' || $user['gender'] == $preferences['preferred_gender']) {
        $score += 20;
    }
    
    // Religion match (20 points)
    $max_score += 20;
    if (!$preferences['preferred_religion'] || $user['religion'] == $preferences['preferred_religion']) {
        $score += 20;
    }
    
    // City match (15 points)
    $max_score += 15;
    if (!$preferences['preferred_city'] || $user['city'] == $preferences['preferred_city']) {
        $score += 15;
    }
    
    // Education match (10 points)
    $max_score += 10;
    if (!$preferences['preferred_education'] || strpos($user['education'], $preferences['preferred_education']) !== false || strpos($preferences['preferred_education'], $user['education']) !== false) {
        $score += 10;
    }
    
    // Occupation match (5 points)
    $max_score += 5;
    if (!$preferences['preferred_occupation'] || strpos(strtolower($user['occupation']), strtolower($preferences['preferred_occupation'])) !== false) {
        $score += 5;
    }
    
    return $max_score > 0 ? round(($score / $max_score) * 100) : 0;
}


$religions_query = "SELECT DISTINCT religion FROM users ORDER BY religion";
$religions_result = mysqli_query($conn, $religions_query);

$cities_query = "SELECT DISTINCT city FROM users ORDER BY city";
$cities_result = mysqli_query($conn, $cities_query);

$educations_query = "SELECT DISTINCT education FROM users ORDER BY education";
$educations_result = mysqli_query($conn, $educations_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Indian Matrimony</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">💑 Indian Matrimony</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="match_preferences.php">Match Preferences</a></li>
                <li><a href="connections.php">Connections</a></li>
                <li><a href="connections.php?tab=messages">💬 Messages</a></li>
                <li><a href="edit_profile.php">My Profile</a></li>
                <li><a href="statistics.php">Statistics</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2 class="text-center mt-3 mb-3">Find Your Perfect Match</h2>
        
        <?php if (!$user_preferences): ?>
            <div class="glass-card mb-3" style="background: linear-gradient(135deg, #fff3cd, #ffe69c); border-left: 4px solid #ffc107;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="font-size: 2rem;">💡</div>
                    <div style="flex: 1;">
                        <h4 style="margin: 0; color: #856404;">Set Your Match Preferences</h4>
                        <p style="margin: 0.5rem 0 0 0; color: #856404;">Set your preferences to see match percentages and find your ideal partner faster!</p>
                    </div>
                    <a href="match_preferences.php" class="btn btn-primary">⚙️ Set Preferences Now</a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Quick Links -->
        <div style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 2rem; flex-wrap: wrap;">
            <a href="match_preferences.php" class="btn btn-primary">⚙️ <?php echo $user_preferences ? 'Update' : 'Set'; ?> Match Preferences</a>
            <a href="statistics.php" class="btn btn-secondary">📊 View Statistics</a>
            <a href="connections.php" class="btn btn-outline">💝 My Connections</a>
        </div>
        
        <div class="search-filter">
            <h3 style="margin-bottom: 1rem;">Search Filters</h3>
            <form method="GET" action="dashboard.php">
                <div class="filter-grid">
                    <!-- Gender Filter -->
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="">All Genders</option>
                            <option value="Male" <?php echo ($search_gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($search_gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($search_gender == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <!-- Religion Filter -->
                    <div class="form-group">
                        <label for="religion">Religion</label>
                        <select id="religion" name="religion" class="form-control">
                            <option value="">All Religions</option>
                            <?php while ($rel = mysqli_fetch_assoc($religions_result)): ?>
                                <option value="<?php echo htmlspecialchars($rel['religion']); ?>" 
                                        <?php echo ($search_religion == $rel['religion']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rel['religion']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- City Filter -->
                    <div class="form-group">
                        <label for="city">City</label>
                        <select id="city" name="city" class="form-control">
                            <option value="">All Cities</option>
                            <?php while ($ct = mysqli_fetch_assoc($cities_result)): ?>
                                <option value="<?php echo htmlspecialchars($ct['city']); ?>" 
                                        <?php echo ($search_city == $ct['city']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ct['city']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Education Filter -->
                    <div class="form-group">
                        <label for="education">Education</label>
                        <select id="education" name="education" class="form-control">
                            <option value="">All Education Levels</option>
                            <?php while ($edu = mysqli_fetch_assoc($educations_result)): ?>
                                <option value="<?php echo htmlspecialchars($edu['education']); ?>" 
                                        <?php echo ($search_education == $edu['education']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($edu['education']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="dashboard.php" class="btn btn-outline">Clear Filters</a>
                </div>
            </form>
        </div>

        <!-- Profiles Grid -->
        <div class="profiles-grid">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php 
                // Get all users and their interest status
                $users_data = [];
                while ($user = mysqli_fetch_assoc($result)) {
                    $users_data[] = $user;
                }
                
                // Check interest status for all users at once
                $interest_status = [];
                if (!empty($users_data)) {
                    $user_ids = array_column($users_data, 'id');
                    $ids_str = implode(',', $user_ids);
                    if (!empty($ids_str)) {
                        $interest_check_query = "SELECT interested_in_id, status FROM interests WHERE user_id = $current_user_id AND interested_in_id IN ($ids_str)";
                        $interest_check_result = mysqli_query($conn, $interest_check_query);
                        if ($interest_check_result) {
                            while ($int = mysqli_fetch_assoc($interest_check_result)) {
                                $interest_status[$int['interested_in_id']] = $int['status'];
                            }
                        }
                    }
                }
                
                // Calculate match percentages and sort
                foreach ($users_data as &$user) {
                    $match_percent = calculateMatchPercentage($user, $user_preferences, $current_user_data);
                    $user['match_percent'] = $match_percent;
                    $user['has_interest'] = isset($interest_status[$user['id']]);
                    $user['interest_status'] = $interest_status[$user['id']] ?? null;
                }
                unset($user);
                
                // Sort by match percentage (highest first)
                usort($users_data, function($a, $b) {
                    return $b['match_percent'] - $a['match_percent'];
                });
                
                foreach ($users_data as $user): 
                    $match_percent = $user['match_percent'];
                    $has_interest = $user['has_interest'];
                    $interest_status_val = $user['interest_status'];
                ?>
                    <div class="profile-card">
                        <div class="profile-card-header" style="position: relative;">
                            <?php if ($user_preferences && $match_percent > 0): ?>
                                <div style="position: absolute; top: 10px; right: 10px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                                    <?php echo $match_percent; ?>% Match
                                </div>
                            <?php endif; ?>
                            <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
                                 alt="<?php echo htmlspecialchars($user['name']); ?>"
                                 class="profile-avatar"
                                 onerror="this.src='https://via.placeholder.com/120/a8b5f5/ffffff?text=<?php echo substr($user['name'], 0, 1); ?>'">
                        </div>
                        <div class="profile-card-body">
                            <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($user['name']); ?></h3>
                            <div class="profile-info" style="margin-bottom: 1rem;">
                                <div class="profile-info-item">
                                    <span>👤</span>
                                    <span><?php echo htmlspecialchars($user['age']); ?> years, <?php echo htmlspecialchars($user['gender']); ?></span>
                                </div>
                                <div class="profile-info-item">
                                    <span>🕉️</span>
                                    <span><?php echo htmlspecialchars($user['religion']); ?></span>
                                </div>
                                <div class="profile-info-item">
                                    <span>📍</span>
                                    <span><?php echo htmlspecialchars($user['city']); ?></span>
                                </div>
                                <div class="profile-info-item">
                                    <span>🎓</span>
                                    <span><?php echo htmlspecialchars($user['education']); ?></span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                                <?php if ($has_interest): ?>
                                    <?php if ($interest_status_val == 'accepted'): ?>
                                        <?php 
                                        // Check if user can message (mutual accepted interest)
                                        $mutual_check = "SELECT COUNT(*) as count FROM interests i1
                                                       JOIN interests i2 ON i1.user_id = i2.interested_in_id AND i1.interested_in_id = i2.user_id
                                                       WHERE ((i1.user_id = $current_user_id AND i1.interested_in_id = {$user['id']})
                                                          OR (i1.user_id = {$user['id']} AND i1.interested_in_id = $current_user_id))
                                                       AND i1.status = 'accepted' AND i2.status = 'accepted'";
                                        $mutual_result = mysqli_query($conn, $mutual_check);
                                        $can_message = mysqli_fetch_assoc($mutual_result)['count'] > 0;
                                        ?>
                                        <?php if ($can_message): ?>
                                            <a href="messages.php?user_id=<?php echo $user['id']; ?>" class="btn btn-primary" style="width: 100%; text-align: center;">💬 Message</a>
                                        <?php else: ?>
                                            <button class="btn btn-outline" style="width: 100%;" disabled>✅ Interest Accepted</button>
                                        <?php endif; ?>
                                    <?php elseif ($interest_status_val == 'rejected'): ?>
                                        <button class="btn btn-outline" style="width: 100%;" disabled>❌ Interest Rejected</button>
                                    <?php else: ?>
                                        <button class="btn btn-outline" style="width: 100%;" disabled>⏳ Interest Pending</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <form method="POST" action="view_profile.php" style="margin: 0;">
                                        <input type="hidden" name="quick_interest" value="1">
                                        <input type="hidden" name="interested_in_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-primary" style="width: 100%;">💝 Express Interest</button>
                                    </form>
                                <?php endif; ?>
                                <a href="view_profile.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary" style="width: 100%; text-align: center;">👁️ View Full Profile</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="glass-card" style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                    <h3>No profiles found</h3>
                    <p style="color: var(--text-light); margin-top: 1rem;">Try adjusting your search filters</p>
                </div>
            <?php endif; ?>
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
    <script>
        // Function to view profile details
        function viewProfile(userId) {
            window.location.href = 'view_profile.php?id=' + userId;
        }
    </script>
</body>
</html>
