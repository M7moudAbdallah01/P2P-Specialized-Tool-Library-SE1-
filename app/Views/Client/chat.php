<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();

$user_id   = (int) $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'User';
$user_role = strtolower($_SESSION['role'] ?? 'client');

$isClient = $user_role === 'client';
$isTech   = $user_role === 'technical';

/*
|--------------------------------------------------------------------------
| SEND MESSAGE TO ADMIN
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {

    $receiver_id = (int) ($_POST['receiver_id'] ?? 0);
    $content     = trim($_POST['content'] ?? '');

    if (!empty($content) && $receiver_id > 0) {

        $encrypted = base64_encode($content);

        $stmt = $conn->prepare("
            INSERT INTO messages (
                sender_id,
                receiver_id,
                content,
                encrypted,
                is_read
            ) VALUES (?, ?, ?, ?, 0)
        ");

        $stmt->bind_param(
            "iiss",
            $user_id,
            $receiver_id,
            $content,
            $encrypted
        );

        $stmt->execute();
        $stmt->close();

        header("Location: chat.php?user=" . $receiver_id);
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| GET ADMIN USERS ONLY
|--------------------------------------------------------------------------
*/
$admins = $conn->query("
    SELECT user_id, name, email
    FROM users
    WHERE role = 'admin'
    ORDER BY name ASC
");

/*
|--------------------------------------------------------------------------
| SELECT CURRENT CHAT ADMIN
|--------------------------------------------------------------------------
*/
$selected_user = isset($_GET['user']) ? (int) $_GET['user'] : 0;

if ($selected_user === 0) {
    $firstAdmin = $conn->query("
        SELECT user_id
        FROM users
        WHERE role = 'admin'
        LIMIT 1
    ");
    if ($firstAdmin && $row = $firstAdmin->fetch_assoc()) {
        $selected_user = (int) $row['user_id'];
    }
}

/*
|--------------------------------------------------------------------------
| MARK RECEIVED MESSAGES AS READ
|--------------------------------------------------------------------------
*/
if ($selected_user > 0) {
    $mark = $conn->prepare("
        UPDATE messages
        SET is_read = 1
        WHERE sender_id = ?
          AND receiver_id = ?
    ");
    $mark->bind_param("ii", $selected_user, $user_id);
    $mark->execute();
    $mark->close();
}

/*
|--------------------------------------------------------------------------
| FETCH CHAT MESSAGES
|--------------------------------------------------------------------------
*/
$messages = [];

if ($selected_user > 0) {

    $stmt = $conn->prepare("
        SELECT
            m.*,
            s.name AS sender_name,
            r.name AS receiver_name
        FROM messages m
        JOIN users s ON m.sender_id = s.user_id
        JOIN users r ON m.receiver_id = r.user_id
        WHERE
            (m.sender_id = ? AND m.receiver_id = ?)
            OR
            (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.message_id ASC
    ");

    $stmt->bind_param(
        "iiii",
        $user_id,
        $selected_user,
        $selected_user,
        $user_id
    );

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| UNREAD COUNTS FROM ADMINS
|--------------------------------------------------------------------------
*/
$unread = [];

$resUnread = $conn->query("
    SELECT sender_id, COUNT(*) as total
    FROM messages
    WHERE receiver_id = {$user_id}
      AND is_read = 0
    GROUP BY sender_id
");

while ($row = $resUnread->fetch_assoc()) {
    $unread[$row['sender_id']] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Chat</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/chat.css">

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="role-badge" style="background:#2563eb;color:#fff;">
        <?= strtoupper($user_role) ?>
    </div>

    <div class="sidebar-nav">

        <?php if ($isClient): ?>
            <a href="dashboard.php" class="nav-link">
                <i class="fa fa-gauge"></i> Dashboard
            </a>

            <a href="../Tools/tools.php" class="nav-link">
                <i class="fa fa-wrench"></i> Tools
            </a>

            <a href="ToolSpecification.php" class="nav-link">
                <i class="fa fa-plus"></i> Add Tool
            </a>

            <a href="../Tools/categories.php" class="nav-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg> Categories
            </a>

        <a href="my-reservations.php" class="nav-link active">
            <i class="fa fa-calendar-check"></i> My Reservations
        </a>

            <a href="chat.php" class="nav-link active">
                <i class="fa fa-comments"></i> Chat
            </a>

            <a href="ToolCompatibility.php" class="nav-link">
                <i class="fa fa-circle-check"></i> Compatibility Checker
            </a>

            <a href="DamageDeclaration.php" class="nav-link">
                <i class="fa fa-triangle-exclamation"></i> Damage Report
            </a>


        <?php endif; ?>

        <?php if ($isTech): ?>
            <a href="dashboard.php" class="nav-link">
                <i class="fa fa-gauge"></i> Dashboard
            </a>
            <a href="../Tools/tools.php" class="nav-link">
                <i class="fa fa-wrench"></i> Tools
            </a>

        <a href="chat.php" class="nav-link active">
            <i class="fa fa-comments"></i> Chat
        </a>
        <?php endif; ?>


    </div>
</div>

<!-- RIGHT -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-title">Chat with Admin</div>
        <div class="topbar-right">
            <button class="avatar-btn" style="background:#6366f1;color:#fff;border:none;cursor:default;">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Client</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <!-- MAIN -->
    <div class="main-content">

        <div class="chat-layout">

            <!-- ADMINS LIST -->
            <div class="chat-users">
                <div class="chat-users-header">
                    <i class="fa fa-user-shield"></i> Admin Team
                </div>

                <?php while ($admin = $admins->fetch_assoc()): ?>
                    <a href="?user=<?= $admin['user_id'] ?>"
                       class="chat-user <?= $selected_user == $admin['user_id'] ? 'active' : '' ?>">
                        <div>
                            <strong><?= htmlspecialchars($admin['name']) ?></strong><br>
                            <small style="color:#cbd5e1;">
                                <?= htmlspecialchars($admin['email']) ?>
                            </small>
                        </div>

                        <?php if (!empty($unread[$admin['user_id']])): ?>
                            <span class="badge-unread">
                                <?= $unread[$admin['user_id']] ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endwhile; ?>
            </div>

            <!-- CHAT BOX -->
            <div class="chat-box">

                <div class="chat-header">
                    <i class="fa fa-comments"></i>
                    Conversation
                </div>

                <div class="chat-messages" id="chatMessages">

                    <?php if (empty($messages)): ?>
                        <div class="empty-chat">
                            <i class="fa fa-comments" style="font-size:40px;margin-bottom:12px;"></i>
                            <p>No messages yet. Start chatting with admin.</p>
                        </div>
                    <?php else: ?>

                        <?php foreach ($messages as $msg): ?>
                            <div class="message <?= $msg['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                                <?= nl2br(htmlspecialchars($msg['content'])) ?>
                                <div style="font-size:11px;opacity:.7;margin-top:6px;">
                                    <?= $msg['sender_id'] == $user_id ? 'You' : htmlspecialchars($msg['sender_name']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

                <!-- SEND -->
                <?php if ($selected_user > 0): ?>
                <form method="POST" class="chat-form">
                    <input type="hidden" name="receiver_id" value="<?= $selected_user ?>">
                    <textarea name="content"
                              rows="2"
                              placeholder="Write your message..."
                              required></textarea>
                    <button type="submit" name="send_message">
                        <i class="fa fa-paper-plane"></i>
                    </button>
                </form>
                <?php endif; ?>

            </div>

        </div>

    </div>
</div>

<script>
const chatMessages = document.getElementById('chatMessages');
if (chatMessages) {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}
</script>

</body>
</html>