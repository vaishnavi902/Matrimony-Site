<?php
/**
 * Connections Page - Indian Matrimony
 * View interests, messages, and connections
 * DBMS Mini-Project
 */
require_once 'config.php';

require_login();

$current_user_id = $_SESSION['user_id'];
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'interests_received';

// Handle interest acceptance/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_interest'])) {
    $interest_id = (int)$_POST['interest_id'];
    $action = $_POST['action'];
    
    if ($action == 'accept' || $action == 'reject') {
        $status = $action == 'accept' ? 'accepted' : 'rejected';
        $update_query = "UPDATE interests SET status = '$status' WHERE id = $interest_id AND interested_in_id = $current_user_id";
        if (mysqli_query($conn, $update_query)) {
            $message = $action == 'accept' ? "Interest accepted!" : "Interest rejected.";
            $message_type = $action == 'accept' ? 'success' : 'info';
        }
    }
}

// Get interests received (pending)
$interests_received_query = "SELECT i.*, u.name, u.email, u.age, u.gender, u.photo, u.city 
                              FROM interests i 
                              JOIN users u ON i.user_id = u.id 
                              WHERE i.interested_in_id = $current_user_id AND i.status = 'pending'
                              ORDER BY i.created_at DESC";
$interests_received_result = mysqli_query($conn, $interests_received_query);

// Get interests sent
$interests_sent_query = "SELECT i.*, u.name, u.email, u.age, u.gender, u.photo, u.city 
                         FROM interests i 
                         JOIN users u ON i.interested_in_id = u.id 
                         WHERE i.user_id = $current_user_id
                         ORDER BY i.created_at DESC";
$interests_sent_result = mysqli_query($conn, $interests_sent_query);

// Get accepted interests (mutual connections)
$connections_query = "SELECT i1.*, u.name, u.email, u.age, u.gender, u.photo, u.city, i1.created_at as connected_at
                      FROM interests i1
                      JOIN interests i2 ON i1.user_id = i2.interested_in_id AND i1.interested_in_id = i2.user_id
                      JOIN users u ON (i1.user_id = $current_user_id AND u.id = i1.interested_in_id) 
                                   OR (i1.interested_in_id = $current_user_id AND u.id = i1.user_id)
                      WHERE (i1.user_id = $current_user_id OR i1.interested_in_id = $current_user_id)
                        AND i1.status = 'accepted' AND i2.status = 'accepted'
                      ORDER BY connected_at DESC";
$connections_result = mysqli_query($conn, $connections_query);

// Get recent messages for messages tab
$messages_query = "SELECT m.*, 
                   CASE 
                     WHEN m.sender_id = $current_user_id THEN ur.name
                     ELSE us.name
                   END as other_user_name,
                   CASE 
                     WHEN m.sender_id = $current_user_id THEN ur.photo
                     ELSE us.photo
                   END as other_user_photo,
                   CASE 
                     WHEN m.sender_id = $current_user_id THEN ur.id
                     ELSE us.id
                   END as other_user_id
                   FROM messages m
                   LEFT JOIN users us ON m.sender_id = us.id
                   LEFT JOIN users ur ON m.receiver_id = ur.id
                   WHERE m.sender_id = $current_user_id OR m.receiver_id = $current_user_id
                   ORDER BY m.created_at DESC";
$messages_result_temp = mysqli_query($conn, $messages_query);

// Get unread messages count
$unread_query = "SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = $current_user_id AND is_read = 0";
$unread_result = mysqli_query($conn, $unread_query);
$unread_count = mysqli_fetch_assoc($unread_result)['unread_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connections - Indian Matrimony</title>
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
                <li><a href="connections.php?tab=messages">💬 Messages <?php if ($unread_count > 0): ?><span style="background: red; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li><a href="edit_profile.php">My Profile</a></li>
                <li><a href="statistics.php">Statistics</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="max-width: 1200px;">
        <h2 class="text-center mt-3 mb-3">My Connections</h2>
        
        <!-- Tabs -->
        <div class="glass-card mb-3">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; border-bottom: 2px solid var(--bg-color); padding-bottom: 1rem;">
                <a href="connections.php?tab=interests_received" 
                   class="btn <?php echo $active_tab == 'interests_received' ? 'btn-primary' : 'btn-outline'; ?>">
                    💝 Interests Received
                </a>
                <a href="connections.php?tab=interests_sent" 
                   class="btn <?php echo $active_tab == 'interests_sent' ? 'btn-primary' : 'btn-outline'; ?>">
                    📤 Interests Sent
                </a>
                <a href="connections.php?tab=connections" 
                   class="btn <?php echo $active_tab == 'connections' ? 'btn-primary' : 'btn-outline'; ?>">
                    🤝 Connections
                </a>
                <a href="connections.php?tab=messages" 
                   class="btn <?php echo $active_tab == 'messages' ? 'btn-primary' : 'btn-outline'; ?>">
                    💬 Messages <?php if ($unread_count > 0): ?><span style="background: red; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;"><?php echo $unread_count; ?></span><?php endif; ?>
                </a>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>" style="margin-bottom: 1rem;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Interests Received Tab -->
        <?php if ($active_tab == 'interests_received'): ?>
            <div class="profiles-grid">
                <?php if ($interests_received_result && mysqli_num_rows($interests_received_result) > 0): ?>
                    <?php while ($interest = mysqli_fetch_assoc($interests_received_result)): 
                        // Get full profile details
                        $full_profile_query = "SELECT * FROM users WHERE id = {$interest['user_id']}";
                        $full_profile_result = mysqli_query($conn, $full_profile_query);
                        $full_profile = mysqli_fetch_assoc($full_profile_result);
                    ?>
                        <div class="profile-card" style="border: 2px solid var(--primary-color); position: relative;">
                            <div style="position: absolute; top: 10px; left: 10px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">
                                New Interest
                            </div>
                            <div class="profile-card-header">
                                <img src="uploads/<?php echo htmlspecialchars($interest['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($interest['name']); ?>"
                                     class="profile-avatar"
                                     onerror="this.src='https://via.placeholder.com/120/a8b5f5/ffffff?text=<?php echo substr($interest['name'], 0, 1); ?>'">
                            </div>
                            <div class="profile-card-body">
                                <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($interest['name']); ?></h3>
                                <p style="color: var(--text-light); margin-bottom: 0.5rem; font-size: 0.95rem;">
                                    <strong><?php echo htmlspecialchars($interest['age']); ?> years</strong>, <?php echo htmlspecialchars($interest['gender']); ?>
                                </p>
                                <div style="margin-bottom: 1rem; padding: 0.8rem; background: var(--bg-color); border-radius: 8px;">
                                    <p style="margin: 0.3rem 0; color: var(--text-dark); font-size: 0.9rem;">
                                        <span style="color: var(--primary-color);">🕉️</span> <?php echo htmlspecialchars($full_profile['religion']); ?>
                                    </p>
                                    <p style="margin: 0.3rem 0; color: var(--text-dark); font-size: 0.9rem;">
                                        <span style="color: var(--secondary-color);">📍</span> <?php echo htmlspecialchars($interest['city']); ?>
                                    </p>
                                    <p style="margin: 0.3rem 0; color: var(--text-dark); font-size: 0.9rem;">
                                        <span style="color: var(--accent-color);">🎓</span> <?php echo htmlspecialchars($full_profile['education']); ?>
                                    </p>
                                    <p style="margin: 0.3rem 0; color: var(--text-dark); font-size: 0.9rem;">
                                        <span style="color: var(--primary-color);">💼</span> <?php echo htmlspecialchars($full_profile['occupation']); ?>
                                    </p>
                                </div>
                                <p style="color: var(--text-light); font-size: 0.85rem; margin-bottom: 1rem; text-align: center;">
                                    Expressed interest on <?php echo date('M d, Y', strtotime($interest['created_at'])); ?>
                                </p>
                                <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                                    <a href="view_profile.php?id=<?php echo $interest['user_id']; ?>" class="btn btn-secondary" style="width: 100%; text-align: center;">👁️ View Full Profile</a>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="interest_id" value="<?php echo $interest['id']; ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" name="action_interest" class="btn btn-primary" style="width: 100%;">✅ Accept</button>
                                        </form>
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="interest_id" value="<?php echo $interest['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" name="action_interest" class="btn btn-outline" style="width: 100%;">❌ Decline</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="glass-card" style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                        <h3>No pending interests</h3>
                        <p style="color: var(--text-light); margin-top: 1rem;">You have no pending interest requests</p>
                    </div>
                <?php endif; ?>
            </div>

        <!-- Interests Sent Tab -->
        <?php elseif ($active_tab == 'interests_sent'): ?>
            <div class="profiles-grid">
                <?php if ($interests_sent_result && mysqli_num_rows($interests_sent_result) > 0): ?>
                    <?php while ($interest = mysqli_fetch_assoc($interests_sent_result)): ?>
                        <div class="profile-card">
                            <div class="profile-card-header">
                                <img src="uploads/<?php echo htmlspecialchars($interest['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($interest['name']); ?>"
                                     class="profile-avatar"
                                     onerror="this.src='https://via.placeholder.com/120/a8b5f5/ffffff?text=<?php echo substr($interest['name'], 0, 1); ?>'">
                            </div>
                            <div class="profile-card-body">
                                <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($interest['name']); ?></h3>
                                <p style="color: var(--text-light); margin-bottom: 0.5rem; font-size: 0.95rem;">
                                    <strong><?php echo htmlspecialchars($interest['age']); ?> years</strong>, <?php echo htmlspecialchars($interest['gender']); ?>
                                </p>
                                <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 1rem;">
                                    📍 <?php echo htmlspecialchars($interest['city']); ?>
                                </p>
                                <div style="margin-bottom: 1rem; padding: 0.8rem; background: var(--bg-color); border-radius: 8px;">
                                    <?php 
                                    if ($interest['status'] == 'accepted') {
                                        echo '<div style="display: flex; align-items: center; gap: 0.5rem; color: var(--primary-color);"><span style="font-size: 1.2rem;">✅</span> <strong>Interest Accepted</strong></div>';
                                        echo '<p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: var(--text-light);">You can now message this person</p>';
                                    } elseif ($interest['status'] == 'rejected') {
                                        echo '<div style="display: flex; align-items: center; gap: 0.5rem; color: #e74c3c;"><span style="font-size: 1.2rem;">❌</span> <strong>Interest Rejected</strong></div>';
                                    } else {
                                        echo '<div style="display: flex; align-items: center; gap: 0.5rem; color: #f39c12;"><span style="font-size: 1.2rem;">⏳</span> <strong>Interest Pending</strong></div>';
                                        echo '<p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: var(--text-light);">Waiting for response</p>';
                                    }
                                    ?>
                                </div>
                                <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                                    <a href="view_profile.php?id=<?php echo $interest['interested_in_id']; ?>" class="btn btn-secondary" style="width: 100%; text-align: center;">👁️ View Profile</a>
                                    <?php if ($interest['status'] == 'accepted'): ?>
                                        <a href="messages.php?user_id=<?php echo $interest['interested_in_id']; ?>" class="btn btn-primary" style="width: 100%; text-align: center; font-weight: bold;">💬 Start Messaging</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="glass-card" style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                        <h3>No interests sent yet</h3>
                        <p style="color: var(--text-light); margin-top: 1rem;">Start browsing profiles to express interest</p>
                        <a href="dashboard.php" class="btn btn-primary mt-2">Browse Profiles</a>
                    </div>
                <?php endif; ?>
            </div>

        <!-- Connections Tab -->
        <?php elseif ($active_tab == 'connections'): ?>
            <div class="profiles-grid">
                <?php if ($connections_result && mysqli_num_rows($connections_result) > 0): ?>
                    <?php while ($connection = mysqli_fetch_assoc($connections_result)): ?>
                        <div class="profile-card">
                            <div class="profile-card-header">
                                <img src="uploads/<?php echo htmlspecialchars($connection['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($connection['name']); ?>"
                                     class="profile-avatar"
                                     onerror="this.src='https://via.placeholder.com/120/a8b5f5/ffffff?text=<?php echo substr($connection['name'], 0, 1); ?>'">
                            </div>
                            <div class="profile-card-body">
                                <h3><?php echo htmlspecialchars($connection['name']); ?></h3>
                                <p style="color: var(--text-light); margin-bottom: 1rem;">
                                    <?php echo htmlspecialchars($connection['age']); ?> years, <?php echo htmlspecialchars($connection['gender']); ?><br>
                                    📍 <?php echo htmlspecialchars($connection['city']); ?>
                                </p>
                                <p style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 1rem;">
                                    Connected: <?php echo date('M d, Y', strtotime($connection['connected_at'])); ?>
                                </p>
                                <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                                    <a href="view_profile.php?id=<?php echo $connection['id'] == $current_user_id ? $connection['user_id'] : $connection['id']; ?>" class="btn btn-secondary" style="width: 100%; text-align: center;">View Profile</a>
                                    <a href="messages.php?user_id=<?php echo $connection['id'] == $current_user_id ? $connection['user_id'] : $connection['id']; ?>" class="btn btn-primary" style="width: 100%; text-align: center;">💬 Message</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="glass-card" style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                        <h3>No connections yet</h3>
                        <p style="color: var(--text-light); margin-top: 1rem;">Start expressing interest in profiles to build connections</p>
                        <a href="dashboard.php" class="btn btn-primary mt-2">Browse Profiles</a>
                    </div>
                <?php endif; ?>
            </div>

        <!-- Messages Tab -->
        <?php elseif ($active_tab == 'messages'): ?>
            <div class="glass-card">
                <h3 style="margin-bottom: 1.5rem; color: var(--primary-color);">💬 Your Conversations</h3>
                <?php if ($messages_result_temp && mysqli_num_rows($messages_result_temp) > 0): ?>
                    <?php 
                    // Get unread counts for each user
                    $user_ids_for_unread = [];
                    $messages_array = [];
                    if ($messages_result_temp) {
                        while ($msg = mysqli_fetch_assoc($messages_result_temp)) {
                            $messages_array[] = $msg;
                            $other_id = $msg['other_user_id'];
                            if (!in_array($other_id, $user_ids_for_unread)) {
                                $user_ids_for_unread[] = $other_id;
                            }
                        }
                        // Reset result pointer
                        mysqli_data_seek($messages_result_temp, 0);
                    }
                    
                    // Get unread counts
                    $unread_counts = [];
                    if (!empty($user_ids_for_unread)) {
                        $ids_str = implode(',', $user_ids_for_unread);
                        $unread_query = "SELECT sender_id, COUNT(*) as unread FROM messages WHERE receiver_id = $current_user_id AND is_read = 0 AND sender_id IN ($ids_str) GROUP BY sender_id";
                        $unread_result = mysqli_query($conn, $unread_query);
                        while ($unread = mysqli_fetch_assoc($unread_result)) {
                            $unread_counts[$unread['sender_id']] = $unread['unread'];
                        }
                    }
                    
                    // Get latest message per user
                    $latest_messages = [];
                    foreach ($messages_array as $msg) {
                        $other_id = $msg['other_user_id'];
                        if (!isset($latest_messages[$other_id]) || strtotime($msg['created_at']) > strtotime($latest_messages[$other_id]['created_at'])) {
                            $latest_messages[$other_id] = $msg;
                        }
                    }
                    ?>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($latest_messages as $message): 
                            $other_id = $message['other_user_id'];
                            $unread_count = isset($unread_counts[$other_id]) ? $unread_counts[$other_id] : 0;
                        ?>
                            <div class="neuro-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.5rem; <?php echo $unread_count > 0 ? 'border: 2px solid var(--primary-color);' : ''; ?>">
                                <div style="position: relative;">
                                    <img src="uploads/<?php echo htmlspecialchars($message['other_user_photo']); ?>" 
                                         alt="<?php echo htmlspecialchars($message['other_user_name']); ?>"
                                         style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-color);"
                                         onerror="this.src='https://via.placeholder.com/70/a8b5f5/ffffff?text=<?php echo substr($message['other_user_name'], 0, 1); ?>'">
                                    <?php if ($unread_count > 0): ?>
                                        <span style="position: absolute; top: -5px; right: -5px; background: #e74c3c; color: white; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold; border: 2px solid white;">
                                            <?php echo $unread_count > 9 ? '9+' : $unread_count; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div style="flex: 1;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                        <h4 style="margin: 0; <?php echo $unread_count > 0 ? 'font-weight: bold;' : ''; ?>"><?php echo htmlspecialchars($message['other_user_name']); ?></h4>
                                        <span style="color: var(--text-light); font-size: 0.85rem;">
                                            <?php 
                                            $time_diff = time() - strtotime($message['created_at']);
                                            if ($time_diff < 3600) {
                                                echo round($time_diff / 60) . 'm ago';
                                            } elseif ($time_diff < 86400) {
                                                echo round($time_diff / 3600) . 'h ago';
                                            } else {
                                                echo date('M d', strtotime($message['created_at']));
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <p style="color: var(--text-light); margin: 0; font-size: 0.9rem; <?php echo $unread_count > 0 ? 'font-weight: 600; color: var(--text-dark);' : ''; ?>">
                                        <?php 
                                        $preview = htmlspecialchars($message['message']);
                                        echo strlen($preview) > 80 ? substr($preview, 0, 80) . '...' : $preview;
                                        ?>
                                    </p>
                                    <?php if ($unread_count > 0): ?>
                                        <p style="color: var(--primary-color); margin: 0.5rem 0 0 0; font-size: 0.85rem; font-weight: bold;">
                                            <?php echo $unread_count; ?> unread message<?php echo $unread_count > 1 ? 's' : ''; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <a href="messages.php?user_id=<?php echo $other_id; ?>" class="btn btn-primary" style="min-width: 140px;"><?php echo $unread_count > 0 ? 'View & Reply' : 'Open Chat'; ?></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">💬</div>
                        <h3>No messages yet</h3>
                        <p style="color: var(--text-light); margin-top: 1rem;">Start connecting with people to send messages</p>
                        <a href="dashboard.php" class="btn btn-primary mt-2">Browse Profiles</a>
                    </div>
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

