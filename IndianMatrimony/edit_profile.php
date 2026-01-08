<?php
/**
 * Edit Profile Page - Indian Matrimony
 * Users can update their profile information
 * DBMS Mini-Project
 * Developed by: Varad Kalanke and Vaishnavi Ghadge
 */
require_once 'config.php';

require_login();

$error = '';
$success = '';

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = sanitize_input($_POST['name']);
    $age = (int)$_POST['age'];
    $religion = sanitize_input($_POST['religion']);
    $mother_tongue = sanitize_input($_POST['mother_tongue']);
    $city = sanitize_input($_POST['city']);
    $education = sanitize_input($_POST['education']);
    $occupation = sanitize_input($_POST['occupation']);
    $bio = sanitize_input($_POST['bio']);
    
    
    if (empty($name) || empty($age)) {
        $error = "Name and age are required.";
    } elseif ($age < 18 || $age > 100) {
        $error = "Age must be between 18 and 100.";
    } else {
        $photo = $user['photo']; 
        
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['photo']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $new_photo = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = 'uploads/' . $new_photo;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    
                    if ($user['photo'] != 'default-avatar.jpg' && file_exists('uploads/' . $user['photo'])) {
                        unlink('uploads/' . $user['photo']);
                    }
                    $photo = $new_photo;
                }
            }
        }
        
        
        $update_query = "UPDATE users SET 
                        name = '$name',
                        age = $age,
                        religion = '$religion',
                        mother_tongue = '$mother_tongue',
                        city = '$city',
                        education = '$education',
                        occupation = '$occupation',
                        bio = '$bio',
                        photo = '$photo'
                        WHERE id = $user_id";
        
        if (mysqli_query($conn, $update_query)) {
            $success = "Profile updated successfully!";
            
            $_SESSION['user_name'] = $name;
            
            $result = mysqli_query($conn, $query);
            $user = mysqli_fetch_assoc($result);
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Indian Matrimony</title>
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
        <div class="glass-card mt-4">
            <h2 class="text-center mb-3">Edit Your Profile</h2>
            <p class="text-center" style="color: var(--text-light); margin-bottom: 2rem;">Update your information to find better matches</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            
            <div class="text-center mb-4">
                <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
                     alt="Current Photo"
                     style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary-color);"
                     onerror="this.src='https://via.placeholder.com/150/a8b5f5/ffffff?text=<?php echo substr($user['name'], 0, 1); ?>'">
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required 
                               value="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>
                    
                    <!-- Age -->
                    <div class="form-group">
                        <label for="age">Age *</label>
                        <input type="number" id="age" name="age" class="form-control" required 
                               min="18" max="100" value="<?php echo $user['age']; ?>">
                    </div>
                    
                    <!-- Religion -->
                    <div class="form-group">
                        <label for="religion">Religion</label>
                        <input type="text" id="religion" name="religion" class="form-control" 
                               value="<?php echo htmlspecialchars($user['religion']); ?>">
                    </div>
                    
                    <!-- Mother Tongue -->
                    <div class="form-group">
                        <label for="mother_tongue">Mother Tongue</label>
                        <input type="text" id="mother_tongue" name="mother_tongue" class="form-control" 
                               value="<?php echo htmlspecialchars($user['mother_tongue']); ?>">
                    </div>
                    
                    <!-- City -->
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" class="form-control" 
                               value="<?php echo htmlspecialchars($user['city']); ?>">
                    </div>
                    
                    <!-- Education -->
                    <div class="form-group">
                        <label for="education">Education</label>
                        <input type="text" id="education" name="education" class="form-control" 
                               value="<?php echo htmlspecialchars($user['education']); ?>">
                    </div>
                    
                    <!-- Occupation -->
                    <div class="form-group">
                        <label for="occupation">Occupation</label>
                        <input type="text" id="occupation" name="occupation" class="form-control" 
                               value="<?php echo htmlspecialchars($user['occupation']); ?>">
                    </div>
                    
                    <!-- Photo Upload -->
                    <div class="form-group">
                        <label for="photo">Update Profile Photo</label>
                        <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                        <small style="color: var(--text-light);">Leave empty to keep current photo</small>
                    </div>
                </div>
                
                <!-- Bio -->
                <div class="form-group">
                    <label for="bio">About Yourself</label>
                    <textarea id="bio" name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                </div>
                
                
                <div class="neuro-card mt-3" style="padding: 1.5rem;">
                    <h3 style="margin-bottom: 1rem; color: var(--text-light);">Account Information</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <div>
                            <strong>Email:</strong>
                            <p style="color: var(--text-light); margin: 0.5rem 0 0 0;"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div>
                            <strong>Gender:</strong>
                            <p style="color: var(--text-light); margin: 0.5rem 0 0 0;"><?php echo htmlspecialchars($user['gender']); ?></p>
                        </div>
                        <div>
                            <strong>Member Since:</strong>
                            <p style="color: var(--text-light); margin: 0.5rem 0 0 0;"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>
                    <p style="margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
                        <em>Email and Gender cannot be changed. Contact support if you need to update these.</em>
                    </p>
                </div>
                
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Update Profile</button>
                    <a href="dashboard.php" class="btn btn-outline" style="flex: 1; text-align: center; line-height: 2.5;">Cancel</a>
                </div>
            </form>
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
  