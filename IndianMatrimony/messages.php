<?php
/**
 * Messages Page - Indian Matrimony
 * Send and view messages with other users
 * DBMS Mini-Project
 */
require_once 'config.php';

require_login();

$current_user_id = $_SESSION['user_id'];
$other_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$message = '';
$message_type = '';

// Validate other user
if ($other_user_id == 0 || $other_user_id == $current_user_id) {
    header("Location: connections.php?tab=messages");
    exit();
}

$other_user_query = "SELECT * FROM users WHERE id = $other_user_id";
$other_user_result = mysqli_query($conn, $other_user_query);
if (!$other_user_result || mysqli_num_rows($other_user_result) == 0) {
    header("Location: connections.php?tab=messages");
    exit();
}
$other_user = mysqli_fetch_assoc($other_user_result);

// Check if users have mutual connection
$connection_check = "SELECT COUNT(*) as count FROM interests i1
                     JOIN interests i2 ON i1.user_id = i2.interested_in_id AND i1.interested_in_id = i2.user_id
                     WHERE ((i1.user_id = $current_user_id AND i1.interested_in_id = $other_user_id)
                        OR (i1.user_id = $other_user_id AND i1.interested_in_id = $current_user_id))
                     AND i1.status = 'accepted' AND i2.status = 'accepted'";
$connection_result = mysqli_query($conn, $connection_check);
$has_connection = mysqli_fetch_assoc($connection_result)['count'] > 0;

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    if (!$has_connection) {
        $message = "You need to have a mutual connection to send messages.";
        $message_type = "error";
    } else {
        $message_text = sanitize_input($_POST['message_text']);
        if (!empty($message_text)) {
            $insert_query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES ($current_user_id, $other_user_id, '$message_text')";
            if (mysqli_query($conn, $insert_query)) {
                // Log activity
                $log_query = "INSERT INTO activity_logs (user_id, activity_type, activity_details) VALUES ($current_user_id, 'message_sent', 'Sent message to user ID: $other_user_id')";
                mysqli_query($conn, $log_query);
                
                $message = "Message sent successfully!";
                $message_type = "success";
            } else {
                $message = "Failed to send message. Please try again.";
                $message_type = "error";
            }
        }
    }
}

// Mark messages as read
$mark_read_query = "UPDATE messages SET is_read = 1 WHERE sender_id = $other_user_id AND receiver_id = $current_user_id AND is_read = 0";
mysqli_query($conn, $mark_read_query);

// Get conversation messages
$messages_query = "SELECT m.*, u.name as sender_name, u.photo as sender_photo 
                   FROM messages m
                   JOIN users u ON m.sender_id = u.id
                   WHERE (m.sender_id = $current_user_id AND m.receiver_id = $other_user_id)
                      OR (m.sender_id = $other_user_id AND m.receiver_id = $current_user_id)
                   ORDER BY m.created_at ASC";
$messages_result = mysqli_query($conn, $messages_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Indian Matrimony</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .messages-container {
            display: flex;
            flex-direction: column;
            height: 600px;
            background: var(--bg-color);
            border-radius: 10px;
            overflow: hidden;
        }
        .messages-list {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .message-item {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }
        .message-item.sent {
            flex-direction: row-reverse;
        }
        .message-bubble {
            max-width: 70%;
            padding: 1rem;
            border-radius: 15px;
            word-wrap: break-word;
        }
        .message-item.sent .message-bubble {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-bottom-right-radius: 5px;
        }
        .message-item.received .message-bubble {
            background: var(--glass-bg);
            color: var(--text-dark);
            border-bottom-left-radius: 5px;
        }
        .message-time {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }
        .message-form {
            padding: 1rem;
            border-top: 2px solid var(--bg-color);
            background: white;
        }
    </style>
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
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <a href="connections.php?tab=messages" class="btn btn-outline">← Back to Messages</a>
            <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                <img src="uploads/<?php echo htmlspecialchars($other_user['photo']); ?>" 
                     alt="<?php echo htmlspecialchars($other_user['name']); ?>"
                     style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"
                     onerror="this.src='https://via.placeholder.com/50/a8b5f5/ffffff?text=<?php echo substr($other_user['name'], 0, 1); ?>'">
                <div>
                    <h3 style="margin: 0;"><?php echo htmlspecialchars($other_user['name']); ?></h3>
                    <p style="margin: 0; color: var(--text-light); font-size: 0.9rem;">
                        <?php echo htmlspecialchars($other_user['age']); ?> years, <?php echo htmlspecialchars($other_user['city']); ?>
                    </p>
                </div>
                <a href="view_profile.php?id=<?php echo $other_user_id; ?>" class="btn btn-secondary">View Profile</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'error'; ?>" style="margin-bottom: 1rem;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!$has_connection): ?>
            <div class="glass-card" style="text-align: center; padding: 2rem;">
                <h3>Connection Required</h3>
                <p style="color: var(--text-light); margin: 1rem 0;">You need to have a mutual accepted interest to send messages.</p>
                <a href="view_profile.php?id=<?php echo $other_user_id; ?>" class="btn btn-primary">View Profile</a>
            </div>
        <?php else: ?>
            <div class="glass-card">
                <div class="messages-container">
                    <div class="messages-list">
                        <?php if ($messages_result && mysqli_num_rows($messages_result) > 0): ?>
                            <?php while ($msg = mysqli_fetch_assoc($messages_result)): ?>
                                <div class="message-item <?php echo $msg['sender_id'] == $current_user_id ? 'sent' : 'received'; ?>">
                                    <?php if ($msg['sender_id'] != $current_user_id): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($msg['sender_photo']); ?>" 
                                             alt="<?php echo htmlspecialchars($msg['sender_name']); ?>"
                                             style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
                                             onerror="this.src='https://via.placeholder.com/40/a8b5f5/ffffff?text=<?php echo substr($msg['sender_name'], 0, 1); ?>'">
                                    <?php endif; ?>
                                    <div>
                                        <div class="message-bubble">
                                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                        </div>
                                        <div class="message-time">
                                            <?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: var(--text-light);">
                                <p>No messages yet. Start the conversation!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" class="message-form">
                        <div style="display: flex; gap: 1rem; align-items: flex-end;">
                            <textarea name="message_text" 
                                      id="message_text"
                                      class="form-control" 
                                      rows="2" 
                                      placeholder="Type your message here..." 
                                      required 
                                      style="flex: 1; resize: none; min-height: 50px; max-height: 150px;"></textarea>
                            <button type="submit" name="send_message" class="btn btn-primary" style="padding: 0.8rem 1.5rem; height: fit-content; font-weight: bold;">
                                Send 💬
                            </button>
                        </div>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: var(--text-light); text-align: center;">
                            Press Enter to send (Shift+Enter for new line)
                        </p>
                    </form>
                    <script>
                        // Allow Enter to send, Shift+Enter for new line
                        document.getElementById('message_text').addEventListener('keydown', function(e) {
                            if (e.key === 'Enter' && !e.shiftKey) {
                                e.preventDefault();
                                this.form.submit();
                            }
                        });
                    </script>
                </div>
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
    <script>
        // Auto-scroll to bottom of messages
        const messagesList = document.querySelector('.messages-list');
        if (messagesList) {
            messagesList.scrollTop = messagesList.scrollHeight;
        }
        
        // Auto-refresh messages every 10 seconds
        setInterval(function() {
            if (document.visibilityState === 'visible') {
                window.location.reload();
            }
        }, 10000);
        
        // Auto-scroll on new message
        const messageForm = document.querySelector('.message-form form');
        if (messageForm) {
            messageForm.addEventListener('submit', function() {
                setTimeout(function() {
                    messagesList.scrollTop = messagesList.scrollHeight;
                }, 100);
            });
        }
    </script>
</body>
</html>

