<?php
/**
 * Match Preferences Page - Indian Matrimony
 * Users can set their preferences for finding matches
 * DBMS Mini-Project
 */
require_once 'config.php';

require_login();

$current_user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get existing preferences
$pref_query = "SELECT * FROM match_preferences WHERE user_id = $current_user_id";
$pref_result = mysqli_query($conn, $pref_query);
$preferences = mysqli_fetch_assoc($pref_result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_preferences'])) {
    $preferred_gender = sanitize_input($_POST['preferred_gender']);
    $min_age = (int)$_POST['min_age'];
    $max_age = (int)$_POST['max_age'];
    $preferred_religion = !empty($_POST['preferred_religion']) ? sanitize_input($_POST['preferred_religion']) : NULL;
    $preferred_city = !empty($_POST['preferred_city']) ? sanitize_input($_POST['preferred_city']) : NULL;
    $preferred_education = !empty($_POST['preferred_education']) ? sanitize_input($_POST['preferred_education']) : NULL;
    $preferred_occupation = !empty($_POST['preferred_occupation']) ? sanitize_input($_POST['preferred_occupation']) : NULL;
    
    // Validation
    if ($min_age < 18 || $min_age > 100) {
        $error = "Minimum age must be between 18 and 100.";
    } elseif ($max_age < 18 || $max_age > 100) {
        $error = "Maximum age must be between 18 and 100.";
    } elseif ($min_age > $max_age) {
        $error = "Minimum age cannot be greater than maximum age.";
    } else {
        // Prepare NULL values for SQL
        $preferred_religion_sql = $preferred_religion ? "'$preferred_religion'" : 'NULL';
        $preferred_city_sql = $preferred_city ? "'$preferred_city'" : 'NULL';
        $preferred_education_sql = $preferred_education ? "'$preferred_education'" : 'NULL';
        $preferred_occupation_sql = $preferred_occupation ? "'$preferred_occupation'" : 'NULL';
        
        if ($preferences) {
            // Update existing preferences
            $update_query = "UPDATE match_preferences SET
                            preferred_gender = '$preferred_gender',
                            min_age = $min_age,
                            max_age = $max_age,
                            preferred_religion = $preferred_religion_sql,
                            preferred_city = $preferred_city_sql,
                            preferred_education = $preferred_education_sql,
                            preferred_occupation = $preferred_occupation_sql
                            WHERE user_id = $current_user_id";
            
            if (mysqli_query($conn, $update_query)) {
                $success = "Preferences updated successfully!";
                $pref_result = mysqli_query($conn, $pref_query);
                $preferences = mysqli_fetch_assoc($pref_result);
            } else {
                $error = "Failed to update preferences. Please try again.";
            }
        } else {
            // Insert new preferences
            $insert_query = "INSERT INTO match_preferences 
                            (user_id, preferred_gender, min_age, max_age, preferred_religion, preferred_city, preferred_education, preferred_occupation)
                            VALUES ($current_user_id, '$preferred_gender', $min_age, $max_age, $preferred_religion_sql, $preferred_city_sql, $preferred_education_sql, $preferred_occupation_sql)";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = "Preferences saved successfully!";
                $pref_result = mysqli_query($conn, $pref_query);
                $preferences = mysqli_fetch_assoc($pref_result);
            } else {
                $error = "Failed to save preferences. Please try again.";
            }
        }
    }
}

// Get distinct values for dropdowns
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
    <title>Match Preferences - Indian Matrimony</title>
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

    <div class="container" style="max-width: 900px;">
        <h2 class="text-center mt-3 mb-3">Match Preferences</h2>
        <p class="text-center" style="color: var(--text-light); margin-bottom: 2rem;">
            Set your preferences to find your ideal match
        </p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="glass-card">
            <form method="POST">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <!-- Preferred Gender -->
                    <div class="form-group">
                        <label for="preferred_gender">Preferred Gender *</label>
                        <select id="preferred_gender" name="preferred_gender" class="form-control" required>
                            <option value="Any" <?php echo (!$preferences || $preferences['preferred_gender'] == 'Any') ? 'selected' : ''; ?>>Any</option>
                            <option value="Male" <?php echo ($preferences && $preferences['preferred_gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($preferences && $preferences['preferred_gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($preferences && $preferences['preferred_gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <!-- Age Range -->
                    <div class="form-group">
                        <label for="min_age">Minimum Age *</label>
                        <input type="number" id="min_age" name="min_age" class="form-control" required 
                               min="18" max="100" 
                               value="<?php echo $preferences ? $preferences['min_age'] : 18; ?>">
                    </div>

                    <div class="form-group">
                        <label for="max_age">Maximum Age *</label>
                        <input type="number" id="max_age" name="max_age" class="form-control" required 
                               min="18" max="100" 
                               value="<?php echo $preferences ? $preferences['max_age'] : 100; ?>">
                    </div>

                    <!-- Preferred Religion -->
                    <div class="form-group">
                        <label for="preferred_religion">Preferred Religion</label>
                        <select id="preferred_religion" name="preferred_religion" class="form-control">
                            <option value="">Any Religion</option>
                            <?php while ($rel = mysqli_fetch_assoc($religions_result)): ?>
                                <option value="<?php echo htmlspecialchars($rel['religion']); ?>" 
                                        <?php echo ($preferences && $preferences['preferred_religion'] == $rel['religion']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rel['religion']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small style="color: var(--text-light);">Leave empty for any religion</small>
                    </div>

                    <!-- Preferred City -->
                    <div class="form-group">
                        <label for="preferred_city">Preferred City</label>
                        <select id="preferred_city" name="preferred_city" class="form-control">
                            <option value="">Any City</option>
                            <?php while ($city = mysqli_fetch_assoc($cities_result)): ?>
                                <option value="<?php echo htmlspecialchars($city['city']); ?>" 
                                        <?php echo ($preferences && $preferences['preferred_city'] == $city['city']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($city['city']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small style="color: var(--text-light);">Leave empty for any city</small>
                    </div>

                    <!-- Preferred Education -->
                    <div class="form-group">
                        <label for="preferred_education">Preferred Education</label>
                        <select id="preferred_education" name="preferred_education" class="form-control">
                            <option value="">Any Education</option>
                            <?php while ($edu = mysqli_fetch_assoc($educations_result)): ?>
                                <option value="<?php echo htmlspecialchars($edu['education']); ?>" 
                                        <?php echo ($preferences && $preferences['preferred_education'] == $edu['education']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($edu['education']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small style="color: var(--text-light);">Leave empty for any education level</small>
                    </div>

                    <!-- Preferred Occupation -->
                    <div class="form-group">
                        <label for="preferred_occupation">Preferred Occupation</label>
                        <input type="text" id="preferred_occupation" name="preferred_occupation" class="form-control"
                               value="<?php echo $preferences ? htmlspecialchars($preferences['preferred_occupation']) : ''; ?>"
                               placeholder="e.g., Software Engineer, Doctor">
                        <small style="color: var(--text-light);">Leave empty for any occupation</small>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" name="save_preferences" class="btn btn-primary" style="flex: 1;">
                        💾 Save Preferences
                    </button>
                    <a href="dashboard.php" class="btn btn-outline" style="flex: 1; text-align: center; line-height: 2.5;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Smart Matches Based on Preferences -->
        <?php if ($preferences): ?>
            <div class="glass-card mt-3">
                <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Smart Matches Based on Your Preferences</h3>
                <?php
                // Use stored procedure to get matches
                $matches_proc = "CALL GetMatches($current_user_id)";
                $matches_result = mysqli_query($conn, $matches_proc);
                
                if ($matches_result && mysqli_num_rows($matches_result) > 0):
                ?>
                    <div class="profiles-grid" style="margin-top: 1rem;">
                        <?php while ($match = mysqli_fetch_assoc($matches_result)): ?>
                            <div class="profile-card">
                                <div class="profile-card-header">
                                    <img src="uploads/<?php echo htmlspecialchars($match['photo']); ?>" 
                                         alt="<?php echo htmlspecialchars($match['name']); ?>"
                                         class="profile-avatar"
                                         onerror="this.src='https://via.placeholder.com/120/a8b5f5/ffffff?text=<?php echo substr($match['name'], 0, 1); ?>'">
                                </div>
                                <div class="profile-card-body">
                                    <h3><?php echo htmlspecialchars($match['name']); ?></h3>
                                    <p style="color: var(--text-light); margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars($match['age']); ?> years, <?php echo htmlspecialchars($match['gender']); ?>
                                    </p>
                                    <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 1rem;">
                                        📍 <?php echo htmlspecialchars($match['city']); ?><br>
                                        🕉️ <?php echo htmlspecialchars($match['religion']); ?>
                                    </p>
                                    <a href="view_profile.php?id=<?php echo $match['id']; ?>" class="btn btn-primary" style="width: 100%;">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        <?php endwhile;
                        mysqli_free_result($matches_result);
                        mysqli_next_result($conn);
                        ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-light);">No matches found based on your current preferences. Try adjusting your preferences.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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


